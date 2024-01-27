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
        $ignoreMissing = false;
        $withoutContext = false;
        foreach (range($tokenPosition, $closingTag - 1) as $position) {
            $token = $tokens[$position];
            if (Token::NAME_TYPE === $token->getType()) {
                switch ($token->getValue()) {
                    case 'with':
                        $fixer->replaceToken($position, ',');
                        break;
                    case 'only':
                        $withoutContext = true;
                        $fixer->replaceToken($position, '');
                        break;
                    case 'ignore':
                        $ignoreMissing = true;
                        $fixer->replaceToken($position, '');
                        break;
                    case 'missing':
                        $fixer->replaceToken($position, '');
                        break;
                    default:
                        break;
                }
            }
        }
        $endInclude = ') '.str_replace('%}', '}}', $tokens[$closingTag]->getValue());
        if ($ignoreMissing) {
            $endInclude = ', ignore_missing = true'.$endInclude;
        }
        if ($withoutContext) {
            $endInclude = ', with_context = false'.$endInclude;
        }
        $fixer->replaceToken($closingTag, $endInclude);
        $fixer->endChangeSet();
    }
}
