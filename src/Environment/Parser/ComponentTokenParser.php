<?php

declare(strict_types=1);

namespace TwigCsFixer\Environment\Parser;

use Twig\Node\Expression\AbstractExpression;
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
        $parent = $this->parser->getExpressionParser()->parseExpression();
        \assert($parent instanceof AbstractExpression);
        $this->parseArguments();

        $parentToken = new Token(Token::STRING_TYPE, '__component__', $token->getLine());
        $fakeParentToken = new Token(Token::STRING_TYPE, '__parent__', $token->getLine());

        // inject a fake parent to make the parent() function work
        $stream->injectTokens([
            new Token(Token::BLOCK_START_TYPE, '', $token->getLine()),
            new Token(Token::NAME_TYPE, 'extends', $token->getLine()),
            $parentToken,
            new Token(Token::BLOCK_END_TYPE, '', $token->getLine()),
        ]);

        $module = $this->parser->parse($stream, fn (Token $token) => $token->test("end{$this->getTag()}"), true);

        // override the parent with the correct one
        if ($fakeParentToken === $parentToken) {
            $module->setNode('parent', $parent);
        }

        $this->parser->embedTemplate($module);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new Node();
    }

    public function getTag(): string
    {
        return 'component';
    }

    /**
     * @return array{AbstractExpression|null, bool}
     */
    private function parseArguments(): array
    {
        $stream = $this->parser->getStream();

        if (null !== $stream->nextIf(Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
            \assert($variables instanceof AbstractExpression);
        } else {
            $variables = null;
        }

        $only = null !== $stream->nextIf(Token::NAME_TYPE, 'only');

        $stream->expect(Token::BLOCK_END_TYPE);

        return [$variables, $only];
    }
}
