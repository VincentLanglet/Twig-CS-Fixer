<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

final class SpacingRuleTest extends TestCase
{
    public function testSpacingRule(): void
    {
        $rule = new class() extends AbstractSpacingRule {
            protected function getSpaceBefore(int $tokenPosition, Tokens $tokens): ?int
            {
                $token = $tokens->get($tokenPosition);
                if (0 === $tokenPosition) {
                    // Check it does not crash
                    return 1;
                }

                if (Token::EOF_TYPE === $token->getType()) {
                    return 2;
                }

                return null;
            }

            protected function getSpaceAfter(int $tokenPosition, Tokens $tokens): ?int
            {
                $token = $tokens->get($tokenPosition);
                if (0 === $tokenPosition) {
                    return 2;
                }

                if (Token::EOF_TYPE === $token->getType()) {
                    // Check it does not crash
                    return 1;
                }

                return null;
            }
        };

        $fixer = $this->createMock(FixerInterface::class);
        $fixer->expects(static::once())->method('addContent')->with(0, '  ');
        $fixer->expects(static::once())->method('addContentBefore')->with(5, '  ');

        $rule->fixFile(new Tokens([
            new Token(Token::TEXT_TYPE, 0, 0, 'fakeFile.html.twig'),
            new Token(Token::TEXT_TYPE, 1, 0, 'fakeFile.html.twig'),
            new Token(Token::TEXT_TYPE, 2, 0, 'fakeFile.html.twig'),
            new Token(Token::TEXT_TYPE, 3, 0, 'fakeFile.html.twig'),
            new Token(Token::TEXT_TYPE, 4, 0, 'fakeFile.html.twig'),
            new Token(Token::EOF_TYPE, 5, 0, 'fakeFile.html.twig'),
        ]), $fixer);
    }
}
