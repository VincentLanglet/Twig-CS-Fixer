<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

final class Tokens
{
    /**
     * @var list<Token>
     */
    private array $tokens = [];

    /**
     * @var int<0, max>
     */
    private int $tokenCount = 0;

    /**
     * @var array<int, int>
     */
    private array $indexes = [];

    /**
     * @param array<Token> $tokens
     */
    public function __construct(array $tokens = [])
    {
        foreach ($tokens as $token) {
            $this->add($token);
        }
    }

    public function add(Token $token): self
    {
        $this->tokens[] = $token;
        $this->indexes[spl_object_id($token)] = $this->tokenCount;
        ++$this->tokenCount;

        return $this;
    }

    public function get(int $index): Token
    {
        if (!$this->has($index)) {
            throw new \OutOfRangeException(\sprintf('There is no token for the index "%s".', $index));
        }

        return $this->tokens[$index];
    }

    public function getIndex(Token $token): int
    {
        $id = spl_object_id($token);
        if (!isset($this->indexes[$id])) {
            throw new \InvalidArgumentException('This token is not in the collection.');
        }

        return $this->indexes[$id];
    }

    public function has(int $index): bool
    {
        return isset($this->tokens[$index]);
    }

    /**
     * @return list<Token>
     */
    public function toArray(): array
    {
        return $this->tokens;
    }

    /**
     * @param int|string|array<int|string> $type
     */
    public function findNext(int|string|array $type, int $start, ?int $end = null, bool $exclude = false): int|false
    {
        $end ??= $this->tokenCount;
        for ($i = $start; $i < $end; ++$i) {
            if ($exclude !== $this->get($i)->isMatching($type)) {
                return $i;
            }
        }

        return false;
    }

    /**
     * @param int|string|array<int|string> $type
     */
    public function findPrevious(int|string|array $type, int $start, int $end = 0, bool $exclude = false): int|false
    {
        for ($i = $start; $i >= $end; --$i) {
            if ($exclude !== $this->get($i)->isMatching($type)) {
                return $i;
            }
        }

        return false;
    }
}
