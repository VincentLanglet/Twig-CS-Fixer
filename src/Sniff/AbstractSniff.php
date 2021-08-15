<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use Exception;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Token\Token;

/**
 * Base for all sniff.
 */
abstract class AbstractSniff implements SniffInterface
{
    /**
     * @var Report|null
     */
    protected $report;

    /**
     * @var Fixer|null
     */
    private $fixer;

    /**
     * @param Report $report
     *
     * @return void
     */
    public function enableReport(Report $report): void
    {
        $this->report = $report;
    }

    /**
     * @param Fixer $fixer
     *
     * @return void
     */
    public function enableFixer(Fixer $fixer): void
    {
        $this->fixer = $fixer;
    }

    /**
     * @return void
     */
    public function disable(): void
    {
        $this->report = null;
        $this->fixer = null;
    }

    /**
     * @param array<int, Token> $stream
     *
     * @return void
     */
    public function processFile(array $stream): void
    {
        foreach ($stream as $index => $token) {
            $this->process($index, $stream);
        }
    }

    /**
     * @param int               $tokenPosition
     * @param array<int, Token> $tokens
     *
     * @return void
     */
    abstract protected function process(int $tokenPosition, array $tokens): void;

    /**
     * @param Token           $token
     * @param int|int[]       $type
     * @param string|string[] $value
     *
     * @return bool
     */
    protected function isTokenMatching(Token $token, $type, $value = []): bool
    {
        if (!is_array($type)) {
            $type = [$type];
        }
        if (!is_array($value)) {
            $value = [$value];
        }

        return in_array($token->getType(), $type, true)
            && ([] === $value || in_array($token->getValue(), $value, true));
    }

    /**
     * @param int|int[]         $type
     * @param array<int, Token> $tokens
     * @param int               $start
     * @param bool              $exclude
     *
     * @return int|false
     */
    protected function findNext($type, array $tokens, int $start, bool $exclude = false)
    {
        $i = 0;

        while (isset($tokens[$start + $i]) && $exclude === $this->isTokenMatching($tokens[$start + $i], $type)) {
            $i++;
        }

        if (!isset($tokens[$start + $i])) {
            return false;
        }

        return $start + $i;
    }

    /**
     * @param int|int[]         $type
     * @param array<int, Token> $tokens
     * @param int               $start
     * @param bool              $exclude
     *
     * @return int|false
     */
    protected function findPrevious($type, array $tokens, int $start, bool $exclude = false)
    {
        $i = 0;

        while (isset($tokens[$start - $i]) && $exclude === $this->isTokenMatching($tokens[$start - $i], $type)) {
            $i++;
        }

        if (!isset($tokens[$start - $i])) {
            return false;
        }

        return $start - $i;
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addWarning(string $message, Token $token): void
    {
        $this->addMessage(SniffViolation::LEVEL_WARNING, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addError(string $message, Token $token): void
    {
        $this->addMessage(SniffViolation::LEVEL_ERROR, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @return Fixer|null
     *
     * @throws Exception
     */
    protected function addFixableWarning(string $message, Token $token): ?Fixer
    {
        return $this->addFixableMessage(SniffViolation::LEVEL_WARNING, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @return Fixer|null
     *
     * @throws Exception
     */
    protected function addFixableError(string $message, Token $token): ?Fixer
    {
        return $this->addFixableMessage(SniffViolation::LEVEL_ERROR, $message, $token);
    }

    /**
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     *
     * @return void
     *
     * @throws Exception
     */
    private function addMessage(int $messageType, string $message, Token $token): void
    {
        $report = $this->report;
        if (null === $report) {
            if (null !== $this->fixer) {
                // We are fixing the file, ignore this
                return;
            }

            throw new Exception(sprintf('Sniff "%s" is disabled.', self::class));
        }

        $sniffViolation = new SniffViolation(
            $messageType,
            $message,
            $token->getFilename(),
            $token->getLine()
        );
        $sniffViolation->setLinePosition($token->getPosition());

        $report->addMessage($sniffViolation);
    }

    /**
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     *
     * @return Fixer|null
     *
     * @throws Exception
     */
    private function addFixableMessage(int $messageType, string $message, Token $token): ?Fixer
    {
        $this->addMessage($messageType, $message, $token);

        return $this->fixer;
    }
}
