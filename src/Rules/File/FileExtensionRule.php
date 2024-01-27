<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\File;

use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;

/**
 * Ensures file name uses two extensions (e.g. index.html.twig).
 */
final class FileExtensionRule extends AbstractRule implements ConfigurableRuleInterface
{
    /**
     * @param array<string> $ignoredSubDirectories
     */
    public function __construct(
        private ?string $baseDirectory = null,
        private array $ignoredSubDirectories = [],
    ) {
    }

    public function getConfiguration(): array
    {
        return [
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
        $fileName = FileHelper::getFileName(
            $token->getFilename(),
            $this->baseDirectory,
            $this->ignoredSubDirectories,
        );
        if (null === $fileName) {
            return;
        }

        $fileParts = explode('.', $fileName);
        $fileExtension = array_pop($fileParts);
        if ('twig' !== $fileExtension) {
            // Not a Twig file: skip
            return;
        }

        $formatExtension = array_pop($fileParts);
        if ([] === $fileParts || null === $formatExtension) {
            $this->addFileError(
                sprintf('The file must use two extensions; found ".%s".', $fileExtension),
                $token,
            );

            return;
        }

        if (1 !== preg_match('/^[a-z][a-z0-9]{1,3}$/', $formatExtension)) {
            $this->addFileError(
                sprintf('The file must use a valid format extension; found ".%s.%s".', $formatExtension, $fileExtension),
                $token,
            );
        }
    }
}
