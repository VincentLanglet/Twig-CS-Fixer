<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Environment\Fixtures;

use Twig\Extension\AbstractExtension;
use Twig\TokenParser\TokenParserInterface;

final class CustomTwigExtension extends AbstractExtension
{
    /**
     * @return list<TokenParserInterface>
     */
    public function getTokenParsers(): array
    {
        return [
            new CustomTokenParser(),
        ];
    }
}
