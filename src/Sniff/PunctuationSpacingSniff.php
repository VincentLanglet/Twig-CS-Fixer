<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure there is no space before and after a punctuation except for ':' and ','.
 */
final class PunctuationSpacingSniff extends AbstractSpacingSniff
{
    private const NO_SPACE_BEFORE = [')', ']', '}', ':', '.', ',', '|'];
    private const NO_SPACE_AFTER = ['(', '[', '{', '.', '|'];
    private const ONE_SPACE_AFTER = [':', ','];

    /**
     * @param list<Token> $tokens
     */
    protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, self::NO_SPACE_BEFORE)) {
            return 0;
        }

        return null;
    }

    /**
     * @param list<Token> $tokens
     */
    protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];

        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, self::NO_SPACE_AFTER)) {
            return 0;
        }

        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, self::ONE_SPACE_AFTER)) {
            // We cannot add one space after, if the next token need zero space before: `[1,2,3,]`.
            $nextPosition = $this->findNext(Token::WHITESPACE_TOKENS, $tokens, $tokenPosition + 1, true);
            if (false !== $nextPosition && null !== $this->getSpaceBefore($nextPosition, $tokens)) {
                return null;
            }

            return 1;
        }

        return null;
    }
}
