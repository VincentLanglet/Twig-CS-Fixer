<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Environment;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Node\DumpNode;
use Symfony\Bridge\Twig\Node\FormThemeNode;
use Symfony\Bridge\Twig\Node\StopwatchNode;
use Symfony\Bridge\Twig\Node\TransDefaultDomainNode;
use Symfony\Bridge\Twig\Node\TransNode;
use Twig\Extra\Cache\Node\CacheNode;
use Twig\Extra\Cache\TokenParser\CacheTokenParser;
use Twig\Node\TextNode;
use Twig\Source;
use Twig\TwigFilter;
use Twig\TwigFunction;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Tests\Environment\Fixtures\CustomTokenParser;
use TwigCsFixer\Tests\Environment\Fixtures\CustomTwigExtension;

final class StubbedEnvironmentTest extends TestCase
{
    public function testFilterIsStubbed(): void
    {
        $env = new StubbedEnvironment();

        static::assertInstanceOf(TwigFilter::class, $env->getFilter('foo'));
    }

    public function testFunctionIsStubbed(): void
    {
        $env = new StubbedEnvironment();

        static::assertInstanceOf(TwigFunction::class, $env->getFunction('foo'));
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
        if (!class_exists(CacheTokenParser::class)) {
            static::markTestSkipped('The cache tag was added in Twig 3.2.');
        }

        $content = file_get_contents(__DIR__.'/Fixtures/cache_tag.html.twig');
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $source = new Source($content, 'tags.html.twig');

        $nodes = $env->parse($env->tokenize($source));
        $body = $nodes->getNode('body');
        static::assertInstanceOf(CacheNode::class, $body->getNode('0'));
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
}
