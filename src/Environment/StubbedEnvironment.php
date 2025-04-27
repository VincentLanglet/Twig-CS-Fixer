<?php

declare(strict_types=1);

namespace TwigCsFixer\Environment;

use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bridge\Twig\TokenParser\FormThemeTokenParser;
use Symfony\Bridge\Twig\TokenParser\StopwatchTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\UX\TwigComponent\Twig\ComponentLexer;
use Symfony\UX\TwigComponent\Twig\ComponentTokenParser as TwigComponentTokenParser;
use Symfony\UX\TwigComponent\Twig\PropsTokenParser;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Extra\Cache\TokenParser\CacheTokenParser;
use Twig\Loader\ArrayLoader;
use Twig\NodeVisitor\NodeVisitorInterface;
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
        'same' => null, // Allow 'same as'
    ];

    /**
     * @param ExtensionInterface[]   $customTwigExtensions
     * @param TokenParserInterface[] $customTokenParsers
     * @param NodeVisitorInterface[] $customNodeVisitors
     */
    public function __construct(
        array $customTwigExtensions = [],
        array $customTokenParsers = [],
        array $customNodeVisitors = [],
    ) {
        parent::__construct(new ArrayLoader());

        $this->handleOptionalDependencies();

        foreach ($customTwigExtensions as $customTwigExtension) {
            $this->addExtension($customTwigExtension);
        }

        foreach ($customTokenParsers as $customTokenParser) {
            $this->addTokenParser($customTokenParser);
        }

        foreach ($customNodeVisitors as $customNodeVisitor) {
            $this->addNodeVisitor($customNodeVisitor);
        }
    }

    /**
     * Avoid dependency to composer/semver for twig version comparison.
     */
    public static function satisfiesTwigVersion(int $major, int $minor = 0, int $patch = 0): bool
    {
        $version = explode('.', self::VERSION);

        if ($major < $version[0]) {
            return true;
        }
        if ($major > $version[0]) {
            return false;
        }
        if ($minor < $version[1]) {
            return true;
        }
        if ($minor > $version[1]) {
            return false;
        }

        return $version[2] >= $patch;
    }

    /**
     * @param string $name
     */
    public function getFilter($name): ?TwigFilter
    {
        if (!\array_key_exists($name, $this->stubFilters)) {
            // @phpstan-ignore-next-line method.internal
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
            // @phpstan-ignore-next-line method.internal
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
            // @phpstan-ignore-next-line method.internal
            $existingTest = parent::getTest($name);
            $this->stubTests[$name] = $existingTest instanceof TwigTest
                ? $existingTest
                : new TwigTest($name);
        }

        return $this->stubTests[$name];
    }

    private function handleOptionalDependencies(): void
    {
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
        // @phpstan-ignore-next-line classConstant.internalClass
        if (class_exists(TwigComponentTokenParser::class)) {
            $this->addTokenParser(new ComponentTokenParser());
        }
        // @phpstan-ignore-next-line classConstant.internalClass
        if (class_exists(PropsTokenParser::class)) {
            // @phpstan-ignore-next-line new.internalClass
            $this->addTokenParser(new PropsTokenParser());
        }
        // @phpstan-ignore-next-line classConstant.internalClass
        if (class_exists(ComponentLexer::class)) {
            // @phpstan-ignore-next-line new.internalClass
            $this->setLexer(new ComponentLexer($this));
        }
    }
}
