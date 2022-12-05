<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Sniff\AbstractSpacingSniff;
use TwigCsFixer\Token\Token;

final class SpacingSniffTest extends TestCase
{
    public function testSpacingSniff(): void
    {
        $sniff = new class () extends AbstractSpacingSniff {
            protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
            {
                $token = $tokens[$tokenPosition];
                if (0 === $tokenPosition) {
                    // Check it does not crash
                    return 1;
                }

                if (Token::EOF_TYPE === $token->getType()) {
                    return 2;
                }

                return null;
            }

            protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int
            {
                $token = $tokens[$tokenPosition];
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

        $sniff->fixFile([
            new Token(Token::TEXT_TYPE, 0, 0, 'fakeFile.html.twig'),
            new Token(Token::TEXT_TYPE, 1, 0, 'fakeFile.html.twig'),
            new Token(Token::TEXT_TYPE, 2, 0, 'fakeFile.html.twig'),
            new Token(Token::TEXT_TYPE, 3, 0, 'fakeFile.html.twig'),
            new Token(Token::TEXT_TYPE, 4, 0, 'fakeFile.html.twig'),
            new Token(Token::EOF_TYPE, 5, 0, 'fakeFile.html.twig'),
        ], $fixer);
    }
}
