# Configuration file

## Standard

By default, the twig-cs-fixer standard is enabled with the twig coding standard rules and the following rules:

- `BlankEOFRule`: ensures that files end with one blank line.
- `BlockNameSpacingRule`: ensures there is one space before and after block names.
- `EmptyLinesRule`: ensures that 2 empty lines do not follow each other.
- `IndentRule`: ensures that files are not indented with tabs.
- `TrailingCommaSingleLineRule`: ensures that single-line arrays, objects and argument lists do not have a trailing comma.
- `TrailingSpaceRule`: ensures that files have no trailing spaces.

If you want to use the basic Twig standard, another standard and/or add/disable a rule, you can provide
your own configuration with a `.twig-cs-fixer.php` file which returns a `TwigCsFixer\Config\Config` class:

```php
<?php

$ruleset = new TwigCsFixer\Ruleset\Ruleset();
$ruleset->addStandard(new TwigCsFixer\Standard\Twig());
$ruleset->addRule(\TwigCsFixer\Rules\Whitespace\EmptyLinesRule::class);

$config = new TwigCsFixer\Config\Config();
$config->setRuleset($ruleset);

return $config;
```

If your config is not located in your current directory, you can specify its path using `--config` when running the command:

```bash
vendor/bin/twig-cs-fixer lint --config=dir/.twig-cs-fixer.php /path/to/code
```

## Files

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

## Cache

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

## Token parser & Twig Extension

If you're using custom token parsers or binary/unary operators, they can be added in your config:

```php
<?php

$config = new TwigCsFixer\Config\Config();
$config->addTwigExtension(new App\Twig\CustomTwigExtension());
$config->addTokenParser(new App\Twig\CustomTokenParser());

return $config;
```
