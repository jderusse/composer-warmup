<?php

declare (strict_types = 1);

namespace Jderusse\Warmup\ClassmapReader;

class ChainReader implements ReaderInterface
{
    /** @var array */
    private $readers;

    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    public function getClassmap() : \Traversable
    {
        /** @var ReaderInterface $reader */
        foreach ($this->readers as $reader) {
            yield from $reader->getClassmap();
        }
    }
}
