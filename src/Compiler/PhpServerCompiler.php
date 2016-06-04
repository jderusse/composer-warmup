<?php

declare (strict_types = 1);

namespace Jderusse\Warmup\Compiler;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class PhpServerCompiler implements CompilerInterface
{
    /** @var string */
    private $address;
    /** @var Process */
    private $process;

    public function __construct(array $portRange = [8000, 8999])
    {
        $port = $this->findPort($portRange);
        $this->address = sprintf('127.0.0.1:%d', $port);

        $this->startServer();
    }

    public function startServer()
    {
        $finder = new PhpExecutableFinder();
        if (false === $binary = $finder->find()) {
            throw new \RuntimeException('Unable to find PHP binary to run server.');
        }

        $builder = new ProcessBuilder(
            ['exec', $binary, '-S', $this->address, realpath(__DIR__.'/../Resource/server.php')]
        );
        $builder->setWorkingDirectory(realpath(__DIR__.'/../Resource'));
        $builder->setTimeout(null);
        $this->process = $builder->getProcess();
        $this->process->start();

        $this->waitServer();
    }

    public function waitServer(int $timeout = 10)
    {
        $start = time();
        while (time() - $start <= $timeout) {
            usleep(10000);
            if (!$this->process->isRunning()) {
                continue;
            }
            try {
                file_get_contents(sprintf('http://%s/', $this->address));

                return true;
            } catch (\Throwable $e) {
            }
        }

        throw new \RuntimeException('Server is not responding');
    }

    public function __destruct()
    {
        $this->stopServer();
    }

    public function stopServer()
    {
        if ($this->process and $this->process->isRunning()) {
            $this->process->stop(0);
        }
    }

    public function compile(string $file)
    {
        file_get_contents(sprintf('http://%s/?file=%s', $this->address, urlencode($file)));
    }

    private function findPort(array $portRange)
    {
        foreach (range(...$portRange) as $port) {
            if ($this->isPortAvailable($port)) {
                return $port;
            }
        }

        throw new \RuntimeException(sprintf('Unable to find a suitable port in the range %d..%d', ...$portRange));
    }

    private function isPortAvailable(int $port) : bool
    {
        $h = null;
        try {
            $h = socket_create_listen($port);
            if ($h !== false) {
                return true;
            }
        } catch (\ErrorException $e) {
            // just ignore exception port already in use
        } finally {
            if (is_resource($h)) {
                socket_close($h);
            }
        }

        return false;
    }
}
