<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

final class TokensTest extends TestCase
{
    public function testTokens(): void
    {
        $token1 = new Token(Token::TEXT_TYPE, 1, 1, 'file.twig');
        $token2 = new Token(Token::TEXT_TYPE, 1, 1, 'file.twig');

        $tokens = new Tokens([$token1, $token2]);
        static::assertTrue($tokens->has(0));
        static::assertTrue($tokens->has(1));
        static::assertFalse($tokens->has(2));
        static::assertSame($token1, $tokens->get(0));
        static::assertSame($token2, $tokens->get(1));
        static::assertSame(0, $tokens->getIndex($token1));
        static::assertSame(1, $tokens->getIndex($token2));

        static::assertFalse($tokens->isReadOnly());
        $tokens->setReadOnly();
        static::assertTrue($tokens->isReadOnly());
    }

    public function testAddToken(): void
    {
        $tokens = new Tokens();
        $tokens->add(new Token(Token::TEXT_TYPE, 1, 1, 'file.twig'));
        $tokens->setReadOnly();

        $this->expectException(\LogicException::class);

        $tokens->add(new Token(Token::TEXT_TYPE, 1, 1, 'file.twig'));
    }

    public function testAddIgnoredViolation(): void
    {
        $tokens = new Tokens();
        $tokens->addIgnoredViolation(new ViolationId());
        $tokens->setReadOnly();

        $this->expectException(\LogicException::class);

        $tokens->addIgnoredViolation(new ViolationId());
    }

    public function testInvalidGet(): void
    {
        $this->expectException(\OutOfRangeException::class);

        (new Tokens())->get(0);
    }

    public function testInvalidGetIndex(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new Tokens())->getIndex(new Token(Token::TEXT_TYPE, 1, 1, 'file.twig'));
    }
}
