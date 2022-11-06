<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token\Tokenizer;

use Exception;
use PHPUnit\Framework\TestCase;
use Twig\Source;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Tests\TestHelper;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;

final class TokenizerTest extends TestCase
{
    /**
     * @param array<int, int> $expectedTokenTypes
     *
     * @throws Exception
     *
     * @dataProvider tokenizeDataProvider
     */
    public function testTokenize(string $filePath, array $expectedTokenTypes): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            static::fail(sprintf('Cannot read file path %s', $filePath));
        }

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $source = new Source($content, $filePath);

        $tokens = $tokenizer->tokenize($source);

        $tokenValues = array_map(static fn (Token $token): string => $token->getValue(), $tokens);

        $diff = TestHelper::generateDiff(implode('', $tokenValues), $filePath);
        if ('' !== $diff) {
            static::fail($diff);
        }

        $tokenTypes = array_map(static fn (Token $token): int => $token->getType(), $tokens);
        static::assertSame($expectedTokenTypes, $tokenTypes);
    }

    /**
     * @return iterable<array-key, array{string, array<int, int>}>
     */
    public function tokenizeDataProvider(): iterable
    {
        yield [
            __DIR__.'/TokenizerTest1.twig',
            [
                0 => Token::TEXT_TYPE,
                1 => Token::EOL_TYPE,
                2 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/TokenizerTest2.twig',
            [
                0  => Token::VAR_START_TYPE,
                1  => Token::WHITESPACE_TYPE,
                2  => Token::NAME_TYPE,
                3  => Token::WHITESPACE_TYPE,
                4  => Token::VAR_END_TYPE,
                5  => Token::EOL_TYPE,
                6  => Token::COMMENT_START_TYPE,
                7  => Token::COMMENT_WHITESPACE_TYPE,
                8  => Token::COMMENT_TEXT_TYPE,
                9  => Token::COMMENT_WHITESPACE_TYPE,
                10 => Token::COMMENT_END_TYPE,
                11 => Token::EOL_TYPE,
                12 => Token::BLOCK_START_TYPE,
                13 => Token::WHITESPACE_TYPE,
                14 => Token::BLOCK_TAG_TYPE,
                15 => Token::WHITESPACE_TYPE,
                16 => Token::NAME_TYPE,
                17 => Token::WHITESPACE_TYPE,
                18 => Token::BLOCK_END_TYPE,
                19 => Token::BLOCK_START_TYPE,
                20 => Token::WHITESPACE_TYPE,
                21 => Token::BLOCK_TAG_TYPE,
                22 => Token::WHITESPACE_TYPE,
                23 => Token::BLOCK_END_TYPE,
                24 => Token::EOL_TYPE,
                25 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/TokenizerTest3.twig',
            [
                0  => Token::VAR_START_TYPE,
                1  => Token::WHITESPACE_TYPE,
                2  => Token::NUMBER_TYPE,
                3  => Token::OPERATOR_TYPE,
                4  => Token::NUMBER_TYPE,
                5  => Token::OPERATOR_TYPE,
                6  => Token::NUMBER_TYPE,
                7  => Token::OPERATOR_TYPE,
                8  => Token::NUMBER_TYPE,
                9  => Token::OPERATOR_TYPE,
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
                55 => Token::BLOCK_TAG_TYPE,
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
                77 => Token::NUMBER_TYPE,
                78 => Token::PUNCTUATION_TYPE,
                79 => Token::WHITESPACE_TYPE,
                80 => Token::BLOCK_END_TYPE,
                81 => Token::EOL_TYPE,
                82 => Token::VAR_START_TYPE,
                83 => Token::WHITESPACE_TYPE,
                84 => Token::NAME_TYPE,
                85 => Token::WHITESPACE_TYPE,
                86 => Token::OPERATOR_TYPE,
                87 => Token::WHITESPACE_TYPE,
                88 => Token::STRING_TYPE,
                89 => Token::WHITESPACE_TYPE,
                90 => Token::VAR_END_TYPE,
                91 => Token::EOL_TYPE,
                92 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/TokenizerTest4.twig',
            [
                0  => Token::VAR_START_TYPE,
                1  => Token::WHITESPACE_TYPE,
                2  => Token::NAME_TYPE,
                3  => Token::PUNCTUATION_TYPE,
                4  => Token::NAME_TYPE,
                5  => Token::PUNCTUATION_TYPE,
                6  => Token::NAME_TYPE,
                7  => Token::WHITESPACE_TYPE,
                8  => Token::ARROW_TYPE,
                9  => Token::WHITESPACE_TYPE,
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
            __DIR__.'/TokenizerTest5.twig',
            [
                0  => Token::BLOCK_START_TYPE,
                1  => Token::WHITESPACE_TYPE,
                2  => Token::BLOCK_TAG_TYPE,
                3  => Token::WHITESPACE_TYPE,
                4  => Token::NAME_TYPE,
                5  => Token::WHITESPACE_TYPE,
                6  => Token::OPERATOR_TYPE,
                7  => Token::WHITESPACE_TYPE,
                8  => Token::NAME_TYPE,
                9  => Token::WHITESPACE_TYPE,
                10 => Token::NAME_TYPE,
                11 => Token::PUNCTUATION_TYPE,
                12 => Token::NAME_TYPE,
                13 => Token::PUNCTUATION_TYPE,
                14 => Token::WHITESPACE_TYPE,
                15 => Token::BLOCK_END_TYPE,
                16 => Token::BLOCK_START_TYPE,
                17 => Token::WHITESPACE_TYPE,
                18 => Token::BLOCK_TAG_TYPE,
                19 => Token::WHITESPACE_TYPE,
                20 => Token::BLOCK_END_TYPE,
                21 => Token::EOL_TYPE,
                22 => Token::BLOCK_START_TYPE,
                23 => Token::WHITESPACE_TYPE,
                24 => Token::BLOCK_TAG_TYPE,
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
                40 => Token::BLOCK_TAG_TYPE,
                41 => Token::WHITESPACE_TYPE,
                42 => Token::BLOCK_END_TYPE,
                43 => Token::EOL_TYPE,
                44 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/TokenizerTest6.twig',
            [
                0  => Token::BLOCK_START_TYPE,
                1  => Token::WHITESPACE_TYPE,
                2  => Token::BLOCK_TAG_TYPE,
                3  => Token::WHITESPACE_TYPE,
                4  => Token::NAME_TYPE,
                5  => Token::WHITESPACE_TYPE,
                6  => Token::BLOCK_END_TYPE,
                7  => Token::TEXT_TYPE,
                8  => Token::BLOCK_START_TYPE,
                9  => Token::WHITESPACE_TYPE,
                10 => Token::BLOCK_TAG_TYPE,
                11 => Token::WHITESPACE_TYPE,
                12 => Token::BLOCK_END_TYPE,
                13 => Token::EOL_TYPE,
                14 => Token::EOF_TYPE,
            ],
        ];

        yield [
            __DIR__.'/TokenizerTest7.twig',
            [
                0  => Token::COMMENT_START_TYPE,
                1  => Token::COMMENT_EOL_TYPE,
                2  => Token::COMMENT_WHITESPACE_TYPE,
                3  => Token::COMMENT_TEXT_TYPE,
                4  => Token::COMMENT_WHITESPACE_TYPE,
                5  => Token::COMMENT_TEXT_TYPE,
                6  => Token::COMMENT_WHITESPACE_TYPE,
                7  => Token::COMMENT_TEXT_TYPE,
                8  => Token::COMMENT_WHITESPACE_TYPE,
                9  => Token::COMMENT_TEXT_TYPE,
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
            __DIR__.'/TokenizerTest8.twig',
            [
                0  => Token::TAB_TYPE,
                1  => Token::COMMENT_START_TYPE,
                2  => Token::COMMENT_TAB_TYPE,
                3  => Token::COMMENT_TEXT_TYPE,
                4  => Token::COMMENT_EOL_TYPE,
                5  => Token::COMMENT_TAB_TYPE,
                6  => Token::COMMENT_END_TYPE,
                7  => Token::EOL_TYPE,
                8  => Token::EOL_TYPE,
                9  => Token::VAR_START_TYPE,
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
            __DIR__.'/TokenizerTest9.twig',
            [
                0  => Token::COMMENT_START_TYPE,
                1  => Token::COMMENT_WHITESPACE_TYPE,
                2  => Token::COMMENT_END_TYPE,
                3  => Token::EOL_TYPE,
                4  => Token::COMMENT_START_TYPE,
                5  => Token::COMMENT_WHITESPACE_TYPE,
                6  => Token::COMMENT_TEXT_TYPE,
                7  => Token::COMMENT_WHITESPACE_TYPE,
                8  => Token::COMMENT_TEXT_TYPE,
                9  => Token::COMMENT_WHITESPACE_TYPE,
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
            __DIR__.'/TokenizerTest10.twig',
            [
                0  => Token::VAR_START_TYPE,
                1  => Token::WHITESPACE_TYPE,
                2  => Token::NAME_TYPE,
                3  => Token::WHITESPACE_TYPE,
                4  => Token::OPERATOR_TYPE,
                5  => Token::WHITESPACE_TYPE,
                6  => Token::NAME_TYPE,
                7  => Token::WHITESPACE_TYPE,
                8  => Token::OPERATOR_TYPE,
                9  => Token::WHITESPACE_TYPE,
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
            __DIR__.'/TokenizerTest11.twig',
            [
                0  => Token::VAR_START_TYPE,
                1  => Token::WHITESPACE_TYPE,
                2  => Token::NAME_TYPE,
                3  => Token::WHITESPACE_TYPE,
                4  => Token::NAME_TYPE,
                5  => Token::WHITESPACE_TYPE,
                6  => Token::VAR_END_TYPE,
                7  => Token::EOL_TYPE,
                8  => Token::VAR_START_TYPE,
                9  => Token::WHITESPACE_TYPE,
                10 => Token::NAME_TYPE,
                11 => Token::PUNCTUATION_TYPE,
                12 => Token::NAME_TYPE,
                13 => Token::WHITESPACE_TYPE,
                14 => Token::VAR_END_TYPE,
                15 => Token::EOL_TYPE,
                16 => Token::EOF_TYPE,
            ],
        ];
    }

    /**
     * @throws Exception
     *
     * @dataProvider tokenizeInvalidDataProvider
     */
    public function testTokenizeInvalid(string $filePath, string $expectedMessage): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            static::fail(sprintf('Cannot read file path %s', $filePath));
        }

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $source = new Source($content, $filePath);

        self::expectExceptionMessage($expectedMessage);
        $tokenizer->tokenize($source);
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public function tokenizeInvalidDataProvider(): iterable
    {
        yield [__DIR__.'/TokenizerTestInvalid1.twig', 'Error Processing Request.'];
        yield [__DIR__.'/TokenizerTestInvalid2.twig', 'Unexpected character "&" at line 4.'];
        yield [__DIR__.'/TokenizerTestInvalid3.twig', 'Unclosed "(" at line 1.'];
        yield [__DIR__.'/TokenizerTestInvalid4.twig', 'Unexpected ")" at line 1.'];
        yield [__DIR__.'/TokenizerTestInvalid5.twig', 'Unexpected character "#" at line 1.'];
        yield [__DIR__.'/TokenizerTestInvalid6.twig', 'Unclosed comment at line 1.'];
    }
}
