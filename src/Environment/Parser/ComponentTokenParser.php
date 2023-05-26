<?php

declare(strict_types=1);

namespace TwigCsFixer\Environment\Parser;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * @see https://github.com/symfony/ux-twig-component/blob/2.x/src/Twig/ComponentTokenParser.php
 */
final class ComponentTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        $this->parser->getExpressionParser()->parseExpression();
        $this->parseArguments();

        $fakeParentToken = new Token(Token::STRING_TYPE, '__parent__', $token->getLine());

        // inject a fake parent to make the parent() function work
        $stream->injectTokens([
            new Token(Token::BLOCK_START_TYPE, '', $token->getLine()),
            new Token(Token::NAME_TYPE, 'extends', $token->getLine()),
            $fakeParentToken,
            new Token(Token::BLOCK_END_TYPE, '', $token->getLine()),
        ]);

        $this->parser->parse($stream, fn (Token $token) => $token->test("end{$this->getTag()}"), true);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new Node();
    }

    public function getTag(): string
    {
        return 'component';
    }

    private function parseArguments(): void
    {
        $stream = $this->parser->getStream();

        if (null !== $stream->nextIf(Token::NAME_TYPE, 'with')) {
            $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->nextIf(Token::NAME_TYPE, 'only');

        $stream->expect(Token::BLOCK_END_TYPE);
    }
}
