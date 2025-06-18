<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Delimiter;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures there is one space before '}}', '%}' and '#}', and after '{{', '{%', '{#'.
 * Ensure there is no space inside empty comment `{##}` or `{#--#}.
 */
final class DelimiterSpacingRule extends AbstractSpacingRule implements ConfigurableRuleInterface
{
    public function __construct(bool $skipIfNewLine = true)
    {
        $this->skipIfNewLine = $skipIfNewLine;
    }

    public function getConfiguration(): array
    {
        return [
            'skipIfNewLine' => $this->skipIfNewLine,
        ];
    }

    protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (
            $token->isMatching([
                Token::BLOCK_END_TYPE,
                Token::VAR_END_TYPE,
            ])
        ) {
            return 1;
        }

        if ($token->isMatching(Token::COMMENT_END_TYPE)) {
            $previous = $tokens->findPrevious(Token::INDENT_TOKENS + Token::EOL_TOKENS, $tokenIndex - 1, exclude: true);
            if (
                false !== $previous
                && $tokens->get($previous)->isMatching(Token::COMMENT_START_TYPE)
                // We cannot fix `{# -#}` since `{#-#}` means `{#- #}`
                && \strlen($tokens->get($previous)->getValue()) === \strlen($token->getValue())
            ) {
                return 0;
            }

            return 1;
        }

        return null;
    }

    protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (
            $token->isMatching([
                Token::BLOCK_START_TYPE,
                Token::VAR_START_TYPE,
            ])
        ) {
            return 1;
        }

        if ($token->isMatching(Token::COMMENT_START_TYPE)) {
            $next = $tokens->findNext(Token::INDENT_TOKENS + Token::EOL_TOKENS, $tokenIndex + 1, exclude: true);
            if (
                false !== $next
                && $tokens->get($next)->isMatching(Token::COMMENT_END_TYPE)
                // We cannot fix `{# -#}` since `{#-#}` means `{#- #}`
                && \strlen($tokens->get($next)->getValue()) === \strlen($token->getValue())
            ) {
                return 0;
            }

            return 1;
        }

        return null;
    }
}
