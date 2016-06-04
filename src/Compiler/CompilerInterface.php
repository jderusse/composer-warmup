<?php

declare (strict_types = 1);

namespace Jderusse\Warmup\Compiler;

interface CompilerInterface
{
    public function compile(string $file);
}
