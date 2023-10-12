<?php

declare(strict_types=1);

namespace TwigCsFixer\Environment;

use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bridge\Twig\TokenParser\FormThemeTokenParser;
use Symfony\Bridge\Twig\TokenParser\StopwatchTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\UX\TwigComponent\Twig\PropsTokenParser;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Extra\Cache\TokenParser\CacheTokenParser;
use Twig\Loader\ArrayLoader;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use TwigCsFixer\Environment\Parser\ComponentTokenParser;

/**
 * Provide stubs for all filters, functions, tests and tags that are not defined in twig's core.
 */
final class StubbedEnvironment extends Environment
{
    /**
     * @var array<string, TwigFilter|null>
     */
    private array $stubFilters = [];

    /**
     * @var array<string, TwigFunction|null>
     */
    private array $stubFunctions = [];

    /**
     * @var array<string, TwigTest|null>
     */
    private array $stubTests = [
        'divisible' => null, // Allow 'divisible by'
        'same'      => null, // Allow 'same as'
    ];

    /**
     * @param ExtensionInterface[]   $customTwigExtensions
     * @param TokenParserInterface[] $customTokenParsers
     */
    public function __construct(
        array $customTwigExtensions = [],
        array $customTokenParsers = []
    ) {
        parent::__construct(new ArrayLoader());

        // Optional dependency
        if (class_exists(DumpTokenParser::class)) {
            $this->addTokenParser(new DumpTokenParser());
        }
        if (class_exists(FormThemeTokenParser::class)) {
            $this->addTokenParser(new FormThemeTokenParser());
        }
        if (class_exists(StopwatchTokenParser::class)) {
            $this->addTokenParser(new StopwatchTokenParser(true));
        }
        if (class_exists(TransDefaultDomainTokenParser::class)) {
            $this->addTokenParser(new TransDefaultDomainTokenParser());
        }
        if (class_exists(TransTokenParser::class)) {
            $this->addTokenParser(new TransTokenParser());
        }
        if (class_exists(CacheTokenParser::class)) {
            $this->addTokenParser(new CacheTokenParser());
        }
        if (class_exists(TwigComponentBundle::class)) {
            $this->addTokenParser(new ComponentTokenParser());
            if (class_exists(PropsTokenParser::class)) {
                /** @psalm-suppress InternalClass */
                $this->addTokenParser(new PropsTokenParser());
            }
        }

        foreach ($customTwigExtensions as $customTwigExtension) {
            $this->addExtension($customTwigExtension);
        }

        foreach ($customTokenParsers as $customTokenParser) {
            $this->addTokenParser($customTokenParser);
        }
    }

    /**
     * @param string $name
     */
    public function getFilter($name): ?TwigFilter
    {
        if (!\array_key_exists($name, $this->stubFilters)) {
            /** @psalm-suppress InternalMethod */
            $existingFilter = parent::getFilter($name);
            $this->stubFilters[$name] = $existingFilter instanceof TwigFilter
                ? $existingFilter
                : new TwigFilter($name);
        }

        return $this->stubFilters[$name];
    }

    /**
     * @param string $name
     */
    public function getFunction($name): ?TwigFunction
    {
        if (!\array_key_exists($name, $this->stubFunctions)) {
            /** @psalm-suppress InternalMethod */
            $existingFunction = parent::getFunction($name);
            $this->stubFunctions[$name] = $existingFunction instanceof TwigFunction
                ? $existingFunction
                : new TwigFunction($name);
        }

        return $this->stubFunctions[$name];
    }

    /**
     * @param string $name
     */
    public function getTest($name): ?TwigTest
    {
        if (!\array_key_exists($name, $this->stubTests)) {
            /** @psalm-suppress InternalMethod */
            $existingTest = parent::getTest($name);
            $this->stubTests[$name] = $existingTest instanceof TwigTest
                ? $existingTest
                : new TwigTest($name);
        }

        return $this->stubTests[$name];
    }
}
