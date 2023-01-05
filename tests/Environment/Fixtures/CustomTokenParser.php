<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Environment\Fixtures;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class CustomTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        if (!$this->parser->getStream()->test(Token::BLOCK_END_TYPE)) {
            $this->parser->getExpressionParser()->parseMultitargetExpression();
        }
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new Node([], [], $token->getLine(), $this->getTag());
    }

    public function getTag(): string
    {
        return 'custom';
    }
}
