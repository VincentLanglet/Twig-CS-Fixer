<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Comment;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;

/**
 * Change a variable comment "{# @var var_name type" with "{% types var_name: 'type' %}".
 */
final class CommentTypeRule extends AbstractFixableRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        var_dump($tokenPosition, $tokens);
        $token = $tokens[$tokenPosition];
        var_dump($token);

        // Listen COMMENT_TEXT_TYPE https://github.com/VincentLanglet/Twig-CS-Fixer/blob/main/src/Token/Token.php#L42
        // If the value is not @var return

        if (!$this->isTokenMatching($token, Token::COMMENT_START_TYPE, '@var')) {
            return;
        }

        // is warning the proper violation "level" here?
        $fixer = $this->addFixableWarning(
            'Variable comment declaration must be used via types tag instead of comment.',
            $token
        );
        if (null === $fixer) {
            return;
        }

        $tokenValue = $token->getValue();
        var_dump($tokenValue);

        $firstTextType = $this->findNext(Token::COMMENT_TEXT_TYPE, $tokens, $tokenPosition);
        var_dump($firstTextType);
        $secondTextType = $this->findNext(Token::COMMENT_TEXT_TYPE, $tokens, $tokenPosition);
        var_dump($secondTextType);
        $thirdTextType = $this->findNext(Token::COMMENT_TEXT_TYPE, $tokens, $tokenPosition);
        var_dump($thirdTextType);

        // TODO

        /*

        Else parse the whole comment, looking for COMMENT_START_TYPE before and COMMENT_END_TYPE

COMMENT_START_TYPE
COMMENT_WHITESPACE_TYPE
COMMENT_TEXT_TYPE
COMMENT_WHITESPACE_TYPE
COMMENT_TEXT_TYPE
COMMENT_WHITESPACE_TYPE
COMMENT_TEXT_TYPE
COMMENT_WHITESPACE_TYPE
COMMENT_END_TYPE

        Rewrite it

{% types foo: 'bar' %}
where foo is the 2nd COMMENT_TEXT_TYPE and bar the third.

        */

        $variableName = $secondTextType;
        $variableType = $thirdTextType;

        $fixer->replaceToken(
            $tokenPosition,
            \sprintf("{% types %s: '%s'", $variableName, $variableType)
        );
    }
}
