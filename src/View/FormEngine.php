<?php

declare(strict_types=1);

namespace SA\Form\View;

use SA\Form\View\Factory\ManagesBlocks;
use Symfony\Component\Form\AbstractRendererEngine;
use Symfony\Component\Form\FormView;

final class FormEngine extends AbstractRendererEngine
{
    /**
     * @var ManagesBlocks
     */
    private $blocks;

    public function __construct(ManagesBlocks $blocks, array $defaultThemes = [])
    {
        parent::__construct($defaultThemes);
        $this->blocks = $blocks;
    }

    /**
     * @inheritDoc
     */
    protected function loadResourceForBlockName(string $cacheKey, FormView $view, string $blockName)
    {
        $this->resources[$cacheKey][$blockName] = false;

        if (isset($this->blocks[$blockName])) {
            $this->resources[$cacheKey][$blockName] = $this->blocks;
        }

        return false !== $this->resources[$cacheKey][$blockName];
    }

    /**
     * @inheritDoc
     */
    public function renderBlock(FormView $view, $resource, string $blockName, array $variables = [])
    {
        // TODO: Implement renderBlock() method.
    }
}
