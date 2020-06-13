<?php

declare(strict_types=1);

namespace SA\Form\View;

use Illuminate\View\Compilers\BladeCompiler;

final class BlockCompiler
{
    /**
     * @var string
     */
    private $cachePath;

    private $blocks = [];

    /**
     * BlockCompiler constructor.
     * @param string $cachePath
     */
    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function __invoke($content, BladeCompiler $compiler)
    {
        $path = $this->getCompiledPath($compiler->getPath());

        preg_match_all('/\B@(@?(?:end)*block(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $content, $matches, PREG_OFFSET_CAPTURE);

        $blocksStack = [];

        $blockNamesStack = [];

        $level = 0;

        foreach ($matches[1] as $index => $item) {
            if ($item[0] === 'block') {
                $params = array_map('trim', explode(',', $matches[4][$index][0]));
                $nameBlock = trim($params[0], '\'');

                if (count($params) === 2) {
                    $this->blocks[$level][$nameBlock] = [
                        'content' => $params[1],
                        'nested' => $blockNamesStack[$level - 1] ?? null,
                        'position' => [
                            'start' => $item[1] - 1,
                            'len' => $matches[3][$index][1] + strlen($matches[3][$index][0]) - $item[1] + 1,
                        ],
                    ];
                } else {
                    $blockNamesStack[$level] = $nameBlock;
                    array_push($blocksStack, [
                        'name' => $nameBlock,
                        'start' => $item[1],
                        'level' => $level,
                    ]);
                    $level++;
                }
            } elseif ($item[0] === 'endblock') {
                $lastBlock = array_pop($blocksStack);
                $position = [
                    'start' => $lastBlock['start'] - 1,
                    'len' => $item[1] + 9 - $lastBlock['start'],
                ];
                $this->blocks[$lastBlock['level']][$lastBlock['name']] = [
                    'content' => substr($content, $position['start'], $position['len']),
                    'nested' => $blockNamesStack[$lastBlock['level'] - 1] ?? null,
                    'position' => $position,
                ];
                $level--;
            }
        }

        return $content;
    }

    private function getCompiledPath(string $path): string
    {
        return $this->cachePath . '/blocks_' . sha1($path) . '.php';
    }
}
