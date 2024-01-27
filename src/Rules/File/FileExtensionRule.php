<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\File;

use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Rules\AbstractRule;

/**
 * Ensures that file name uses two extensions (e.g. index.html.twig).
 */
final class FileExtensionRule extends AbstractRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        if (0 !== $tokenPosition) {
            return;
        }

        $token = $tokens[$tokenPosition];
        $fileName = FileHelper::getFileName($token->getFilename()) ?? '';
        $fileParts = explode('.', FileHelper::removeDot($fileName));

        $fileExtension = array_pop($fileParts);
        if ('twig' !== $fileExtension) {
            return;
        }

        if (\count($fileParts) < 2) {
            $this->addFileError(
                sprintf('The file must use two extensions; found ".%s".', $fileExtension),
                $token,
            );
        }
    }
}
