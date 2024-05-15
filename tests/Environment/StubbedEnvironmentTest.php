<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Environment;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Node\DumpNode;
use Symfony\Bridge\Twig\Node\FormThemeNode;
use Symfony\Bridge\Twig\Node\StopwatchNode;
use Symfony\Bridge\Twig\Node\TransDefaultDomainNode;
use Symfony\Bridge\Twig\Node\TransNode;
use Twig\Node\TextNode;
use Twig\Source;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Tests\Environment\Fixtures\CustomTokenParser;
use TwigCsFixer\Tests\Environment\Fixtures\CustomTwigExtension;

final class StubbedEnvironmentTest extends TestCase
{
    public function testFilterIsStubbed(): void
    {
        $env = new StubbedEnvironment();

        $stub = $env->getFilter('foo');
        static::assertInstanceOf(TwigFilter::class, $stub);
        static::assertNull($stub->getCallable());
    }

    public function testExistingFilterIsNotStubbed(): void
    {
        $env = new StubbedEnvironment();

        $existing = $env->getFilter('length');
        static::assertInstanceOf(TwigFilter::class, $existing);
        static::assertNotNull($existing->getCallable());
    }

    public function testFunctionIsStubbed(): void
    {
        $env = new StubbedEnvironment();

        $stub = $env->getFunction('foo');
        static::assertInstanceOf(TwigFunction::class, $stub);
        static::assertNull($stub->getCallable());
    }

    public function testExistingFunctionIsNotStubbed(): void
    {
        $env = new StubbedEnvironment();

        $existing = $env->getFunction('include');
        static::assertInstanceOf(TwigFunction::class, $existing);
        static::assertNotNull($existing->getCallable());
    }

    public function testTestIsStubbed(): void
    {
        $env = new StubbedEnvironment();

        $stub = $env->getTest('foo');
        static::assertInstanceOf(TwigTest::class, $stub);
        static::assertNull($stub->getCallable());
    }

    public function testExistingTestIsNotStubbed(): void
    {
        $env = new StubbedEnvironment();

        $existing = $env->getTest('empty');
        static::assertInstanceOf(TwigTest::class, $existing);
        static::assertNotNull($existing->getCallable());
    }

    public function testSameAs(): void
    {
        $content = file_get_contents(__DIR__.'/Fixtures/same_as.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'same_as.html.twig');

        $env->parse($env->tokenize($source));
    }

    public function testDivisibleBy(): void
    {
        $content = file_get_contents(__DIR__.'/Fixtures/divisible_by.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'divisible_by.html.twig');

        $env->parse($env->tokenize($source));
    }

    public function testParse(): void
    {
        $content = file_get_contents(__DIR__.'/Fixtures/tags.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'tags.html.twig');

        $nodes = $env->parse($env->tokenize($source));

        $body = $nodes->getNode('body')->getNode('0');
        static::assertInstanceOf(FormThemeNode::class, $body->getNode('0'));
        static::assertInstanceOf(TextNode::class, $body->getNode('1'));
        static::assertInstanceOf(DumpNode::class, $body->getNode('2'));
        static::assertInstanceOf(TextNode::class, $body->getNode('3'));
        static::assertInstanceOf(StopwatchNode::class, $body->getNode('4'));
        static::assertInstanceOf(TextNode::class, $body->getNode('5'));
        static::assertInstanceOf(TransDefaultDomainNode::class, $body->getNode('6'));
        static::assertInstanceOf(TextNode::class, $body->getNode('7'));
        static::assertInstanceOf(TransNode::class, $body->getNode('8'));
    }

    public function testParseCacheTag(): void
    {
        if (!InstalledVersions::satisfies(new VersionParser(), 'twig/twig', '>=3.2.0')) {
            static::markTestSkipped('twig/twig ^3.2.0 is required.');
        }

        $content = file_get_contents(__DIR__.'/Fixtures/cache_tag.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'cache_tag.html.twig');

        $env->parse($env->tokenize($source));
    }

    public function testParseComponentTag(): void
    {
        if (!InstalledVersions::satisfies(new VersionParser(), 'symfony/ux-twig-component', '>=2.2.0')) {
            static::markTestSkipped('symfony/ux-twig-component ^2.2.0 is required.');
        }

        $content = file_get_contents(__DIR__.'/Fixtures/component_tag.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'component_tag.html.twig');

        $env->parse($env->tokenize($source));
    }

    public function testParsePropsTag(): void
    {
        if (!InstalledVersions::satisfies(new VersionParser(), 'symfony/ux-twig-component', '>=2.11.0')) {
            static::markTestSkipped('symfony/ux-twig-component >=2.11.0 is required.');
        }

        $content = file_get_contents(__DIR__.'/Fixtures/props_tag.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'props_tag.html.twig');

        $env->parse($env->tokenize($source));
    }

    public function testParseWithCustomTokenParser(): void
    {
        $content = file_get_contents(__DIR__.'/Fixtures/custom_tags.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment([], [new CustomTokenParser()]);
        $source = new Source($content, 'custom_tags.html.twig');

        $env->parse($env->tokenize($source));
    }

    public function testParseWithCustomTwigExtension(): void
    {
        $content = file_get_contents(__DIR__.'/Fixtures/custom_tags.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment([new CustomTwigExtension()]);
        $source = new Source($content, 'custom_tags.html.twig');

        $env->parse($env->tokenize($source));
    }

    public function testParseComponentSpreadOperator(): void
    {
        if (!InstalledVersions::satisfies(new VersionParser(), 'symfony/ux-twig-component', '>=2.11.0')) {
            static::markTestSkipped('symfony/ux-twig-component ^2.11.0 is required.');
        }

        $content = file_get_contents(__DIR__.'/Fixtures/component_lexer.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'component_lexer.html.twig');

        $env->parse($env->tokenize($source));
    }
}
