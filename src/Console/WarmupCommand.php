<?php

namespace Jderusse\Warmup\Console;

use Composer\Command\BaseCommand;
use Jderusse\Warmup\Classmap\ClassmapReader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

class WarmupCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('warmup-opcode')
            ->setDescription('Warmup the application\'s OpCode')
            ->addArgument('extra', InputArgument::IS_ARRAY, 'add extra path to compile');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!extension_loaded('Zend OPcache')) {
            throw new \RuntimeException('You have to enable opcache to use this commande');
        }

        if (!(bool) ini_get('opcache.enable_cli')) {
            throw new \RuntimeException('You have to enable the opcache extension');
        }

        $opcacheDir = ini_get('opcache.file_cache');
        if (empty($opcacheDir)) {
            throw new \RuntimeException('You have to define a file_cache to use');
        }

        if (!is_dir($opcacheDir)) {
            throw new \RuntimeException(sprintf('You have to create the "%s" directory', $opcacheDir));
        }

        if ((int) ini_get('opcache.file_update_protection') > 0) {
            $output->writeln(
                '<warning>You should set the `opcache.file_update_protection` to 0 in order to cache recently updated files</warning>'
            );
        }

        $composer = $this->getComposer();
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();

        $generator = $composer->getAutoloadGenerator();
        $generator->setDevMode(true);

        $reader = new ClassmapReader(
            $composer->getInstallationManager(),
            $generator,
            $composer->getConfig()
        );

        $files = $reader->getFilesByPackage(
            $composer->getPackage(),
            $localRepo->getCanonicalPackages(),
            $input->getArgument('extra')
        );
        $phpFinder = new PhpExecutableFinder();
        $phpPath = $phpFinder->find();
        foreach ($files as $file) {
            $process = ProcessBuilder::create([$phpPath, '-l', $file])->getProcess();
            if (0 == $process->run()) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $output->writeln(sprintf('<info>Compiled file <comment>%s</comment></info>', $file));
                }
                continue;
            }
            $output->writeln(
                sprintf(
                    '<error>Failed to compile file <comment>%s</comment> : <info>%s</info></error>',
                    $file,
                    $process->getOutput().' '.$process->getErrorOutput()
                )
            );
        }
    }
}
