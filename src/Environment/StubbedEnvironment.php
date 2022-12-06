<?php

declare(strict_types=1);

namespace TwigCsFixer\Environment;

use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bridge\Twig\TokenParser\FormThemeTokenParser;
use Symfony\Bridge\Twig\TokenParser\StopwatchTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Extra\Cache\TokenParser\CacheTokenParser;
use Twig\Loader\ArrayLoader;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

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

        $this->addTokenParser(new DumpTokenParser());
        $this->addTokenParser(new FormThemeTokenParser());
        $this->addTokenParser(new StopwatchTokenParser(true));
        $this->addTokenParser(new TransDefaultDomainTokenParser());
        $this->addTokenParser(new TransTokenParser());

        // Optional dependency
        if (class_exists(CacheTokenParser::class)) {
            $this->addTokenParser(new CacheTokenParser());
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
            $this->stubFilters[$name] = new TwigFilter($name);
        }

        return $this->stubFilters[$name];
    }

    /**
     * @param string $name
     */
    public function getFunction($name): ?TwigFunction
    {
        if (!\array_key_exists($name, $this->stubFunctions)) {
            $this->stubFunctions[$name] = new TwigFunction($name);
        }

        return $this->stubFunctions[$name];
    }

    /**
     * @param string $name
     */
    public function getTest($name): ?TwigTest
    {
        if (!\array_key_exists($name, $this->stubTests)) {
            $this->stubTests[$name] = new TwigTest($name);
        }

        return $this->stubTests[$name];
    }
}
