<?php

declare (strict_types = 1);

namespace Jderusse\Warmup\ClassmapReader;

use Composer\Autoload\ClassMapGenerator;
use Composer\Util\Filesystem;

class DirectoryReader implements ReaderInterface
{
    /** @var string */
    private $directory;
    /** @var Filesystem */
    private $filesystem;
    /** @var string */
    private $basePath;

    public function __construct(string $directory)
    {
        $this->directory = $directory;

        $this->filesystem = new Filesystem();
        $this->basePath = $this->filesystem->normalizePath(realpath(getcwd()));
    }

    public function getClassmap() : \Traversable
    {
        foreach (ClassMapGenerator::createMap($this->directory) as $class => $path) {
            yield $class => $this->normalize($path);
        }
    }

    private function normalize(string $path) : string
    {
        if (!$this->filesystem->isAbsolutePath($path)) {
            $path = $this->basePath.'/'.$path;
        }

        return $this->filesystem->normalizePath($path);
    }
}
