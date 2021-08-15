<?php

namespace TwigCsFixer\Token\Token;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Token\Token;

/**
 * Test for Token.
 */
class TokenTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetters(): void
    {
        $relatedToken = new Token(Token::PUNCTUATION_TYPE, 1, 1, 'file.twig', '[');
        $token = new Token(Token::PUNCTUATION_TYPE, 1, 2, 'file.twig', ']', $relatedToken);

        self::assertSame(Token::PUNCTUATION_TYPE, $token->getType());
        self::assertSame(1, $token->getLine());
        self::assertSame(2, $token->getPosition());
        self::assertSame('file.twig', $token->getFilename());
        self::assertSame(']', $token->getValue());
        self::assertSame($relatedToken, $token->getRelatedToken());
    }

    /**
     * @return void
     */
    public function testNullValue(): void
    {
        $token = new Token(Token::EOF_TYPE, 2, 1, 'file.twig');
        self::assertSame('', $token->getValue());
    }
}
