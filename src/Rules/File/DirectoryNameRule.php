<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\File;

use Symfony\Component\String\UnicodeString;
use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;

/**
 * Ensures directory name is snake_case (Configurable).
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
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'case'                  => $this->case,
            'baseDirectory'         => $this->baseDirectory,
            'ignoredSubDirectories' => $this->ignoredSubDirectories,
        ];
    }

    protected function process(int $tokenPosition, array $tokens): void
    {
        if (0 !== $tokenPosition) {
            return;
        }

        $token = $tokens[$tokenPosition];
        $directories = FileHelper::getDirectories(
            $token->getFilename(),
            $this->baseDirectory,
            $this->ignoredSubDirectories,
        );

        foreach ($directories as $directory) {
            $expected = match ($this->case) {
                self::SNAKE_CASE  => (new UnicodeString($directory))->snake()->toString(),
                self::CAMEL_CASE  => (new UnicodeString($directory))->camel()->toString(),
                self::PASCAL_CASE => ucfirst((new UnicodeString($directory))->camel()->toString()),
                self::KEBAB_CASE  => (new UnicodeString($directory))->snake()->replace('_', '-')->toString(),
            };

            if ($expected !== $directory) {
                $this->addFileError(
                    sprintf('The directory name must use %s; expected %s.', $this->case, $expected),
                    $token,
                );
            }
        }
    }
}
