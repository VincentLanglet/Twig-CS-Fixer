<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Symfony\Component\Finder\Finder;
use Twig\Extension\ExtensionInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\File\Finder as TwigCsFinder;
use TwigCsFixer\Report\Reporter\ReporterInterface;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\TwigCsFixer;

/**
 * Main entry point to config the TwigCsFixer.
 */
final class Config
{
    public const DEFAULT_PATH = '.twig-cs-fixer.php';
    public const DEFAULT_DIST_PATH = '.twig-cs-fixer.dist.php';
    public const DEFAULT_CACHE_PATH = '.twig-cs-fixer.cache';

    private Ruleset $ruleset;

    private Finder $finder;

    private ?string $cacheFile = self::DEFAULT_CACHE_PATH;

    private ?CacheManagerInterface $cacheManager = null;

    /**
     * @var list<ReporterInterface>
     */
    private array $customReporters = [];

    /**
     * @var list<ExtensionInterface>
     */
    private array $twigExtensions = [];

    /**
     * @var list<TokenParserInterface>
     */
    private array $tokenParsers = [];

    /**
     * @var list<NodeVisitorInterface>
     */
    private array $nodeVisitors = [];

    private bool $allowNonFixableRules = false;

    public function __construct(private string $name = 'Default')
    {
        $this->ruleset = new Ruleset();
        $this->ruleset->addStandard(new TwigCsFixer());
        $this->finder = new TwigCsFinder();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRuleset(): Ruleset
    {
        return $this->ruleset;
    }

    /**
     * @return $this
     */
    public function setRuleset(Ruleset $ruleset): self
    {
        $this->ruleset = $ruleset;

        return $this;
    }

    public function getFinder(): Finder
    {
        return $this->finder;
    }

    /**
     * @return $this
     */
    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    public function getCacheManager(): ?CacheManagerInterface
    {
        return $this->cacheManager;
    }

    /**
     * @return $this
     */
    public function setCacheManager(?CacheManagerInterface $cacheManager): self
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }

    public function getCacheFile(): ?string
    {
        return $this->cacheFile;
    }

    /**
     * @return $this
     */
    public function setCacheFile(?string $cacheFile): self
    {
        $this->cacheFile = $cacheFile;

        return $this;
    }

    public function addCustomReporter(ReporterInterface $reporter): self
    {
        $this->customReporters[] = $reporter;

        return $this;
    }

    /**
     * @return list<ReporterInterface>
     */
    public function getCustomReporters(): array
    {
        return $this->customReporters;
    }

    /**
     * @return $this
     */
    public function addTwigExtension(ExtensionInterface $extension): self
    {
        $this->twigExtensions[] = $extension;

        return $this;
    }

    /**
     * @return list<ExtensionInterface>
     */
    public function getTwigExtensions(): array
    {
        return $this->twigExtensions;
    }

    /**
     * @return $this
     */
    public function addTokenParser(TokenParserInterface $tokenParser): self
    {
        $this->tokenParsers[] = $tokenParser;

        return $this;
    }

    /**
     * @return list<TokenParserInterface>
     */
    public function getTokenParsers(): array
    {
        return $this->tokenParsers;
    }

    /**
     * @return $this
     */
    public function addNodeVisitor(NodeVisitorInterface $nodeVisitor): self
    {
        $this->nodeVisitors[] = $nodeVisitor;

        return $this;
    }

    /**
     * @return list<NodeVisitorInterface>
     */
    public function getNodeVisitors(): array
    {
        return $this->nodeVisitors;
    }

    /**
     * @return $this
     */
    public function allowNonFixableRules(bool $allowNonFixableRules = true): self
    {
        $this->allowNonFixableRules = $allowNonFixableRules;

        return $this;
    }

    public function areNonFixableRulesAllowed(): bool
    {
        return $this->allowNonFixableRules;
    }
}
