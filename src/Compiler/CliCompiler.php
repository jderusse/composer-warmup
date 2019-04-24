<?php

declare(strict_types=1);

namespace Jderusse\Warmup\Compiler;

final class CliCompiler implements CompilerInterface
{
    public function compile(string $file)
    {
        opcache_compile_file($file);
    }
}
