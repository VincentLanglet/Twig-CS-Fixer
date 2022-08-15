<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Environment;

use PHPUnit\Framework\TestCase;
use Twig\Source;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use TwigCsFixer\Environment\StubbedEnvironment;

class StubbedEnvironmentTest extends TestCase
{
    public function testFilterIsStubbed(): void
    {
        $env = new StubbedEnvironment();

        self::assertInstanceOf(TwigFilter::class, $env->getFilter('foo'));
    }

    public function testFunctionIsStubbed(): void
    {
        $env = new StubbedEnvironment();

        self::assertInstanceOf(TwigFunction::class, $env->getFunction('foo'));
    }

    public function testTestIsStubbed(): void
    {
        $env = new StubbedEnvironment();

        self::assertInstanceOf(TwigTest::class, $env->getTest('foo'));

        self::assertNull($env->getTest('divisible')); // To not conflict with `divisible by`
        self::assertNull($env->getTest('same')); // To not conflict with `same as`
    }

    public function testParse(): void
    {
        $content = file_get_contents(__DIR__.'/Fixtures/tags.html.twig');
        self::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'tags.html.twig');

        $env->parse($env->tokenize($source));
    }
}
