<?php

declare(strict_types=1);

namespace TwigCsFixer\Ruleset;

use JsonException;
use TwigCsFixer\Exception\CannotJsonEncodeException;
use TwigCsFixer\Sniff\ConfigurableSniffInterface;
use TwigCsFixer\Sniff\SniffInterface;
use TwigCsFixer\Standard\StandardInterface;

/**
 * Set of rules to be used by TwigCsFixer and contains all sniffs.
 */
final class Ruleset
{
    /**
     * @var array<class-string<SniffInterface>, SniffInterface>
     */
    private array $sniffs = [];

    /**
     * @throws CannotJsonEncodeException
     */
    public function serialize(): string
    {
        $sniffs = [];
        foreach ($this->getSniffs() as $sniff) {
            $sniffs[$sniff::class] = $sniff instanceof ConfigurableSniffInterface
                ? $sniff->getConfiguration()
                : null;
        }

        try {
            return json_encode($sniffs, \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw CannotJsonEncodeException::because($exception);
        }
    }

    /**
     * @return array<class-string<SniffInterface>, SniffInterface>
     */
    public function getSniffs(): array
    {
        return $this->sniffs;
    }

    /**
     * @return $this
     */
    public function addSniff(SniffInterface $sniff): self
    {
        $this->sniffs[$sniff::class] = $sniff;

        return $this;
    }

    /**
     * @param class-string<SniffInterface> $class
     *
     * @return $this
     */
    public function removeSniff(string $class): self
    {
        unset($this->sniffs[$class]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addStandard(StandardInterface $standard): self
    {
        foreach ($standard->getSniffs() as $sniff) {
            $this->addSniff($sniff);
        }

        return $this;
    }
}
