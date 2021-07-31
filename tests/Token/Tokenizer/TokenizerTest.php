<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token\Tokenizer;

use PHPUnit\Framework\TestCase;
use Twig\Source;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Tests\TestHelper;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;

/**
 * Class TokenizerTest
 */
class TokenizerTest extends TestCase
{
    /**
     * @param string          $filePath
     * @param array<int, int> $expectedTokenTypes
     *
     * @return void
     *
     * @throws \Exception
     *
     * @dataProvider tokenizeDataProvider
     */
    public function testTokenize(string $filePath, array $expectedTokenTypes): void
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            self::fail(sprintf('Cannot read file path %s', $filePath));
        }

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $source = new Source($content, $filePath);

        $tokens = $tokenizer->tokenize($source);

        $tokenValues = array_map(static function (Token $token): ?string {
            return $token->getValue();
        }, $tokens);

        $diff = TestHelper::generateDiff(implode($tokenValues), $filePath);
        if ('' !== $diff) {
            self::fail($diff);
        }

        $tokenTypes = array_map(static function (Token $token): int {
            return $token->getType();
        }, $tokens);
        self::assertSame($expectedTokenTypes, $tokenTypes);
    }

    /**
     * @return array<array{string, array<int, int>}>
     */
    public function tokenizeDataProvider(): array
    {
        return [
            [
                __DIR__.'/TokenizerTest1.twig',
                [
                    0 => Token::TEXT_TYPE,
                    1 => Token::EOL_TYPE,
                    2 => Token::EOF_TYPE,
                ],
            ],
            [
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
            ],
            [
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
                    16 => Token::NAME_TYPE,
                    17 => Token::WHITESPACE_TYPE,
                    18 => Token::OPERATOR_TYPE,
                    19 => Token::WHITESPACE_TYPE,
                    20 => Token::STRING_TYPE,
                    21 => Token::WHITESPACE_TYPE,
                    22 => Token::OPERATOR_TYPE,
                    23 => Token::WHITESPACE_TYPE,
                    24 => Token::STRING_TYPE,
                    25 => Token::WHITESPACE_TYPE,
                    26 => Token::VAR_END_TYPE,
                    27 => Token::EOL_TYPE,
                    28 => Token::VAR_START_TYPE,
                    29 => Token::WHITESPACE_TYPE,
                    30 => Token::NAME_TYPE,
                    31 => Token::WHITESPACE_TYPE,
                    32 => Token::OPERATOR_TYPE,
                    33 => Token::WHITESPACE_TYPE,
                    34 => Token::PUNCTUATION_TYPE,
                    35 => Token::WHITESPACE_TYPE,
                    36 => Token::NAME_TYPE,
                    37 => Token::PUNCTUATION_TYPE,
                    38 => Token::PUNCTUATION_TYPE,
                    39 => Token::NUMBER_TYPE,
                    40 => Token::PUNCTUATION_TYPE,
                    41 => Token::WHITESPACE_TYPE,
                    42 => Token::NUMBER_TYPE,
                    43 => Token::PUNCTUATION_TYPE,
                    44 => Token::WHITESPACE_TYPE,
                    45 => Token::PUNCTUATION_TYPE,
                    46 => Token::WHITESPACE_TYPE,
                    47 => Token::VAR_END_TYPE,
                    48 => Token::EOL_TYPE,
                    49 => Token::BLOCK_START_TYPE,
                    50 => Token::WHITESPACE_TYPE,
                    51 => Token::BLOCK_TAG_TYPE,
                    52 => Token::WHITESPACE_TYPE,
                    53 => Token::NAME_TYPE,
                    54 => Token::OPERATOR_TYPE,
                    55 => Token::PUNCTUATION_TYPE,
                    56 => Token::NAME_TYPE,
                    57 => Token::PUNCTUATION_TYPE,
                    58 => Token::WHITESPACE_TYPE,
                    59 => Token::NAME_TYPE,
                    60 => Token::WHITESPACE_TYPE,
                    61 => Token::OPERATOR_TYPE,
                    62 => Token::WHITESPACE_TYPE,
                    63 => Token::NAME_TYPE,
                    64 => Token::WHITESPACE_TYPE,
                    65 => Token::OPERATOR_TYPE,
                    66 => Token::WHITESPACE_TYPE,
                    67 => Token::NUMBER_TYPE,
                    68 => Token::PUNCTUATION_TYPE,
                    69 => Token::WHITESPACE_TYPE,
                    70 => Token::NAME_TYPE,
                    71 => Token::PUNCTUATION_TYPE,
                    72 => Token::WHITESPACE_TYPE,
                    73 => Token::NUMBER_TYPE,
                    74 => Token::PUNCTUATION_TYPE,
                    75 => Token::WHITESPACE_TYPE,
                    76 => Token::BLOCK_END_TYPE,
                    77 => Token::EOL_TYPE,
                    78 => Token::VAR_START_TYPE,
                    79 => Token::WHITESPACE_TYPE,
                    80 => Token::NAME_TYPE,
                    81 => Token::WHITESPACE_TYPE,
                    82 => Token::OPERATOR_TYPE,
                    83 => Token::WHITESPACE_TYPE,
                    84 => Token::STRING_TYPE,
                    85 => Token::WHITESPACE_TYPE,
                    86 => Token::VAR_END_TYPE,
                    87 => Token::EOL_TYPE,
                    88 => Token::EOF_TYPE,
                ],
            ],
            [
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
            ],
            [
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
            ],
            [
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
            ],
        ];
    }
}
