# Twig CS Fixer

[![PHP Version](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/require/php)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![Latest Stable Version](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/v)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![License](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/license)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![Actions Status](https://github.com/VincentLanglet/Twig-CS-Fixer/workflows/Test/badge.svg)](https://github.com/RobDWaller/csp-generator/actions)
[![Coverage](https://codecov.io/gh/VincentLanglet/Twig-CS-Fixer/branch/main/graph/badge.svg)](https://codecov.io/gh/VincentLanglet/Twig-CS-Fixer/branch/main)
[![Type Coverage](https://shepherd.dev/github/VincentLanglet/Twig-CS-Fixer/coverage.svg)](https://shepherd.dev/github/VincentLanglet/Twig-CS-Fixer)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/VincentLanglet/Twig-CS-Fixer/main)](https://infection.github.io)

## Installation

This standard can be installed with the [Composer](https://getcomposer.org/) dependency manager.

Add the coding standard as a dependency of your project
```bash
composer require --dev vincentlanglet/twig-cs-fixer
```

Then, use it!
```bash
bin/twig-cs-fixer lint /path/to/code
bin/twig-cs-fixer lint --fix /path/to/code
```

## Twig Coding Standard Rules

From the [official one](https://twig.symfony.com/doc/3.x/coding_standards.html).

### Delimiter spacing

Put one (and only one) space after the start of a delimiter (`{{`, `{%`, and `{#`)
and before the end of a delimiter (`}}`, `%}`, and `#}`).

When using the whitespace control character, do not put any spaces between it and the delimiter.

### Operator spacing

Put one (and only one) space before and after the following operators:
comparison operators (`==`, `!=`, `<`, `>`, `>=`, `<=`), math operators (`+`, `-`, `/`, `*`, `%`, `//`, `**`),
logic operators (`not`, `and`, `or`), `~`, `is`, `in`, and the ternary operator (`?:`).

Do not put any spaces before and after the operator `..`.

### Punctuation spacing

Put one (and only one) space after the `:` sign in hashes and `,` in arrays and hashes.

Do not put any spaces after an opening parenthesis and before a closing parenthesis in expressions.

Do not put any spaces before and after the following operators: `|`, `.`, `[]`.

Do not put any spaces before and after the parenthesis used for filter and function calls.

Do not put any spaces before and after the opening and the closing of arrays and hashes.

## Custom configuration

### Standard

By default, the generic standard is enabled with the twig coding standard rules and the following sniffs:
 - `BlankEOFSniff`: Ensure that files ends with one blank line.
 - `EmptyLinesSniff`: Checks that there are not 2 empty lines following each other.
 - `TrailingCommaSingleLineSniff`: Ensure that single-line arrays, objects and arguments list does not have a trailing comma.
 - `TrailingSpaceSniff`: Ensure that files has no trailing spaces.

If you want to use a custom standard and/or add/disable a sniff, you can provide your own configuration with
a `.twig-cs-fixer.php` file which returns a `TwigCsFixer\Config\Config` class:
```php
<?php

$ruleset = new TwigCsFixer\Ruleset\Ruleset();
$ruleset->addStandard(new TwigCsFixer\Standard\Generic());
$ruleset->removeSniff(TwigCsFixer\Sniff\EmptyLinesSniff::class);

$config = new TwigCsFixer\Config\Config();
$config->setRuleset($ruleset);

return $config;
```

If your config is not located in your current directory, you can pass his path when running the command:
```bash
bin/twig-cs-fixer lint --config=dir/.twig-cs-fixer.php /path/to/code
```

### Files

By default, all the `.twig` files in the current directory are linted, except the one in the `vendor` directory.

If you want to lint a specific files/directory you can pass it as argument, but if you want a more sophisticated
rule, you can configure it in the `.twig-cs-fixer.php` file:
```php
<?php

$finder = new TwigCsFixer\File\Finder();
$finder->exclude('myCustomDirectory');

$config = new TwigCsFixer\Config\Config();
$config->setFinder($finder);

return $config;
```

### Cache

By default, the result of the run is cached in a `.twig-cs-fixer.cache` file so the subsequent runs are much
faster. The cache is invalidated when a different php version, twig-cs-fixer version or ruleset is used.

If you want to use a custom path for the cache file you can configure it in the `.twig-cs-fixer.php` file:
```php
<?php

$config = new TwigCsFixer\Config\Config();
$config->setCacheFile('tmp/.twig-cs-fixer.cache');

return $config;
```

If you want to disable the cache once, you can pass `--no-cache` when running the command:
```bash
bin/twig-cs-fixer lint --no-cache
```
If you want to completely disable the cache, just set the cache file to `null` in your config.
