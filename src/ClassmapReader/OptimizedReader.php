<?php

declare (strict_types = 1);

namespace Jderusse\Warmup\ClassmapReader;

use Composer\Config;
use Composer\Util\Filesystem;

class OptimizedReader implements ReaderInterface
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getClassmap() : \Traversable
    {
        $filesystem = new Filesystem();
        $vendorPath = $filesystem->normalizePath(realpath($this->config->get('vendor-dir')));
        $classmapPath = $vendorPath.'/composer/autoload_classmap.php';

        if (!is_file($classmapPath)) {
            throw new \RuntimeException(
                'Th dumped classmap does not exists. Try to run `composer dump-autoload --optimize` first.'
            );
        }

        yield from include $vendorPath.'/composer/autoload_classmap.php';
    }
}
