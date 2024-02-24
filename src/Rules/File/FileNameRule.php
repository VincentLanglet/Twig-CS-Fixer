<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\File;

use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Util\StringUtil;

/**
 * Ensures that file name uses snake_case (Configurable).
 */
final class FileNameRule extends AbstractRule implements ConfigurableRuleInterface
{
    public const SNAKE_CASE = 'snake_case';
    public const CAMEL_CASE = 'camelCase';
    public const PASCAL_CASE = 'PascalCase';
    public const KEBAB_CASE = 'kebab-case';

    /**
     * @param self::*       $case
     * @param array<string> $ignoredSubDirectories
     */
    public function __construct(
        private string $case = self::SNAKE_CASE,
        private ?string $baseDirectory = null,
        private array $ignoredSubDirectories = [],
        private string $allowedPrefix = '',
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'case' => $this->case,
            'baseDirectory' => $this->baseDirectory,
            'ignoredSubDirectories' => $this->ignoredSubDirectories,
            'allowedPrefix' => $this->allowedPrefix,
        ];
    }

    protected function process(int $tokenPosition, array $tokens): void
    {
        if (0 !== $tokenPosition) {
            return;
        }

        $token = $tokens[$tokenPosition];
        $fileName = FileHelper::getFileName(
            $token->getFilename(),
            $this->baseDirectory,
            $this->ignoredSubDirectories,
        );
        if (null === $fileName) {
            return;
        }

        // We're only checking the first part before a dot,
        // in order to avoid conflict with some file extensions.
        $fileName = explode('.', FileHelper::removeDot($fileName))[0];

        $prefix = '';
        if (str_starts_with($fileName, $this->allowedPrefix)) {
            $prefix = $this->allowedPrefix;
            $fileName = substr($fileName, \strlen($this->allowedPrefix));
        }

        $expected = match ($this->case) {
            self::SNAKE_CASE => StringUtil::toSnakeCase($fileName),
            self::CAMEL_CASE => StringUtil::toCamelCase($fileName),
            self::PASCAL_CASE => StringUtil::toPascalCase($fileName),
            self::KEBAB_CASE => StringUtil::toKebabCase($fileName),
        };

        if ($expected !== $fileName) {
            $this->addFileError(
                sprintf('The file name must use %s; expected %s.', $this->case, $prefix.$expected),
                $token,
            );
        }
    }
}
