<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Function;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures that include function is used instead of include tag.
 */
final class IncludeFunctionRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::BLOCK_NAME_TYPE, 'include')) {
            return;
        }

        $fixer = $this->addFixableError(
            'Include function must be used instead of include tag.',
            $token
        );
        if (null === $fixer) {
            return;
        }

        $openingTag = $tokens->findPrevious(Token::BLOCK_START_TYPE, $tokenIndex);
        Assert::notFalse($openingTag, 'Opening tag cannot be null.');

        $closingTag = $tokens->findNext(Token::BLOCK_END_TYPE, $tokenIndex);
        Assert::notFalse($closingTag, 'Closing tag cannot be null.');

        $fixer->beginChangeSet();

        // Replace opening tag (and keep eventual whitespace modifiers)
        $fixer->replaceToken($openingTag, str_replace('{%', '{{', $tokens->get($openingTag)->getValue()));
        $fixer->replaceToken($tokenIndex, 'include(');

        $ignoreMissing = false;
        $withoutContext = false;
        $withVariable = false;
        foreach (range($tokenIndex, $closingTag) as $index) {
            $token = $tokens->get($index);
            if (!$token->isMatching(Token::NAME_TYPE)) {
                continue;
            }
            switch ($token->getValue()) {
                case 'with':
                    $withVariable = true;
                    $fixer->replaceToken($index, ',');
                    break;
                case 'only':
                    $withoutContext = true;
                    $fixer->replaceToken($index, '');
                    break;
                case 'ignore':
                    $ignoreMissing = true;
                    $fixer->replaceToken($index, '');
                    break;
                case 'missing':
                    $fixer->replaceToken($index, '');
                    break;
            }
        }

        $endInclude = ') '.str_replace('%}', '}}', $tokens->get($closingTag)->getValue());
        if ($ignoreMissing) {
            $endInclude = ', true'.$endInclude;
        }
        if ($ignoreMissing || $withoutContext) {
            $endInclude = \sprintf(', %s', $withoutContext ? 'false' : 'true').$endInclude;

            if (!$withVariable) {
                $endInclude = ', []'.$endInclude;
            }
        }

        $fixer->replaceToken($closingTag, $endInclude);
        $fixer->endChangeSet();
    }
}
