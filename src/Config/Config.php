<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Symfony\Component\Finder\Finder;
use Twig\Extension\ExtensionInterface;
use Twig\TokenParser\TokenParserInterface;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\File\Finder as TwigCsFinder;
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
     * @var list<ExtensionInterface>
     */
    private array $twigExtensions = [];

    /**
     * @var list<TokenParserInterface>
     */
    private array $tokenParsers = [];

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

    public function setCacheFile(?string $cacheFile): self
    {
        $this->cacheFile = $cacheFile;

        return $this;
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
