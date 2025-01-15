# Twig CS Fixer

[![PHP Version](https://poser.pugx.org/vincentlanglet/twig-cs-fixer/require/php)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![Latest Stable Version](https://poser.pugx.org/vincentlanglet/twig-cs-fixer/v)](https://github.com/VincentLanglet/Twig-CS-Fixer/releases/latest)
[![License](https://poser.pugx.org/vincentlanglet/twig-cs-fixer/license)](https://github.com/VincentLanglet/Twig-CS-Fixer/blob/main/LICENCE)
[![Actions Status](https://github.com/VincentLanglet/Twig-CS-Fixer/workflows/Test/badge.svg)](https://github.com/RobDWaller/csp-generator/actions)
[![Coverage](https://codecov.io/gh/VincentLanglet/Twig-CS-Fixer/branch/main/graph/badge.svg)](https://codecov.io/gh/VincentLanglet/Twig-CS-Fixer/branch/main)
[![Infection MSI](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FVincentLanglet%2FTwig-CS-Fixer%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/VincentLanglet/Twig-CS-Fixer/main)

## Installation

### From composer

This tool can be installed with [Composer](https://getcomposer.org/).

Add the package as a dependency of your project

```bash
composer require --dev vincentlanglet/twig-cs-fixer
```

Then, use it!

```bash
vendor/bin/twig-cs-fixer lint /path/to/code
vendor/bin/twig-cs-fixer lint --fix /path/to/code
```

> [!NOTE]
> Although [bin-dependencies may have composer conflicts](https://github.com/bamarni/composer-bin-plugin#why-a-hard-problem-with-a-simple-solution),
> this is the recommended way because it will autoload everything you need.

### As a PHAR

You can always fetch the stable version as a Phar archive through the following
link with the `VERSION` you're looking for:

```bash
wget -c https://github.com/VincentLanglet/Twig-CS-Fixer/releases/download/VERSION/twig-cs-fixer.phar
```

The PHAR files are signed with a public key which can be queried at 
`keys.openpgp.org` with the id `AC0E7FD8858D80003AA88FF8DEBB71EDE9601234`.

> [!TIP]
> You will certainly need to add
> ```php
> require_once __DIR__.'/vendor/autoload.php';
> ```
> in your [config file](docs/configuration.md) in order to:
> - Use existing [node based rules](docs/configuration.md#node-based-rules).
> - Write your own custom rules.

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

### Macro & Function/Filter/Test

Ensures there is a single space before and after `=` in macro argument declarations.

Ensures there is no space before and after `=` sign when using named arguments.

Ensures one space after the `:` sign when using named arguments.

Use `:` instead of `=` to separate argument names and values.

### Naming

Use snake case for all variable names.

Use snake case for all argument names.

Use snake case for all named arguments.

## Custom configuration

By default, the twig-cs-fixer standard is enabled with the twig coding standard rules and some extra rules.
This tool also provides a standard with only the twig rules
and another standard with extra rules from the symfony coding standards.

Everything is configurable, so take a look at the following documentation:
- [CLI options](docs/command.md)
- [Configuration file](docs/configuration.md)
- [How to disable a rule on a specific file or line](docs/identifiers.md)
- [Rules & Standard](docs/rules.md)
- [How to write a custom rule](docs/custom.md)
