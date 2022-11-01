<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Token\Token;

class TokenTest extends TestCase
{
    public function testGetters(): void
    {
        $relatedToken = new Token(Token::PUNCTUATION_TYPE, 1, 1, 'file.twig', '[');
        $token = new Token(Token::PUNCTUATION_TYPE, 1, 2, 'file.twig', ']', $relatedToken);

        static::assertSame(Token::PUNCTUATION_TYPE, $token->getType());
        static::assertSame(1, $token->getLine());
        static::assertSame(2, $token->getPosition());
        static::assertSame('file.twig', $token->getFilename());
        static::assertSame(']', $token->getValue());
        static::assertSame($relatedToken, $token->getRelatedToken());
    }

    public function testNullValue(): void
    {
        $token = new Token(Token::EOF_TYPE, 2, 1, 'file.twig');
        static::assertSame('', $token->getValue());
    }
}
