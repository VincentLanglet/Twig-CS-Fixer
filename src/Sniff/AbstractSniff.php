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
    protected $fixer;

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
     * @param array $stream
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
     * @param int     $tokenPosition
     * @param Token[] $stream
     *
     * @return void
     */
    abstract protected function process(int $tokenPosition, array $stream): void;

    /**
     * @param Token        $token
     * @param int|array    $type
     * @param string|array $value
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

        return in_array($token->getType(), $type) && ([] === $value || in_array($token->getValue(), $value));
    }

    /**
     * @param int|array $type
     * @param array     $tokens
     * @param int       $start
     * @param bool      $exclude
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
     * @param int|array $type
     * @param array     $tokens
     * @param int       $start
     * @param bool      $exclude
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
        $this->addMessage(Report::MESSAGE_TYPE_WARNING, $message, $token);
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
        $this->addMessage(Report::MESSAGE_TYPE_ERROR, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function addFixableWarning(string $message, Token $token): bool
    {
        return $this->addFixableMessage(Report::MESSAGE_TYPE_WARNING, $message, $token);
    }

    /**
     * @param string $message
     * @param Token  $token
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function addFixableError(string $message, Token $token): bool
    {
        return $this->addFixableMessage(Report::MESSAGE_TYPE_ERROR, $message, $token);
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
        if (null === $this->report) {
            if (null !== $this->fixer) {
                // We are fixing the file, ignore this
                return;
            }

            throw new Exception('Sniff is disabled!');
        }

        $sniffViolation = new SniffViolation(
            $messageType,
            $message,
            $token->getFilename(),
            $token->getLine()
        );
        $sniffViolation->setLinePosition($token->getPosition());

        $this->report->addMessage($sniffViolation);
    }

    /**
     * @param int    $messageType
     * @param string $message
     * @param Token  $token
     *
     * @return bool
     *
     * @throws Exception
     */
    private function addFixableMessage(int $messageType, string $message, Token $token): bool
    {
        $this->addMessage($messageType, $message, $token);

        return null !== $this->fixer;
    }
}
