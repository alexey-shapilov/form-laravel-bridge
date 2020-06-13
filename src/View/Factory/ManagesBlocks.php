<?php

declare(strict_types=1);

namespace SA\Form\View\Factory;

use Closure;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;

/**
 * Class ManagesBlocks
 * @package SA\Form\View\Factory
 */
final class ManagesBlocks
{
    /**
     * @var array
     */
    private $blockStack;

    private $blocks;

    /**
     * Start injecting content into a block.
     *
     * @return Closure
     */
    protected function startBlock()
    {
        return function ($block, $content = null) {
            if ($content === null) {
                if (ob_start()) {
                    $this->blockStack[] = $block;
                }
            } else {
                $this->blocks[$block] = $content instanceof View ? $content : e($content);
            }
        };
    }

    /**
     * Stop injecting content into a block.
     *
     * @return Closure
     *
     */
    protected function stopBlock()
    {
        return function () {
            if (empty($this->blockStack)) {
                throw new InvalidArgumentException('Cannot end a block without first starting one.');
            }

            $last = array_pop($this->blockStack);

            $this->blocks[$last] = ob_get_clean();

            return $last;
        };
    }

    /**
     * Get the string contents of a block.
     *
     * @return Closure
     */
    public function yieldBlock()
    {
        return function ($block, $default = '') {
            $blockContent = $default instanceof View ? $default : e($default);

            if (isset($this->blocks[$block])) {
                $blockContent = $this->blocks[$block];
            }

            return $blockContent;
        };
    }
}
