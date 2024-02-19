<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token\Tokenizer;

use PHPUnit\Framework\TestCase;
use Twig\Source;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Tests\TestHelper;
use TwigCsFixer\Tests\Token\Tokenizer\Fixtures\CustomTwigExtension;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;

final class TokenizerTest extends TestCase
{
    public function testTokenize(): void
    {
        $filePath = __DIR__.'/Fixtures/test1.twig';
        $content = file_get_contents($filePath);
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $source = new Source($content, $filePath);

        static::assertEquals(
            [
                [
                    new Token(Token::TEXT_TYPE, 1, 1, $filePath, '<div>test</div>'),
                    new Token(Token::EOL_TYPE, 1, 16, $filePath, \PHP_EOL),
                    new Token(Token::EOF_TYPE, 2, 1, $filePath),
                ],
                [],
            ],
            $tokenizer->tokenize($source)
        );
    }

    public function testTokenizeWithCustomOperators(): void
    {
        $filePath = __DIR__.'/Fixtures/custom_operators.twig';
        $content = file_get_contents($filePath);
        static::assertNotFalse($content);

        $env = new StubbedEnvironment([new CustomTwigExtension()]);
        $tokenizer = new Tokenizer($env);
        $source = new Source($content, $filePath);

        static::assertEquals(
            [
                new Token(Token::VAR_START_TYPE, 1, 1, $filePath, '{{'),
                new Token(Token::WHITESPACE_TYPE, 1, 3, $filePath, ' '),
                new Token(Token::OPERATOR_TYPE, 1, 4, $filePath, 'n0t'),
                new Token(Token::WHITESPACE_TYPE, 1, 7, $filePath, ' '),
                new Token(Token::NAME_TYPE, 1, 8, $filePath, 'foo'),
                new Token(Token::PUNCTUATION_TYPE, 1, 11, $filePath, '.'),
                new Token(Token::NAME_TYPE, 1, 12, $filePath, 'n0t'),
                new Token(Token::WHITESPACE_TYPE, 1, 15, $filePath, ' '),
                new Token(Token::OPERATOR_TYPE, 1, 16, $filePath, '+sum'),
                new Token(Token::WHITESPACE_TYPE, 1, 20, $filePath, ' '),
                new Token(Token::OPERATOR_TYPE, 1, 21, $filePath, '+'),
                new Token(Token::NAME_TYPE, 1, 22, $filePath, 'sumVariable'),
                new Token(Token::WHITESPACE_TYPE, 1, 33, $filePath, ' '),
                new Token(Token::VAR_END_TYPE, 1, 34, $filePath, '}}'),
                new Token(Token::EOL_TYPE, 1, 36, $filePath, \PHP_EOL),
                new Token(Token::EOF_TYPE, 2, 1, $filePath),
            ],
            $tokenizer->tokenize($source)[0]
        );
    }

    public function testTokenizeIgnoredViolations(): void
    {
        $filePath = __DIR__.'/Fixtures/ignored_violations.twig';
        $content = file_get_contents($filePath);
        static::assertNotFalse($content);

        $env = new StubbedEnvironment([new CustomTwigExtension()]);
        $tokenizer = new Tokenizer($env);
        $source = new Source($content, $filePath);

        static::assertEquals(
            [
                'Foo.Bar',
                'Foo.BarInsensitive',
                'Foo.Bar:3',
                'Foo.Bar:5',
                'Bar.Foo:5',
                ':6',
            ],
            array_map(
                static fn (ViolationId $validationId) => $validationId->toString(),
                $tokenizer->tokenize($source)[1]
            )
        );
    }

    /**
     * @param array<int, int|string> $expectedTokenTypes
     *
     * @dataProvider tokenizeDataProvider
     */
    public function testTokenizeTypes(string $filePath, array $expectedTokenTypes): void
    {
        $content = file_get_contents($filePath);
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $source = new Source($content, $filePath);

        $tokens = $tokenizer->tokenize($source)[0];

        $tokenValues = array_map(static fn (Token $token): string => $token->getValue(), $tokens);

        $diff = TestHelper::generateDiff(implode('', $tokenValues), $filePath);
        if ('' !== $diff) {
            static::fail($diff);
        }

        $tokenTypes = array_map(static fn (Token $token): int|string => $token->getType(), $tokens);
        static::assertSame($expectedTokenTypes, $tokenTypes);
    }

