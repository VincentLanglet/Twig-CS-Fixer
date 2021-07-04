<?php

namespace TwigCsFixer\Config;

/**
 * Class TwigFileFilter
 */
class TwigFileFilter extends \RecursiveFilterIterator
{
    /**
     * @return bool
     */
    public function accept()
    {
        /** @var \SplFileInfo $file */
        $file = $this->current();

        return $file->isDir() || 'twig' === $file->getExtension();
    }
}
