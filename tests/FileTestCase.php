<?php

namespace TwigCsFixer\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;

class FileTestCase extends TestCase
{
    private const TMP_DIR = 'twig-cs-fixer';

    private string $cwd;

    private ?string $dir = null;

    protected function setUp(): void
    {
        parent::setUp();

        $fixtureDir = $this->getDir().'/Fixtures';
        $tmpFixtures = $this->getTmpPath($fixtureDir);

        $fs = new Filesystem();
        $fs->remove($tmpFixtures);

        if ($fs->exists($fixtureDir)) {
            $fs->mirror($fixtureDir, $tmpFixtures);
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
        if (strpos($path, $this->getDir()) !== 0) {
            throw new InvalidArgumentException(sprintf('The path "%s" is not supported', $path));
        }

        return str_replace($this->getDir(), realpath('/tmp/'.self::TMP_DIR), $path);
    }

    private function getDir(): string
    {
        if ($this->dir === null) {
            $reflectionClass = new ReflectionClass($this);
            $fileName = $reflectionClass->getFileName();
            static::assertNotFalse($fileName);

            $this->dir = dirname($fileName);
        }

        return $this->dir;
    }
}
