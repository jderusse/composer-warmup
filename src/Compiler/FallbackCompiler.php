<?php

declare(strict_types=1);

namespace Jderusse\Warmup\Compiler;

final class FallbackCompiler implements CompilerInterface
{
    /** @var CompilerInterface[] */
    private $compilers;

    public function __construct(array $compilers)
    {
        $this->compilers = $compilers;
    }

    public function compile(string $file)
    {
        foreach ($this->compilers as $compiler) {
            try {
                return $compiler->compile($file);
            } catch (\Throwable $e) {
                continue;
            }
        }

        throw new \RuntimeException("Failed to compile {$file}.");
    }
}
