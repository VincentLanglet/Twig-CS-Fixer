<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use ReflectionClass;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Token\Token;

abstract class AbstractRule implements RuleInterface
{
    private ?Report $report = null;

    /**
     * @var list<ViolationId>
     */
    private array $ignoredViolations = [];

    public function getName(): string
    {
        return static::class;
    }

    public function getShortName(): string
    {
        $shortName = (new ReflectionClass($this))->getShortName();

        return str_ends_with($shortName, 'Rule') ? substr($shortName, 0, -4) : $shortName;
    }

    public function lintFile(array $stream, Report $report, array $ignoredViolations = []): void
    {
        $this->init($report, $ignoredViolations);

        foreach (array_keys($stream) as $index) {
            $this->process($index, $stream);
        }
    }

    /**
     * @param list<ViolationId> $ignoredViolations
     */
    protected function init(?Report $report, array $ignoredViolations = []): void
    {
        $this->report = $report;
        $this->ignoredViolations = $ignoredViolations;
    }

    /**
     * @param array<int, Token> $tokens
     */
    abstract protected function process(int $tokenPosition, array $tokens): void;

    /**
     * @param int|string|array<int|string> $type
     * @param string|string[]              $value
     */
    protected function isTokenMatching(Token $token, int|string|array $type, string|array $value = []): bool
    {
        if (!\is_array($type)) {
            $type = [$type];
        }
        if (!\is_array($value)) {
            $value = [$value];
        }

        return \in_array($token->getType(), $type, true)
            && ([] === $value || \in_array($token->getValue(), $value, true));
    }

    /**
     * @param int|string|array<int|string> $type
     * @param array<int, Token>            $tokens
     */
    protected function findNext(int|string|array $type, array $tokens, int $start, bool $exclude = false): int|false
    {
        $i = 0;

        while (
            isset($tokens[$start + $i])
            && $exclude === $this->isTokenMatching($tokens[$start + $i], $type)
        ) {
            ++$i;
        }

        if (!isset($tokens[$start + $i])) {
            return false;
        }

        return $start + $i;
    }

    /**
     * @param int|string|array<int|string> $type
     * @param array<int, Token>            $tokens
     */
    protected function findPrevious(int|string|array $type, array $tokens, int $start, bool $exclude = false): int|false
    {
        $i = 0;

        while (
            isset($tokens[$start - $i])
            && $exclude === $this->isTokenMatching($tokens[$start - $i], $type)
        ) {
            ++$i;
        }

        if (!isset($tokens[$start - $i])) {
            return false;
        }

        return $start - $i;
    }

    protected function addWarning(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_WARNING,
            $message,
            $token->getFilename(),
            $token->getLine(),
            $token->getPosition(),
            $messageId,
        );
    }

    protected function addFileWarning(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_WARNING,
            $message,
            $token->getFilename(),
            null,
            null,
            $messageId,
        );
    }

    protected function addError(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_ERROR,
            $message,
            $token->getFilename(),
            $token->getLine(),
            $token->getPosition(),
            $messageId,
        );
    }

    protected function addFileError(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_ERROR,
            $message,
            $token->getFilename(),
            null,
            null,
            $messageId,
        );
    }

    private function addMessage(
        int $messageType,
        string $message,
        string $fileName,
        ?int $line = null,
        ?int $position = null,
        ?string $messageId = null
    ): bool {
        $id = new ViolationId(
            $this->getShortName(),
            $messageId ?? ucfirst(strtolower(Violation::getLevelAsString($messageType))),
            $line,
            $position,
        );
        foreach ($this->ignoredViolations as $ignoredViolation) {
            if ($ignoredViolation->match($id)) {
                return false;
            }
        }

        $report = $this->report;
        if (null !== $report) { // The report is null when we are fixing the file.
            $violation = new Violation(
                $messageType,
                $message,
                $fileName,
                $this->getName(),
                $id,
            );

            $report->addViolation($violation);
        }

        return true;
    }
}
