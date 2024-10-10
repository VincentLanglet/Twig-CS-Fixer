<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;

trait RuleTrait
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
        $shortName = (new \ReflectionClass($this))->getShortName();

        return str_ends_with($shortName, 'Rule') ? substr($shortName, 0, -4) : $shortName;
    }

    private function addMessage(
        int $messageType,
        string $message,
        string $fileName,
        ?int $line = null,
        ?int $linePosition = null,
        ?string $messageId = null,
    ): bool {
        $id = new ViolationId(
            $this->getShortName(),
            $messageId ?? ucfirst(strtolower(Violation::getLevelAsString($messageType))),
            $line,
            $linePosition,
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
