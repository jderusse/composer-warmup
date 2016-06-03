<?php

namespace Jderusse\Warmup\Classmap;

use Composer\Autoload\AutoloadGenerator;
use Composer\Autoload\ClassMapGenerator;
use Composer\Config;
use Composer\Installer\InstallationManager;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;

class ClassmapReader
{
    /** @var InstallationManager */
    private $installationManager;
    /** @var AutoloadGenerator */
    private $autoloadGenerator;
    /** @var Config */
    private $config;
    /** @var Filesystem */
    private $filesystem;
    /** @var string */
    private $basePath;

    public function __construct(
        InstallationManager $installationManager,
        AutoloadGenerator $autoloadGenerator,
        Config $config
    ) {
        $this->installationManager = $installationManager;
        $this->autoloadGenerator = $autoloadGenerator;

        $this->filesystem = new Filesystem();
        $this->basePath = $this->filesystem->normalizePath(realpath(getcwd()));
    }

    public function getFilesByPackage(PackageInterface $mainPackage, array $packages, array $dirs = [])
    {
        $autoloads = $this->getAutoloads($mainPackage, $packages);

        $blacklist = null;
        if (!empty($autoloads['exclude-from-classmap'])) {
            $blacklist = '{('.implode('|', $autoloads['exclude-from-classmap']).')}';
        }

        foreach ($dirs as $dir) {
            yield from $this->getFilesByDir($dir);
        }

        foreach ($this->getNamespaces($autoloads) as list($namespace, $paths)) {
            foreach ($paths as $dir) {
                $dir = $this->normalize($dir);
                if (!is_dir($dir)) {
                    continue;
                }

                $namespaceFilter = $namespace === '' ? null : $namespace;
                yield from $this->getFilesByDir($dir, $blacklist, $namespaceFilter);
            }
        }

        foreach ($autoloads['classmap'] as $dir) {
            yield from $this->getFilesByDir($dir, $blacklist);
        }
    }

    public function getFilesByDir($dir, $blacklist = null, $namespaceFilter = null)
    {
        foreach ($this->generateClassMap($dir, $blacklist, $namespaceFilter) as $class => $path) {
            yield $class => $this->normalize($path);
        }
    }

    private function getAutoloads(PackageInterface $mainPackage, array $packages)
    {
        $packageMap = $this->autoloadGenerator->buildPackageMap(
            $this->installationManager,
            $mainPackage,
            $packages
        );

        return $this->autoloadGenerator->parseAutoloads($packageMap, $mainPackage);
    }

    private function getNamespaces(array $autoloads)
    {
        // Scan the PSR-0/4 directories for class files, and add them to the class map
        foreach (['psr-0', 'psr-4'] as $psrType) {
            foreach ($autoloads[$psrType] as $namespace => $paths) {
                yield [$namespace, $paths];
            }
        }
    }

    private function generateClassMap($dir, $blacklist = null, $namespaceFilter = null)
    {
        return ClassMapGenerator::createMap($dir, $blacklist, null, $namespaceFilter);
    }

    private function normalize($path)
    {
        if (!$this->filesystem->isAbsolutePath($path)) {
            $path = $this->basePath.'/'.$path;
        }

        return $this->filesystem->normalizePath($path);
    }
}