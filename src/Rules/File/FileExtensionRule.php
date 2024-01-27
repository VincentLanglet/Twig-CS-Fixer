<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\File;

use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Rules\AbstractRule;

/**
 * Ensures file name uses two extensions (e.g. index.html.twig).
 */
final class FileExtensionRule extends AbstractRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        if (0 !== $tokenPosition) {
            return;
        }

        $token = $tokens[$tokenPosition];
        $fileName = FileHelper::getFileName(
            $token->getFilename(),
        );
        if (null === $fileName) {
            return;
        }

        $fileParts = explode('.', $fileName);
        $fileExtension = array_pop($fileParts);
        if ('twig' !== $fileExtension) {
            return;
        }

        $formatExtension = array_pop($fileParts);
        if ([] === $fileParts || null === $formatExtension) {
            $this->addFileError(
                sprintf('The file must use two extensions; found ".%s".', $fileExtension),
                $token,
            );
        }
    }
}
