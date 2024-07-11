# Rules & Standard

## Rules

### Fixable

- **BlankEOFRule**:

  Ensures that files ends with one blank line.

- **BlockNameSpacingRule**:

  Ensures there is one space before and after block names.

- **DelimiterSpacingRule** (Configurable):

  Ensures there is one space before `}}`, `%}` and `#}`, and after `{{`, `{%`, `{#`. Options are:
    - `skipIfNewLine`: ignore the spacing check if there is a new line (default true).

- **EmptyLinesRule**:

  Ensures that 2 empty lines do not follow each other.

- **HashQuoteRule** (Configurable): 

  Ensures that hash key are not unnecessarily quoted. Options are:
    - `useQuote`: hash key must be preferred quoted (default false).

- **IncludeFunctionRule**:

  Ensures that include function is used instead of function tag.

- **IndentRule** (Configurable):

  Ensures that files are indented with spaces (or tabs). Options are:
    - `spaceRatio`: how many spaces replace a tab (default 4).
    - `useTab`: indentation must be done with tab (default false).

- **OperatorNameSpacingRule**:

  Ensures there is no consecutive spaces inside operator names.

- **OperatorSpacingRule**:

  Ensures there is one space before and after an operator except for `..`.

- **PunctuationSpacingRule** (Configurable):

  Ensures there is no space before and after a punctuation except for `:` and `,`. Options are:
    - `punctuationWithSpaceBefore`: used to override the space before check.
    - `punctuationWithSpaceAfter`: used to override the space after check.

- **SingleQuoteRule** (Configurable):

  Ensures that strings use single quotes when possible. Options are:
    - `skipStringContainingSingleQuote`: ignore double-quoted strings that contains single-quotes (default true).

- **TrailingCommaMultiLineRule** (Configurable):

  Ensures that multi-line arrays, objects and argument lists have a trailing comma. Options are:
    - `useTrailingComma`: trailing comma must be used (default true).

- **TrailingCommaSingleLineRule**: 

  Ensures that single-line arrays, objects and argument lists do not have a trailing comma.

- **TrailingSpaceRule**:

  Ensures that files have no trailing spaces.

### Non-fixable

To use these rules, you have to [allow non-fixable rules](configuration.md#non-fixable-rules) on your ruleset.

- **DirectoryNameRule** (Configurable):

  Ensures that directory name uses snake_case. Options are:
    - `case`: preferred case to use (default snake_case).
    - `baseDirectory`: used to restrict the check for directories inside this one.
    - `ignoredSubDirectories`: specific sub-directories to ignore.
    - `optionalPrefix`: allow to prefix directory name by this prefix.

- **FileExtensionRule**:

  Ensures that file name uses two extensions (e.g. index.html.twig).

- **FileNameRule** (Configurable):

  Ensures that file name uses snake_case. Options are:
    - `case`: preferred case to use (default snake_case).
    - `baseDirectory`: used to restrict the check for files inside this directory.
    - `ignoredSubDirectories`: specific sub-directories to ignore.
    - `optionalPrefix`: allow to prefix file name by this prefix.

- **VariableNameRule** (Configurable):

  Ensures that variable name uses snake_case. Options are:
    - `case`: preferred case to use (default snake_case).
    - `optionalPrefix`: allow to prefix directory name by this prefix.

### Node-based rules

- **ForbiddenBlockRule** (Configurable):

  Ensures some blocks are not used. Options are:
    - `blocks`: the name of the forbidden blocks.

- **ForbiddenFilterRule** (Configurable):

  Ensures some blocks are not used. Options are:
    - `filter`: the name of the forbidden blocks.

- **ForbiddenFunctionRule** (Configurable):

  Ensures some blocks are not used. Options are:
    - `function`: the name of the forbidden blocks.

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
- HashQuoteRule
- IncludeFunctionRule
- IndentRule
- SingleQuoteRule
- TrailingCommaMultiLineRule
- TrailingCommaSingleLineRule
- TrailingSpaceRule

**Symfony**:
- Twig
- DirectoryNameRule
- FileNameRule
- FileExtensionRule
