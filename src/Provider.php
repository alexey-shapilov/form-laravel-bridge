<?php

namespace SA\Form;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory as ViewFactory;
use Psr\Container\ContainerInterface;
use ReflectionException;
use SA\Form\Session\SessionTokenStorage;
use SA\Form\View\BlockCompiler;
use SA\Form\View\FormEngine;
use SA\Form\View\Factory\CompilesBlocks;
use SA\Form\View\Factory\ManagesBlocks;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormRendererEngineInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Validator\Validation;

class Provider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @throws ReflectionException
     */
    public function boot()
    {
        $this->app->singleton(CsrfTokenManagerInterface::class, function (ContainerInterface $container) {
            $csrfGenerator = new UriSafeTokenGenerator();
            $csrfStorage = new SessionTokenStorage($container->get('session.store'));
            return new CsrfTokenManager($csrfGenerator, $csrfStorage);
        });

        $this->app->singleton(FormFactoryInterface::class, function (ContainerInterface $container) {
            if (method_exists(AnnotationRegistry::class, 'registerLoader')) {
                AnnotationRegistry::registerLoader(function($class) {
                    return class_exists($class);
                });
            }

            $validator = Validation::createValidatorBuilder()
                ->addMethodMapping('loadValidatorMetadata')
                ->enableAnnotationMapping()
                ->setMappingCache($container->get('cache.psr6'))
                ->getValidator()
            ;

            return Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->addExtension(new CsrfExtension($container->get(CsrfTokenManagerInterface::class)))
                ->addExtension(new ValidatorExtension($validator))
                ->getFormFactory()
            ;
        });
        $this->app->alias(FormFactoryInterface::class, FormFactory::class);

        $this->app->singleton(FormRendererEngineInterface::class, FormEngine::class);

        $this->app->singleton(FormRenderer::class);

        ViewFactory::mixin(new ManagesBlocks());

        Blade::extend(new BlockCompiler(config('view.compiled')));

        Blade::directive('block', function ($expression) {
            return CompilesBlocks::compileBlock($expression);
        });

        Blade::directive('endblock', function () {
            return CompilesBlocks::compileEndblock();
        });

        Blade::directive('yieldblock', function ($expression) {
            return CompilesBlocks::compileYieldblock($expression);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/form.php', 'form');
    }

    public function provides()
    {
        return [
            FormFactoryInterface::class,
            FormFactory::class,
            CsrfTokenManagerInterface::class,
            FormRendererEngineInterface::class,
            FormRenderer::class,
        ];
    }
}
