<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;

class FileTestCase extends TestCase
{
    private string $cwd;

    private ?string $tmp = null;

    private ?string $dir = null;

    protected function setUp(): void
    {
        parent::setUp();

        $fixtureDir = $this->getDir().'/Fixtures';
        $tmpFixtures = $this->getTmpPath($fixtureDir);

        if ($tmpFixtures !== $fixtureDir) {
            $fs = new Filesystem();
            $fs->remove($tmpFixtures);

            if ($fs->exists($fixtureDir)) {
                $fs->mirror($fixtureDir, $tmpFixtures);
            }
        }

        $cwd = getcwd();
        static::assertNotFalse($cwd);

        $this->cwd = $cwd;
        chdir($this->getTmpPath($this->getDir()));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        chdir($this->cwd);
    }

    protected function getTmpPath(string $path): string
    {
        if (0 !== strpos($path, $this->getDir())) {
            throw new InvalidArgumentException(sprintf('The path "%s" is not supported', $path));
        }

        return str_replace($this->getDir(), $this->getTmp(), $path);
    }

    private function getDir(): string
    {
        if (null === $this->dir) {
            $reflectionClass = new ReflectionClass($this);
            $fileName = $reflectionClass->getFileName();
            static::assertNotFalse($fileName);

            $this->dir = \dirname($fileName);
        }

        return $this->dir;
    }

    private function getTmp(): string
    {
        if (null === $this->tmp) {
            $tmp = realpath(sys_get_temp_dir().'/twig-cs-fixer');

            // On GitHub actions we cannot access the tmp dir
            if (false === $tmp) {
                $tmp = $this->getDir();
            }

            $this->tmp = $tmp;
        }

        return $this->tmp;
    }
}
