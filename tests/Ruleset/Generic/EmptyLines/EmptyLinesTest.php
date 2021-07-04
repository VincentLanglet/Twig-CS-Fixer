<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Ruleset\Generic\EmptyLines;

use TwigCsFixer\Ruleset\Generic\EmptyLinesSniff;
use TwigCsFixer\Tests\Ruleset\AbstractSniffTest;

/**
 * Class EmptyLinesTest
 */
class EmptyLinesTest extends AbstractSniffTest
{
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new EmptyLinesSniff(), [
            [3 => 1],
        ]);
    }
}
