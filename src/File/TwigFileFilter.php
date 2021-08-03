<?php

namespace TwigCsFixer\File;

use RecursiveFilterIterator;
use SplFileInfo;

/**
 * RecursiveFilterIterator for directories and twig files.
 */
final class TwigFileFilter extends RecursiveFilterIterator
{
    /**
     * @return bool
     */
    public function accept(): bool
    {
        /** @var SplFileInfo $file */
        $file = $this->current();

        return $file->isDir() || 'twig' === $file->getExtension();
    }
}