    /**
     * @return iterable<array-key, array{string, array<int, int|string>}>
     */
    public static function tokenizeDataProvider(): iterable
    {
        yield [
            __DIR__.'/Fixtures/test1.twig',
            [
                0 => Token::TEXT_TYPE,
                1 => Token::EOL_TYPE,
                2 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test2.twig',
            [
                0 => Token::VAR_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::NAME_TYPE,
                3 => Token::WHITESPACE_TYPE,
                4 => Token::VAR_END_TYPE,
                5 => Token::EOL_TYPE,
                6 => Token::COMMENT_START_TYPE,
                7 => Token::COMMENT_WHITESPACE_TYPE,
                8 => Token::COMMENT_TEXT_TYPE,
                9 => Token::COMMENT_WHITESPACE_TYPE,
                10 => Token::COMMENT_END_TYPE,
                11 => Token::EOL_TYPE,
                12 => Token::BLOCK_START_TYPE,
                13 => Token::WHITESPACE_TYPE,
                14 => Token::BLOCK_NAME_TYPE,
                15 => Token::WHITESPACE_TYPE,
                16 => Token::NAME_TYPE,
                17 => Token::WHITESPACE_TYPE,
                18 => Token::BLOCK_END_TYPE,
                19 => Token::BLOCK_START_TYPE,
                20 => Token::WHITESPACE_TYPE,
                21 => Token::BLOCK_NAME_TYPE,
                22 => Token::WHITESPACE_TYPE,
                23 => Token::BLOCK_END_TYPE,
                24 => Token::EOL_TYPE,
                25 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test3.twig',
            [
                0 => Token::VAR_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::NUMBER_TYPE,
                3 => Token::OPERATOR_TYPE,
                4 => Token::NUMBER_TYPE,
                5 => Token::OPERATOR_TYPE,
                6 => Token::NUMBER_TYPE,
                7 => Token::OPERATOR_TYPE,
                8 => Token::NUMBER_TYPE,
                9 => Token::OPERATOR_TYPE,
                10 => Token::NUMBER_TYPE,
                11 => Token::WHITESPACE_TYPE,
                12 => Token::VAR_END_TYPE,
                13 => Token::EOL_TYPE,
                14 => Token::VAR_START_TYPE,
                15 => Token::WHITESPACE_TYPE,
                16 => Token::PUNCTUATION_TYPE,
                17 => Token::NAME_TYPE,
                18 => Token::WHITESPACE_TYPE,
                19 => Token::OPERATOR_TYPE,
                20 => Token::WHITESPACE_TYPE,
                21 => Token::STRING_TYPE,
                22 => Token::WHITESPACE_TYPE,
                23 => Token::OPERATOR_TYPE,
                24 => Token::WHITESPACE_TYPE,
                25 => Token::STRING_TYPE,
                26 => Token::PUNCTUATION_TYPE,
                27 => Token::WHITESPACE_TYPE,
                28 => Token::VAR_END_TYPE,
                29 => Token::EOL_TYPE,
                30 => Token::VAR_START_TYPE,
                31 => Token::WHITESPACE_TYPE,
                32 => Token::PUNCTUATION_TYPE,
                33 => Token::NAME_TYPE,
                34 => Token::WHITESPACE_TYPE,
                35 => Token::OPERATOR_TYPE,
                36 => Token::WHITESPACE_TYPE,
                37 => Token::PUNCTUATION_TYPE,
                38 => Token::WHITESPACE_TYPE,
                39 => Token::NAME_TYPE,
                40 => Token::PUNCTUATION_TYPE,
                41 => Token::PUNCTUATION_TYPE,
                42 => Token::NUMBER_TYPE,
                43 => Token::PUNCTUATION_TYPE,
                44 => Token::WHITESPACE_TYPE,
                45 => Token::NUMBER_TYPE,
                46 => Token::PUNCTUATION_TYPE,
                47 => Token::WHITESPACE_TYPE,
                48 => Token::PUNCTUATION_TYPE,
                49 => Token::PUNCTUATION_TYPE,
                50 => Token::WHITESPACE_TYPE,
                51 => Token::VAR_END_TYPE,
                52 => Token::EOL_TYPE,
                53 => Token::BLOCK_START_TYPE,
                54 => Token::WHITESPACE_TYPE,
                55 => Token::BLOCK_NAME_TYPE,
                56 => Token::WHITESPACE_TYPE,
                57 => Token::NAME_TYPE,
                58 => Token::OPERATOR_TYPE,
                59 => Token::PUNCTUATION_TYPE,
                60 => Token::NAME_TYPE,
                61 => Token::PUNCTUATION_TYPE,
                62 => Token::WHITESPACE_TYPE,
                63 => Token::NAME_TYPE,
                64 => Token::WHITESPACE_TYPE,
                65 => Token::OPERATOR_TYPE,
                66 => Token::WHITESPACE_TYPE,
                67 => Token::NAME_TYPE,
                68 => Token::WHITESPACE_TYPE,
                69 => Token::OPERATOR_TYPE,
                70 => Token::WHITESPACE_TYPE,
                71 => Token::NUMBER_TYPE,
                72 => Token::PUNCTUATION_TYPE,
                73 => Token::WHITESPACE_TYPE,
                74 => Token::NAME_TYPE,
                75 => Token::PUNCTUATION_TYPE,
                76 => Token::WHITESPACE_TYPE,
                77 => Token::OPERATOR_TYPE,
                78 => Token::NUMBER_TYPE,
                79 => Token::PUNCTUATION_TYPE,
                80 => Token::WHITESPACE_TYPE,
                81 => Token::BLOCK_END_TYPE,
                82 => Token::EOL_TYPE,
                83 => Token::VAR_START_TYPE,
                84 => Token::WHITESPACE_TYPE,
                85 => Token::NAME_TYPE,
                86 => Token::WHITESPACE_TYPE,
                87 => Token::OPERATOR_TYPE,
                88 => Token::WHITESPACE_TYPE,
                89 => Token::STRING_TYPE,
                90 => Token::WHITESPACE_TYPE,
                91 => Token::VAR_END_TYPE,
                92 => Token::EOL_TYPE,
                93 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test4.twig',
            [
                0 => Token::VAR_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::NAME_TYPE,
                3 => Token::PUNCTUATION_TYPE,
                4 => Token::NAME_TYPE,
                5 => Token::PUNCTUATION_TYPE,
                6 => Token::NAME_TYPE,
                7 => Token::WHITESPACE_TYPE,
                8 => Token::ARROW_TYPE,
                9 => Token::WHITESPACE_TYPE,
                10 => Token::DQ_STRING_START_TYPE,
                11 => Token::INTERPOLATION_START_TYPE,
                12 => Token::NAME_TYPE,
                13 => Token::PUNCTUATION_TYPE,
                14 => Token::NAME_TYPE,
                15 => Token::INTERPOLATION_END_TYPE,
                16 => Token::STRING_TYPE,
                17 => Token::INTERPOLATION_START_TYPE,
                18 => Token::NAME_TYPE,
                19 => Token::PUNCTUATION_TYPE,
                20 => Token::NAME_TYPE,
                21 => Token::INTERPOLATION_END_TYPE,
                22 => Token::DQ_STRING_END_TYPE,
                23 => Token::PUNCTUATION_TYPE,
                24 => Token::PUNCTUATION_TYPE,
                25 => Token::NAME_TYPE,
                26 => Token::PUNCTUATION_TYPE,
                27 => Token::STRING_TYPE,
                28 => Token::PUNCTUATION_TYPE,
                29 => Token::WHITESPACE_TYPE,
                30 => Token::VAR_END_TYPE,
                31 => Token::EOL_TYPE,
                32 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test5.twig',
            [
                0 => Token::BLOCK_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::BLOCK_NAME_TYPE,
                3 => Token::WHITESPACE_TYPE,
                4 => Token::NAME_TYPE,
                5 => Token::WHITESPACE_TYPE,
                6 => Token::OPERATOR_TYPE,
                7 => Token::WHITESPACE_TYPE,
                8 => Token::NAME_TYPE,
                9 => Token::WHITESPACE_TYPE,
                10 => Token::NAME_TYPE,
                11 => Token::PUNCTUATION_TYPE,
                12 => Token::NAME_TYPE,
                13 => Token::PUNCTUATION_TYPE,
                14 => Token::WHITESPACE_TYPE,
                15 => Token::BLOCK_END_TYPE,
                16 => Token::BLOCK_START_TYPE,
                17 => Token::WHITESPACE_TYPE,
                18 => Token::BLOCK_NAME_TYPE,
                19 => Token::WHITESPACE_TYPE,
                20 => Token::BLOCK_END_TYPE,
                21 => Token::EOL_TYPE,
                22 => Token::BLOCK_START_TYPE,
                23 => Token::WHITESPACE_TYPE,
                24 => Token::BLOCK_NAME_TYPE,
                25 => Token::WHITESPACE_TYPE,
                26 => Token::NAME_TYPE,
                27 => Token::WHITESPACE_TYPE,
                28 => Token::OPERATOR_TYPE,
                29 => Token::WHITESPACE_TYPE,
                30 => Token::NAME_TYPE,
                31 => Token::WHITESPACE_TYPE,
                32 => Token::NAME_TYPE,
                33 => Token::PUNCTUATION_TYPE,
                34 => Token::NAME_TYPE,
                35 => Token::PUNCTUATION_TYPE,
                36 => Token::WHITESPACE_TYPE,
                37 => Token::BLOCK_END_TYPE,
                38 => Token::BLOCK_START_TYPE,
                39 => Token::WHITESPACE_TYPE,
                40 => Token::BLOCK_NAME_TYPE,
                41 => Token::WHITESPACE_TYPE,
                42 => Token::BLOCK_END_TYPE,
                43 => Token::EOL_TYPE,
                44 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test6.twig',
            [
                0 => Token::BLOCK_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::BLOCK_NAME_TYPE,
                3 => Token::WHITESPACE_TYPE,
                4 => Token::NAME_TYPE,
                5 => Token::WHITESPACE_TYPE,
                6 => Token::BLOCK_END_TYPE,
                7 => Token::TEXT_TYPE,
                8 => Token::BLOCK_START_TYPE,
                9 => Token::WHITESPACE_TYPE,
                10 => Token::BLOCK_NAME_TYPE,
                11 => Token::WHITESPACE_TYPE,
                12 => Token::BLOCK_END_TYPE,
                13 => Token::EOL_TYPE,
                14 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test7.twig',
            [
                0 => Token::COMMENT_START_TYPE,
                1 => Token::COMMENT_EOL_TYPE,
                2 => Token::COMMENT_WHITESPACE_TYPE,
                3 => Token::COMMENT_TEXT_TYPE,
                4 => Token::COMMENT_WHITESPACE_TYPE,
                5 => Token::COMMENT_TEXT_TYPE,
                6 => Token::COMMENT_WHITESPACE_TYPE,
                7 => Token::COMMENT_TEXT_TYPE,
                8 => Token::COMMENT_WHITESPACE_TYPE,
                9 => Token::COMMENT_TEXT_TYPE,
                10 => Token::COMMENT_WHITESPACE_TYPE,
                11 => Token::COMMENT_TEXT_TYPE,
                12 => Token::COMMENT_WHITESPACE_TYPE,
                13 => Token::COMMENT_EOL_TYPE,
                14 => Token::COMMENT_END_TYPE,
                15 => Token::EOL_TYPE,
                16 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test8.twig',
            [
                0 => Token::TAB_TYPE,
                1 => Token::COMMENT_START_TYPE,
                2 => Token::COMMENT_TAB_TYPE,
                3 => Token::COMMENT_TEXT_TYPE,
                4 => Token::COMMENT_EOL_TYPE,
                5 => Token::COMMENT_TAB_TYPE,
                6 => Token::COMMENT_END_TYPE,
                7 => Token::EOL_TYPE,
                8 => Token::EOL_TYPE,
                9 => Token::VAR_START_TYPE,
                10 => Token::WHITESPACE_TYPE,
                11 => Token::NUMBER_TYPE,
                12 => Token::TAB_TYPE,
                13 => Token::OPERATOR_TYPE,
                14 => Token::TAB_TYPE,
                15 => Token::NUMBER_TYPE,
                16 => Token::WHITESPACE_TYPE,
                17 => Token::VAR_END_TYPE,
                18 => Token::EOL_TYPE,
                19 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test9.twig',
            [
                0 => Token::COMMENT_START_TYPE,
                1 => Token::COMMENT_WHITESPACE_TYPE,
                2 => Token::COMMENT_END_TYPE,
                3 => Token::EOL_TYPE,
                4 => Token::COMMENT_START_TYPE,
                5 => Token::COMMENT_WHITESPACE_TYPE,
                6 => Token::COMMENT_TEXT_TYPE,
                7 => Token::COMMENT_WHITESPACE_TYPE,
                8 => Token::COMMENT_TEXT_TYPE,
                9 => Token::COMMENT_WHITESPACE_TYPE,
                10 => Token::COMMENT_TEXT_TYPE,
                11 => Token::COMMENT_WHITESPACE_TYPE,
                12 => Token::COMMENT_END_TYPE,
                13 => Token::EOL_TYPE,
                14 => Token::COMMENT_START_TYPE,
                15 => Token::COMMENT_WHITESPACE_TYPE,
                16 => Token::COMMENT_END_TYPE,
                17 => Token::EOL_TYPE,
                18 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test10.twig',
            [
                0 => Token::VAR_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::NAME_TYPE,
                3 => Token::WHITESPACE_TYPE,
                4 => Token::OPERATOR_TYPE,
                5 => Token::WHITESPACE_TYPE,
                6 => Token::NAME_TYPE,
                7 => Token::WHITESPACE_TYPE,
                8 => Token::OPERATOR_TYPE,
                9 => Token::WHITESPACE_TYPE,
                10 => Token::NAME_TYPE,
                11 => Token::PUNCTUATION_TYPE,
                12 => Token::NAME_TYPE,
                13 => Token::WHITESPACE_TYPE,
                14 => Token::VAR_END_TYPE,
                15 => Token::EOL_TYPE,
                16 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test11.twig',
            [
                0 => Token::VAR_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::NAME_TYPE,
                3 => Token::WHITESPACE_TYPE,
                4 => Token::NAME_TYPE,
                5 => Token::WHITESPACE_TYPE,
                6 => Token::VAR_END_TYPE,
                7 => Token::EOL_TYPE,
                8 => Token::VAR_START_TYPE,
                9 => Token::WHITESPACE_TYPE,
                10 => Token::NAME_TYPE,
                11 => Token::PUNCTUATION_TYPE,
                12 => Token::NAME_TYPE,
                13 => Token::WHITESPACE_TYPE,
                14 => Token::VAR_END_TYPE,
                15 => Token::EOL_TYPE,
                16 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test12.twig',
            [
                0 => Token::BLOCK_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::BLOCK_NAME_TYPE,
                3 => Token::WHITESPACE_TYPE,
                4 => Token::BLOCK_END_TYPE,
                5 => Token::EOL_TYPE,
                6 => Token::WHITESPACE_TYPE,
                7 => Token::TEXT_TYPE,
                8 => Token::EOL_TYPE,
                9 => Token::WHITESPACE_TYPE,
                10 => Token::TEXT_TYPE,
                11 => Token::WHITESPACE_TYPE,
                12 => Token::TEXT_TYPE,
                13 => Token::WHITESPACE_TYPE,
                14 => Token::TEXT_TYPE,
                15 => Token::WHITESPACE_TYPE,
                16 => Token::TEXT_TYPE,
                17 => Token::WHITESPACE_TYPE,
                18 => Token::TEXT_TYPE,
                19 => Token::WHITESPACE_TYPE,
                20 => Token::TEXT_TYPE,
                21 => Token::EOL_TYPE,
                22 => Token::WHITESPACE_TYPE,
                23 => Token::TEXT_TYPE,
                24 => Token::TEXT_TYPE,
                25 => Token::WHITESPACE_TYPE,
                26 => Token::TEXT_TYPE,
                27 => Token::WHITESPACE_TYPE,
                28 => Token::TEXT_TYPE,
                29 => Token::EOL_TYPE,
                30 => Token::WHITESPACE_TYPE,
                31 => Token::TEXT_TYPE,
                32 => Token::WHITESPACE_TYPE,
                33 => Token::TEXT_TYPE,
                34 => Token::WHITESPACE_TYPE,
                35 => Token::TEXT_TYPE,
                36 => Token::EOL_TYPE,
                37 => Token::WHITESPACE_TYPE,
                38 => Token::TEXT_TYPE,
                39 => Token::EOL_TYPE,
                40 => Token::BLOCK_START_TYPE,
                41 => Token::WHITESPACE_TYPE,
                42 => Token::BLOCK_NAME_TYPE,
                43 => Token::WHITESPACE_TYPE,
                44 => Token::BLOCK_END_TYPE,
                45 => Token::EOL_TYPE,
                46 => Token::BLOCK_START_TYPE,
                47 => Token::WHITESPACE_TYPE,
                48 => Token::BLOCK_NAME_TYPE,
                49 => Token::WHITESPACE_TYPE,
                50 => Token::NAME_TYPE,
                51 => Token::WHITESPACE_TYPE,
                52 => Token::OPERATOR_TYPE,
                53 => Token::WHITESPACE_TYPE,
                54 => Token::NAME_TYPE,
                55 => Token::WHITESPACE_TYPE,
                56 => Token::BLOCK_END_TYPE,
                57 => Token::EOL_TYPE,
                58 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/Fixtures/test13.twig',
            [
                0 => Token::VAR_START_TYPE,
                1 => Token::WHITESPACE_TYPE,
                2 => Token::PUNCTUATION_TYPE,
                3 => Token::WHITESPACE_TYPE,
                4 => Token::NAME_TYPE,
                5 => Token::PUNCTUATION_TYPE,
                6 => Token::WHITESPACE_TYPE,
                7 => Token::STRING_TYPE,
                8 => Token::PUNCTUATION_TYPE,
                9 => Token::WHITESPACE_TYPE,
                10 => Token::SPREAD_TYPE,
                11 => Token::PUNCTUATION_TYPE,
                12 => Token::WHITESPACE_TYPE,
                13 => Token::NAME_TYPE,
                14 => Token::PUNCTUATION_TYPE,
                15 => Token::WHITESPACE_TYPE,
                16 => Token::STRING_TYPE,
                17 => Token::WHITESPACE_TYPE,
                18 => Token::PUNCTUATION_TYPE,
                19 => Token::WHITESPACE_TYPE,
                20 => Token::PUNCTUATION_TYPE,
                21 => Token::WHITESPACE_TYPE,
                22 => Token::VAR_END_TYPE,
                23 => Token::EOL_TYPE,
                24 => Token::VAR_START_TYPE,
                25 => Token::WHITESPACE_TYPE,
                26 => Token::PUNCTUATION_TYPE,
                27 => Token::NUMBER_TYPE,
                28 => Token::PUNCTUATION_TYPE,
                29 => Token::WHITESPACE_TYPE,
                30 => Token::NUMBER_TYPE,
                31 => Token::PUNCTUATION_TYPE,
                32 => Token::WHITESPACE_TYPE,
                33 => Token::SPREAD_TYPE,
                34 => Token::PUNCTUATION_TYPE,
                35 => Token::NUMBER_TYPE,
                36 => Token::PUNCTUATION_TYPE,
                37 => Token::WHITESPACE_TYPE,
                38 => Token::NUMBER_TYPE,
                39 => Token::PUNCTUATION_TYPE,
                40 => Token::PUNCTUATION_TYPE,
                41 => Token::WHITESPACE_TYPE,
                42 => Token::VAR_END_TYPE,
                43 => Token::EOL_TYPE,
                44 => Token::EOF_TYPE,
            ],
        ];
    }

    /**
     * @dataProvider tokenizeInvalidDataProvider
     */
    public function testTokenizeInvalid(string $filePath, string $expectedMessage): void
    {
        $content = file_get_contents($filePath);
        static::assertNotFalse($content);

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $source = new Source($content, $filePath);

        $this->expectException(CannotTokenizeException::class);
        $this->expectExceptionMessage($expectedMessage);
        $tokenizer->tokenize($source);
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function tokenizeInvalidDataProvider(): iterable
    {
        yield [__DIR__.'/Fixtures/invalid1.twig', 'The template is invalid.'];
        yield [__DIR__.'/Fixtures/invalid2.twig', 'Unexpected character "&" at line 4.'];
        yield [__DIR__.'/Fixtures/invalid3.twig', 'Unclosed "(" at line 1.'];
        yield [__DIR__.'/Fixtures/invalid4.twig', 'Unexpected character ")" at line 1.'];
        yield [__DIR__.'/Fixtures/invalid5.twig', 'Unexpected character "#" at line 1.'];
        yield [__DIR__.'/Fixtures/invalid6.twig', 'Unclosed comment at line 1.'];
        yield [__DIR__.'/Fixtures/invalid7.twig', 'Unexpected character ":" at line 1.'];
    }
}
