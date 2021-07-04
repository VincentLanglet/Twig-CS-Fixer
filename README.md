# Twig CS Fixer

[![Latest Stable Version](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/v)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![License](http://poser.pugx.org/vincentlanglet/twig-cs-fixer/license)](https://packagist.org/packages/vincentlanglet/twig-cs-fixer)
[![Actions Status](https://github.com/VincentLanglet/Twig-CS-Fixer/workflows/Test/badge.svg)](https://github.com/RobDWaller/csp-generator/actions)

Documentation
-------------

## Installation

This standard can be installed with the [Composer](https://getcomposer.org/) dependency manager.

Add the coding standard as a dependency of your project
```
composer require --dev vincentlanglet/twig-cs-fixer
```

Then, use it!
```
bin/twig-cs-fixer lint /path/to/code
```

## Twig Coding Standard Rules

From the [official one](http://twig.sensiolabs.org/doc/coding_standards.html).

### Delimiter spacing

Put one (and only one) space after the start of a delimiter (`{{`, `{%`, and `{#`)
and before the end of a delimiter (`}}`, `%}`, and `#}`).

When using the whitespace control character, do not put any spaces between it and the delimiter

### Operator spacing

Put one (and only one) space before and after the following operators:
comparison operators (`==`, `!=`, `<`, `>`, `>=`, `<=`), math operators (`+`, `-`, `/`, `*`, `%`, `//`, `**`),
logic operators (`not`, `and`, `or`), `~`, `is`, `in`, and the ternary operator (`?:`)

Do not put any spaces before and after the operator `..`.

### Punctuation spacing

Put one (and only one) space after the `:` sign in hashes and `,` in arrays and hashes

Do not put any spaces after an opening parenthesis and before a closing parenthesis in expressions

Do not put any spaces before and after the following operators: `|`, `.`, `[]`

Do not put any spaces before and after the opening and the closing of arrays and hashes
