<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\RequireWithOnly;

use TwigCsFixer\Sniff\RequireWithOnlySniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

final class RequireWithOnlyTest extends AbstractSniffTestCase
{
    public function testSniff(): void
    {
        $this->checkSniff(new RequireWithOnlySniff(), [
            [1 => 48],
            [7 => 1],
        ]);
    }
}
