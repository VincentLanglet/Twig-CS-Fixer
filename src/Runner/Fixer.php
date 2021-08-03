<?php

declare(strict_types=1);

namespace TwigCsFixer\Runner;

use Exception;
use Twig\Source;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;

/**
 * Fixer will fix twig files against a set of rules.
 */
final class Fixer
{
    /**
     * @var int
     */
    private $loops = 0;

    /**
     * @var string
     */
    private $eolChar = "\n";

    /**
     * @var Ruleset
     */
    private $ruleset;

    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * The list of tokens that make up the file contents.
     *
     * This is a simplified list which just contains the token content and nothing else.
     * This is the array that is updated as fixes are made, not the file's token array.
     * Imploding this array will give you the file content back.
     *
     * @var array<int, string>
     */
    private $tokens = [];

    /**
     * A list of tokens that have already been fixed.
     *
     * We don't allow the same token to be fixed more than once each time through a file
     * as this can easily cause conflicts between sniffs.
     *
     * @var array<int, string>
     */
    private $fixedTokens = [];

    /**
     * The last value of each fixed token.
     *
     * If a token is being "fixed" back to its last value, the fix is probably conflicting with another.
     *
     * @var array<array{curr: string, prev: string, loop: int}>
     */
    private $oldTokenValues = [];

    /**
     * A list of tokens that have been fixed during a changeset.
     *
     * All changes in changeset must be able to be applied, or else the entire changeset is rejected.
     *
     * @var array<int, string>
     */
    private $changeset = [];

    /**
     * Is there an open changeset.
     *
     * @var bool
     */
    private $inChangeset = false;

    /**
     * Is the current fixing loop in conflict?
     *
     * @var bool
     */
    private $inConflict = false;

    /**
     * The number of fixes that have been performed.
     *
     * @var int
     */
    private $numFixes = 0;

    /**
     * @param Ruleset   $ruleset
     * @param Tokenizer $tokenizer
     *
     * @return void
     */
    public function __construct(Ruleset $ruleset, Tokenizer $tokenizer)
    {
        $this->ruleset = $ruleset;
        $this->tokenizer = $tokenizer;
    }

