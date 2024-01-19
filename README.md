# Twig CS Fixer

[![PHP Version](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/require/php)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![Latest Stable Version](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/v)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![License](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/license)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![Actions Status](https://github.com/VincentLanglet/Twig-CS-Fixer/workflows/Test/badge.svg)](https://github.com/RobDWaller/csp-generator/actions)
[![Coverage](https://codecov.io/gh/VincentLanglet/Twig-CS-Fixer/branch/main/graph/badge.svg)](https://codecov.io/gh/VincentLanglet/Twig-CS-Fixer/branch/main)
[![Type Coverage](https://shepherd.dev/github/VincentLanglet/Twig-CS-Fixer/coverage.svg)](https://shepherd.dev/github/VincentLanglet/Twig-CS-Fixer)
[![Infection MSI](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FVincentLanglet%2FTwig-CS-Fixer%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/VincentLanglet/Twig-CS-Fixer/main)


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

- [CLI options](docs/command.md)
- [Configuration file](docs/configuration.md)
- [How to disable a rule on a specific file or line](docs/identifiers.md)
- [Rules & Standard](docs/rules.md)
