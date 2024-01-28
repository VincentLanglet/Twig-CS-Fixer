<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Function;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensures that include function is used instead of function tag.
 */
final class IncludeFunctionRule extends AbstractFixableRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::BLOCK_NAME_TYPE, 'include')) {
            return;
        }

        $fixer = $this->addFixableError(
            'Include function must be used instead of include tag.',
            $token
        );
        if (null === $fixer) {
            return;
        }

        $openingTag = $this->findPrevious(Token::BLOCK_START_TYPE, $tokens, $tokenPosition);
        Assert::notFalse($openingTag, 'Opening tag cannot be null.');

        $closingTag = $this->findNext(Token::BLOCK_END_TYPE, $tokens, $tokenPosition);
        Assert::notFalse($closingTag, 'Closing tag cannot be null.');

        $fixer->beginChangeSet();

        // Replace opening tag (and keep eventual whitespace modifiers)
        $fixer->replaceToken($openingTag, str_replace('{%', '{{', $tokens[$openingTag]->getValue()));
        $fixer->replaceToken($tokenPosition, 'include(');

        // Unlike tag, function doesn't require a whitespace after the opening parenthesis.
        if ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::WHITESPACE_TYPE)) {
            $fixer->replaceToken($tokenPosition + 1, '');
        }

        if ($this->isTokenMatching($tokens[$closingTag - 1], Token::WHITESPACE_TYPE)) {
            $closingTagSpace = $tokens[$closingTag - 1]->getValue();
        } else {
            $closingTagSpace = '';
        }

        $ignoreMissing = false;
        $withoutContext = false;
        $withVariable = false;
        $clearWhitespace = true;
        foreach (array_reverse(range($tokenPosition, $closingTag - 1)) as $position) {
            $token = $tokens[$position];
            if ($clearWhitespace && $this->isTokenMatching($token, Token::WHITESPACE_TYPE)) {
                $fixer->replaceToken($position, '');
            }
            $clearWhitespace = false;

            if ($this->isTokenMatching($token, Token::NAME_TYPE)) {
                switch ($token->getValue()) {
                    case 'with':
                        $withVariable = true;
                        $clearWhitespace = true;
                        $fixer->replaceToken($position, ',');
                        break;
                    case 'only':
                        $withoutContext = true;
                        $clearWhitespace = true;
                        $fixer->replaceToken($position, '');
                        break;
                    case 'ignore':
                        $ignoreMissing = true;
                        $clearWhitespace = true;
                        $fixer->replaceToken($position, '');
                        break;
                    case 'missing':
                        $clearWhitespace = true;
                        $fixer->replaceToken($position, '');
                        break;
                }
            }
        }

        $endInclude = ')'.$closingTagSpace.str_replace('%}', '}}', $tokens[$closingTag]->getValue());
        if ($ignoreMissing) {
            $endInclude = ', true'.$endInclude;
        }
        if ($ignoreMissing || $withoutContext) {
            $endInclude = sprintf(', %s', $withoutContext ? 'false' : 'true').$endInclude;

            if (!$withVariable) {
                $endInclude = ', []'.$endInclude;
            }
        }

        $fixer->replaceToken($closingTag, $endInclude);
        $fixer->endChangeSet();
    }
}
