<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\File;

use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Tokens;
use TwigCsFixer\Util\StringUtil;

/**
 * Ensures that directory name uses snake_case (Configurable).
 */
final class DirectoryNameRule extends AbstractRule implements ConfigurableRuleInterface
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
        private string $optionalPrefix = '',
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'case' => $this->case,
            'baseDirectory' => $this->baseDirectory,
            'ignoredSubDirectories' => $this->ignoredSubDirectories,
            'optionalPrefix' => $this->optionalPrefix,
        ];
    }

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        if (0 !== $tokenIndex) {
            return;
        }

        $token = $tokens->get($tokenIndex);
        $directories = FileHelper::getDirectories(
            $token->getFilename(),
            $this->baseDirectory,
            $this->ignoredSubDirectories,
        );

        foreach ($directories as $directory) {
            $prefix = '';
            if (str_starts_with($directory, $this->optionalPrefix)) {
                $prefix = $this->optionalPrefix;
                $directory = substr($directory, \strlen($this->optionalPrefix));
            }

            $expected = match ($this->case) {
                self::SNAKE_CASE => StringUtil::toSnakeCase($directory),
                self::CAMEL_CASE => StringUtil::toCamelCase($directory),
                self::PASCAL_CASE => StringUtil::toPascalCase($directory),
                self::KEBAB_CASE => StringUtil::toKebabCase($directory),
            };

            if ($expected !== $directory) {
                $this->addFileError(
                    \sprintf('The directory name must use %s; expected %s.', $this->case, $prefix.$expected),
                    $token,
                );
            }
        }
    }
}
