<?php

declare(strict_types=1);

namespace SA\Form\View\Factory;

/**
 * Class CompilesBlocks
 * @package SA\Form\View\Factory
 */
final class CompilesBlocks
{
    /**
     * Compile the block statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    public static function compileBlock(string $expression): string
    {
        return "<?php \$__env->startBlock({$expression}); ?>";
    }

    /**
     * Compile the end-block statements into valid PHP.
     *
     * @return string
     */
    public static function compileEndblock(): string
    {
        return '<?php $__env->stopBlock(); ?>';
    }

    /**
     * Compile the yield statements into valid PHP.
     * @param string $expression
     * @return string
     */
    public static function compileYieldblock(string $expression): string
    {
        return "<?php echo \$__env->yieldBlock({$expression}); ?>";
    }
}
