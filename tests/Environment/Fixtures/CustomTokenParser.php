<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Environment\Fixtures;

use Twig\Node\Node;
use Twig\Node\Nodes;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class CustomTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        if (!$this->parser->getStream()->test(Token::BLOCK_END_TYPE)) {
            // @phpstan-ignore-next-line function.alreadyNarrowedType
            if (method_exists($this->parser, 'parseExpression')) { // Since Twig 3.21
                $this->parser->parseExpression();
            } else {
                // @codeCoverageIgnoreStart
                // @phpstan-ignore-next-line method.deprecated, method.deprecatedClass
                $this->parser->getExpressionParser()->parseExpression();
                // @codeCoverageIgnoreEnd
            }
        }
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new Nodes([], $token->getLine());
    }

    public function getTag(): string
    {
        return 'custom';
    }
}
