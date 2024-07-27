<?php

declare(strict_types=1);

namespace TwigCsFixer\Runner;

use Twig\Source;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Rules\FixableRuleInterface;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\TokenizerInterface;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Fixer will fix twig files against a set of rules.
 */
final class Fixer implements FixerInterface
{
    public const MAX_FIXER_ITERATION = 50;

    private int $loops = 0;

    private string $eolChar = \PHP_EOL;

    /**
     * The list of tokens that make up the file contents.
     *
     * This is a simplified list which just contains the token content and nothing else.
     * This is the array that is updated as fixes are made, not the file's token array.
     * Imploding this array will give you the file content back.
     *
     * @var array<int, string>
     */
    private array $tokens = [];

    /**
     * A list of tokens that have already been fixed.
     *
     * We don't allow the same token to be fixed more than once each time through a file
     * as this can easily cause conflicts between rules.
     *
     * @var array<int, string>
     */
    private array $fixedTokens = [];

    /**
     * The last value of each fixed token.
     *
     * If a token is being "fixed" back to its last value, the fix is probably conflicting with another.
     *
     * @var array<int, array{curr: string, prev: string, loop: int}>
     */
    private array $oldTokenValues = [];

    /**
     * A list of tokens that have been fixed during a change set.
     *
     * All changes in change set must be able to be applied, or else the entire change set is rejected.
     *
     * @var array<int, string>
     */
    private array $changeSet = [];

    /**
     * Is there an open change set.
     */
    private bool $inChangeSet = false;

    /**
     * Is the current fixing loop in conflict?
     */
    private bool $inConflict = false;

    public function __construct(private TokenizerInterface $tokenizer)
    {
    }

    public function fixFile(string $content, Ruleset $ruleset): string
    {
        $this->loops = 0;
        do {
            $this->inConflict = false;

            $twigSource = new Source($content, 'TwigCsFixer');
            $stream = $this->tokenizer->tokenize($twigSource);

            $this->startFile($stream);

            $rules = $ruleset->getRules();
            foreach ($rules as $rule) {
                if ($rule instanceof FixableRuleInterface) {
                    $rule->fixFile($stream, $this);
                }
            }

            ++$this->loops;
            $content = $this->getContent();
            $numFixes = \count($this->fixedTokens);
        } while (
            (0 !== $numFixes || $this->inConflict)
            && $this->loops < self::MAX_FIXER_ITERATION
        );

        if ($numFixes > 0) {
            throw CannotFixFileException::infiniteLoop();
        }

        return $content;
    }

    public function beginChangeSet(): void
    {
        if ($this->inChangeSet) {
            throw new \BadMethodCallException('Already in change set.');
        }

        $this->changeSet = [];
        $this->inChangeSet = true;
    }

    public function endChangeSet(): void
    {
        if (!$this->inChangeSet) {
            throw new \BadMethodCallException('There is no current change set.');
        }

        $this->inChangeSet = false;

        if (!$this->inConflict) {
            $applied = [];
            foreach ($this->changeSet as $tokenIndex => $content) {
                $success = $this->replaceToken($tokenIndex, $content);
                if (!$success) {
                    // Rolling back all changes.
                    foreach ($applied as $appliedTokenIndex) {
                        $this->revertToken($appliedTokenIndex);
                    }
                    break;
                }

                $applied[] = $tokenIndex;
            }
        }

        $this->changeSet = [];
    }

    public function replaceToken(int $tokenIndex, string $content): bool
    {
        if ($this->inConflict) {
            return false;
        }

        if (!$this->inChangeSet && isset($this->fixedTokens[$tokenIndex])) {
            return false;
        }

        if ($this->inChangeSet) {
            $this->changeSet[$tokenIndex] = $content;

            return true;
        }

        if (!isset($this->oldTokenValues[$tokenIndex])) {
            $this->oldTokenValues[$tokenIndex] = [
                'prev' => $this->tokens[$tokenIndex],
                'curr' => $content,
                'loop' => $this->loops,
            ];
        } elseif (
            $content === $this->oldTokenValues[$tokenIndex]['prev']
            && ($this->loops - 1) === $this->oldTokenValues[$tokenIndex]['loop']
        ) {
            $this->inConflict = true;

            return false;
        } else {
            $this->oldTokenValues[$tokenIndex]['prev'] = $this->oldTokenValues[$tokenIndex]['curr'];
            $this->oldTokenValues[$tokenIndex]['curr'] = $content;
            $this->oldTokenValues[$tokenIndex]['loop'] = $this->loops;
        }

        $this->fixedTokens[$tokenIndex] = $this->tokens[$tokenIndex];
        $this->tokens[$tokenIndex] = $content;

        return true;
    }

    public function addNewline(int $tokenIndex): bool
    {
        $current = $this->getTokenContent($tokenIndex);

        return $this->replaceToken($tokenIndex, $current.$this->eolChar);
    }

    public function addNewlineBefore(int $tokenIndex): bool
    {
        $current = $this->getTokenContent($tokenIndex);

        return $this->replaceToken($tokenIndex, $this->eolChar.$current);
    }

    public function addContent(int $tokenIndex, string $content): bool
    {
        $current = $this->getTokenContent($tokenIndex);

        return $this->replaceToken($tokenIndex, $current.$content);
    }

    public function addContentBefore(int $tokenIndex, string $content): bool
    {
        $current = $this->getTokenContent($tokenIndex);

        return $this->replaceToken($tokenIndex, $content.$current);
    }

    private function startFile(Tokens $tokens): void
    {
        $this->fixedTokens = [];

        $this->tokens = array_map(static fn (Token $token): string => $token->getValue(), $tokens->toArray());

        $this->eolChar = FileHelper::detectEOL($this->getContent());
    }

    private function getContent(): string
    {
        return implode('', $this->tokens);
    }

    /**
     * This function takes change sets into account so should be used
     * instead of directly accessing the token array.
     */
    private function getTokenContent(int $tokenIndex): string
    {
        if ($this->inChangeSet && isset($this->changeSet[$tokenIndex])) {
            return $this->changeSet[$tokenIndex];
        }

        return $this->tokens[$tokenIndex];
    }

    private function revertToken(int $tokenIndex): void
    {
        $errorMessage = \sprintf('Nothing to revert at index %s', $tokenIndex);
        Assert::keyExists($this->fixedTokens, $tokenIndex, $errorMessage);

        $this->tokens[$tokenIndex] = $this->fixedTokens[$tokenIndex];
        unset($this->fixedTokens[$tokenIndex]);
    }
}
