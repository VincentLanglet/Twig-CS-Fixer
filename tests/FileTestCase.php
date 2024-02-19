<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;

abstract class FileTestCase extends TestCase
{
    private ?Filesystem $filesystem = null;

    private string $cwd;

    private ?string $tmp = null;

    private ?string $dir = null;

    protected function setUp(): void
    {
        parent::setUp();

        $fixtureDir = $this->getDir().\DIRECTORY_SEPARATOR.'Fixtures';
        $tmpFixtures = $this->getTmpPath($fixtureDir);

        if ($tmpFixtures !== $fixtureDir) {
            try {
                $this->getFilesystem()->remove($tmpFixtures);
            } catch (IOException) {
                // Ignore
            }

            if ($this->getFilesystem()->exists($fixtureDir)) {
                $this->getFilesystem()->mirror($fixtureDir, $tmpFixtures);
            }
        }

        $cwd = getcwd();
        Assert::notFalse($cwd); // Avoid doing a phpunit assertion

        $this->cwd = $cwd;
        chdir($this->getTmpPath($this->getDir()));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        chdir($this->cwd);
    }

    protected function getFilesystem(): Filesystem
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }

    protected function getTmpPath(string $path): string
    {
        $path = TestHelper::getOsPath($path);
        if (!str_starts_with($path, $this->getDir())) {
            return $path;
        }

        return str_replace($this->getDir(), $this->getTmp(), $path);
    }

    private function getDir(): string
    {
        if (null === $this->dir) {
            $reflectionClass = new \ReflectionClass($this);
            $fileName = $reflectionClass->getFileName();
            Assert::notFalse($fileName); // Avoid doing a phpunit assertion

            $this->dir = \dirname($fileName);
        }

        return $this->dir;
    }

    private function getTmp(): string
    {
        if (null === $this->tmp) {
            $tmp = realpath(sys_get_temp_dir());

            // On GitHub actions we cannot access the tmp dir
            if (false === $tmp) {
                $this->tmp = $this->getDir();
            } else {
                $this->tmp = $tmp.\DIRECTORY_SEPARATOR.'twig-cs-fixer';
            }
        }

        return $this->tmp;
    }
}
