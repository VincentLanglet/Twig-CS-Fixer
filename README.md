# Twig CS Fixer

[![PHP Version](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/require/php)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![Latest Stable Version](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/v)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![License](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/license)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![Actions Status](https://github.com/VincentLanglet/Twig-CS-Fixer/workflows/Test/badge.svg)](https://github.com/RobDWaller/csp-generator/actions)
[![Coverage](https://codecov.io/gh/VincentLanglet/Twig-CS-Fixer/branch/main/graph/badge.svg)](https://codecov.io/gh/VincentLanglet/Twig-CS-Fixer/branch/main)
[![Type Coverage](https://shepherd.dev/github/VincentLanglet/Twig-CS-Fixer/coverage.svg)](https://shepherd.dev/github/VincentLanglet/Twig-CS-Fixer)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/VincentLanglet/Twig-CS-Fixer/main)](https://infection.github.io)

## Installation

This standard can be installed with [Composer](https://getcomposer.org/).

Add the coding standard as a dependency of your project

```bash
composer require --dev vincentlanglet/twig-cs-fixer
```

Then, use it!

```bash
vendor/bin/twig-cs-fixer lint /path/to/code
vendor/bin/twig-cs-fixer lint --fix /path/to/code
```

## Twig Coding Standard Rules

From the [official one](https://twig.symfony.com/doc/3.x/coding_standards.html).

### Delimiter spacing

Ensures there is a single space after a delimiter opening (`{{`, `{%` and `{#`)
and before a delimiter closing (`}}`, `%}` and `#}`).

When using a whitespace control character, do not put any spaces between it and the delimiter.

### Operator spacing

Ensures there is a single space before and after the following operators:
comparison operators (`==`, `!=`, `<`, `>`, `>=`, `<=`), math operators (`+`, `-`, `/`, `*`, `%`, `//`, `**`),
logic operators (`not`, `and`, `or`), `~`, `is`, `in`, and the ternary operator (`?:`).

Removes any space before and after the `..` operator.

### Punctuation spacing

Ensures there is a single space after `:` in hashes and `,` in arrays and hashes.

Removes any space after an opening parenthesis and before a closing parenthesis in expressions.

Removes any space before and after the following operators: `|`, `.`, `[]`.

Removes any space before and after parenthesis in filter and function calls.

Removes any space before and after opening and closing of arrays and hashes.

## Custom configuration

### Standard

By default, the generic standard is enabled with the twig coding standard rules and the following sniffs:

 - `BlankEOFSniff`: ensures that files end with one blank line.
 - `EmptyLinesSniff`: ensures that 2 empty lines do not follow each other.
 - `TrailingCommaSingleLineSniff`: ensures that single-line arrays, objects and argument lists do not have a trailing comma.
 - `TrailingSpaceSniff`: ensures that files have no trailing spaces.

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

If your config is not located in your current directory, you can specify its path using `--config` when running the command:

```bash
vendor/bin/twig-cs-fixer lint --config=dir/.twig-cs-fixer.php /path/to/code
```

### Files

By default, all `.twig` files in the current directory are linted, except the ones in the `vendor` directory.

If you want to lint specific files or directories you can pass them as argument. If you want a more sophisticated
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

By default, cache is enabled and stored in `.twig-cs-fixer.cache`. Further runs are therefore much
faster. Cache is invalidated when a different PHP version, twig-cs-fixer version or ruleset is used.

If you want a custom cache location you can configure it in `.twig-cs-fixer.php`:

```php
<?php

$config = new TwigCsFixer\Config\Config();
$config->setCacheFile('/tmp/.twig-cs-fixer.cache');

return $config;
```

To disable cache you can either pass `--no-cache` when running the command:

```bash
vendor/bin/twig-cs-fixer lint --no-cache
```

or set the cache file to `null` in your config:

```php
<?php

$config = new TwigCsFixer\Config\Config();
$config->setCacheFile(null);

return $config;
```

### Token parser

If you're using custom token parsers, they can be added in your config:

```php
<?php

$config = new TwigCsFixer\Config\Config();
$config->addTokenParser(new App\Twig\CustomTokenParser());

return $config;
```