    /**
     * @param array<int, Token> $tokens
     *
     * @return void
     */
    public function startFile(array $tokens): void
    {
        $this->numFixes = 0;
        $this->fixedTokens = [];

        $this->tokens = array_map(static function (Token $token): string {
            return $token->getValue() ?? '';
        }, $tokens);

        if (preg_match("/\r\n?|\n/", $this->getContents(), $matches) !== 1) {
            // Assume there are no newlines.
            $this->eolChar = "\n";
        } else {
            $this->eolChar = $matches[0];
        }
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function fixFile(string $file): bool
    {
        $contents = file_get_contents($file);
        if (false === $contents) {
            return false;
        }

        $this->loops = 0;
        while ($this->loops < 50) {
            $this->inConflict = false;

            try {
                $twigSource = new Source($contents, 'TwigCsFixer');
                $stream = $this->tokenizer->tokenize($twigSource);
            } catch (Exception $exception) {
                return false;
            }

            $this->startFile($stream);

            $sniffs = $this->ruleset->getSniffs();
            foreach ($sniffs as $sniff) {
                $sniff->processFile($stream);
            }

            $this->loops++;

            if (0 === $this->numFixes && !$this->inConflict) {
                // Nothing left to do.
                break;
            }

            // Only needed once file content has changed.
            $contents = $this->getContents();
        }

        if ($this->numFixes > 0) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return implode($this->tokens);
    }

    /**
     * Start recording actions for a changeset.
     *
     * @return void
     */
    public function beginChangeset(): void
    {
        if ($this->inConflict) {
            return;
        }

        $this->changeset = [];
        $this->inChangeset = true;
    }

    /**
     * Stop recording actions for a changeset, and apply logged changes.
     *
     * @return void
     */
    public function endChangeset(): void
    {
        if ($this->inConflict) {
            return;
        }

        $this->inChangeset = false;

        $success = true;
        $applied = [];
        foreach ($this->changeset as $tokenPosition => $content) {
            $success = $this->replaceToken($tokenPosition, $content);
            if (!$success) {
                break;
            } else {
                $applied[] = $tokenPosition;
            }
        }

        if (!$success) {
            // Rolling back all changes.
            foreach ($applied as $tokenPosition) {
                $this->revertToken($tokenPosition);
            }
        }

        $this->changeset = [];
    }

    /**
     * Stop recording actions for a changeset, and discard logged changes.
     *
     * @return void
     */
    public function rollbackChangeset(): void
    {
        $this->inChangeset = false;
        $this->inConflict = false;

        if (count($this->changeset) > 0) {
            $this->changeset = [];
        }
    }

    /**
     * @param int    $tokenPosition
     * @param string $content
     *
     * @return bool
     */
    public function replaceToken(int $tokenPosition, string $content): bool
    {
        if ($this->inConflict) {
            return false;
        }

        if (!$this->inChangeset && isset($this->fixedTokens[$tokenPosition])) {
            return false;
        }

        if ($this->inChangeset) {
            $this->changeset[$tokenPosition] = $content;

            return true;
        }

        if (!isset($this->oldTokenValues[$tokenPosition])) {
            $this->oldTokenValues[$tokenPosition] = [
                'curr' => $content,
                'prev' => $this->tokens[$tokenPosition],
                'loop' => $this->loops,
            ];
        } else {
            if (
                $content === $this->oldTokenValues[$tokenPosition]['prev']
                && ($this->loops - 1) === $this->oldTokenValues[$tokenPosition]['loop']
            ) {
                if ($this->oldTokenValues[$tokenPosition]['loop'] >= ($this->loops - 1)) {
                    $this->inConflict = true;
                }

                return false;
            }

            $this->oldTokenValues[$tokenPosition]['prev'] = $this->oldTokenValues[$tokenPosition]['curr'];
            $this->oldTokenValues[$tokenPosition]['curr'] = $content;
            $this->oldTokenValues[$tokenPosition]['loop'] = $this->loops;
        }

        $this->fixedTokens[$tokenPosition] = $this->tokens[$tokenPosition];
        $this->tokens[$tokenPosition] = $content;
        $this->numFixes++;

        return true;
    }

    /**
     * @param int $tokenPosition
     *
     * @return bool
     */
    public function addNewline(int $tokenPosition): bool
    {
        $current = $this->getTokenContent($tokenPosition);

        return $this->replaceToken($tokenPosition, $current.$this->eolChar);
    }

    /**
     * @param int $tokenPosition
     *
     * @return bool
     */
    public function addNewlineBefore(int $tokenPosition): bool
    {
        $current = $this->getTokenContent($tokenPosition);

        return $this->replaceToken($tokenPosition, $this->eolChar.$current);
    }

    /**
     * @param int    $tokenPosition
     * @param string $content
     *
     * @return bool
     */
    public function addContent(int $tokenPosition, string $content): bool
    {
        $current = $this->getTokenContent($tokenPosition);

        return $this->replaceToken($tokenPosition, $current.$content);
    }

    /**
     * @param int    $tokenPosition
     * @param string $content
     *
     * @return bool
     */
    public function addContentBefore(int $tokenPosition, string $content): bool
    {
        $current = $this->getTokenContent($tokenPosition);

        return $this->replaceToken($tokenPosition, $content.$current);
    }

    /**
     * This function takes changesets into account so should be used
     * instead of directly accessing the token array.
     *
     * @param int $tokenPosition
     *
     * @return string
     */
    private function getTokenContent(int $tokenPosition): string
    {
        if ($this->inChangeset && isset($this->changeset[$tokenPosition])) {
            return $this->changeset[$tokenPosition];
        }

        return $this->tokens[$tokenPosition];
    }

    /**
     * @param int $tokenPosition
     *
     * @return bool
     */
    private function revertToken(int $tokenPosition): bool
    {
        if (!isset($this->fixedTokens[$tokenPosition])) {
            return false;
        }

        $this->tokens[$tokenPosition] = $this->fixedTokens[$tokenPosition];
        unset($this->fixedTokens[$tokenPosition]);
        $this->numFixes--;

        return true;
    }
}
