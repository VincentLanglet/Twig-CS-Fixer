# Rules & Standard

## Rules

### Fixable

- **BlockNameSpacingRule**: ensures there is one space before and after block names.
- **DelimiterSpacingRule**: ensures there is one space before `}}`, `%}` and `#}`, and after `{{`, `{%`, `{#`.
- **IncludeFunctionRule**: ensures that include function is used instead of function tag.
- **OperatorNameSpacingRule**: ensures there is no consecutive spaces inside operator names.
- **OperatorSpacingRule**: ensures there is one space before and after an operator except for `..`.
- **PunctuationSpacingRule**: ensures there is no space before and after a punctuation except for `:` and `,` (configurable).
- **TrailingCommaSingleLineRule**: ensures that single-line arrays, objects and argument lists do not have a trailing comma.
- **BlankEOFRule**: ensures that files ends with one blank line.
- **EmptyLinesRule**: ensures that 2 empty lines do not follow each other.
- **IndentRule**: ensures that files are not indented with tabs (configurable).
- **TrailingSpaceRule**: ensures that files have no trailing spaces.

### Non-fixable

To use these rules, you have to [allow non-fixable rules](configuration.md#non-fixable-rules) on your ruleset.

- **DirectoryNameRule**: ensures that directory name uses snake_case (configurable).
- **FileNameRule**: ensures that file name uses snake_case (configurable).
- **VariableNameRule**: ensures that variable name uses snake_case (configurable).
- **FileExtensionRule**: ensures that file name uses two extensions (e.g. index.html.twig).

### Configurable rules

Some rules are configurable, those rule are implementing `\TwigCsFixer\Rules\ConfigurableRuleInterface`.

The easiest way to see how to configure such rules is to look at the `__construct()` definition
of those rules. For instance:
```php
new TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule(
    ['}' => 1],
    ['{' => 1],
);

new TwigCsFixer\Rules\Whitespace\IndentRule(3);
```

## Standards

**Twig**:
- DelimiterSpacingRule
- OperatorNameSpacingRule
- OperatorSpacingRule
- PunctuationSpacingRule
- VariableNameRule

**TwigCsFixer**:
- Twig
- BlankEOFRule
- BlockNameSpacingRule
- EmptyLinesRule
- IndentRule
- TrailingCommaSingleLineRule
- TrailingSpaceRule

**Symfony**:
- Twig
- DirectoryNameRule
- FileNameRule
- FileExtensionRule
