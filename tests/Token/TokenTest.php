<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Token\Token;

final class TokenTest extends TestCase
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

    /**
     * @dataProvider tokenNameDataProvider
     */
    public function testTokenName(int|string $type, string $expectedName): void
    {
        $token = new Token($type, 1, 1, 'file.twig');
        static::assertSame($expectedName, $token->getName());
    }

    /**
     * @return iterable<array-key, array{int|string, string}>
     */
    public static function tokenNameDataProvider(): iterable
    {
        yield [Token::VAR_START_TYPE, 'VarStart'];
        yield [Token::WHITESPACE_TYPE, 'Whitespace'];
        yield ['Foo', 'Foo'];
        yield [42, '42'];
    }
}
