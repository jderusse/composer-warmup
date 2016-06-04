<?php

declare (strict_types = 1);

namespace Jderusse\Warmup\ClassmapReader;

interface ReaderInterface
{
    public function getClassmap() : \Traversable;
}
