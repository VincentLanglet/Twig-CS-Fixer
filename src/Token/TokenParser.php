<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use Exception;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TokenStream;

/**
 * Token parser for any block.
 */
class TokenParser extends AbstractTokenParser
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     *
     * @return void
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    public function decideEnd(Token $token): bool
    {
        return $token->test('end'.$this->name);
    }

    /**
     * @param Token $token
     *
     * @return Node
     *
     * @throws Exception
     */
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        while ($stream->getCurrent()->getType() !== Token::BLOCK_END_TYPE) {
            $stream->next();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        if ($this->hasBody($stream)) {
            $this->parser->subparse([$this, 'decideEnd'], true);
            $stream->expect(Token::BLOCK_END_TYPE);
        }

        $attributes = [];
        if ($token->getValue()) {
            $attributes['name'] = $token->getValue();
        }

        return new Node([], $attributes, $token->getLine(), $token->getValue() ?: null);
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->name;
    }

    /**
     * @param TokenStream $stream
     *
     * @return bool
     *
     * @throws Exception
     */
    private function hasBody(TokenStream $stream): bool
    {
        $look = 0;
        $token = $stream->look($look);
        while ($token) {
            if ($token->getType() === Token::EOF_TYPE) {
                return false;
            }

            if (
                $token->getType() === Token::NAME_TYPE
                && $token->getValue() === 'end'.$this->name
            ) {
                return true;
            }

            $look++;
            $token = $stream->look($look);
        }

        return false;
    }
}
