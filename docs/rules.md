# Rules & Standard

## Rules

### Fixable

- **BlockNameSpacingRule**: ensures there is one space before and after block names.
- **DelimiterSpacingRule**: ensures there is one space before '}}', '%}' and '#}', and after '{{', '{%', '{#'.
- **OperatorNameSpacingRule**: ensures there is no consecutive spaces inside operator names.
- **OperatorSpacingRule**: ensures there is one space before and after an operator except for '..'.
- **PunctuationSpacingRule**: ensures there is no space before and after a punctuation except for ':' and ','.
- **TrailingCommaSingleLineRule**: ensures that single-line arrays, objects and argument lists do not have a trailing comma.
- **BlankEOFRule**: ensures that files ends with one blank line.
- **EmptyLinesRule**: ensures that 2 empty lines do not follow each other.
- **IndentRule**: ensures that files are not indented with tabs.
- **TrailingSpaceRule**: ensures that files have no trailing spaces.

### Non-fixable

- **DirectoryNameRule**: ensures that directory name use snake_case (configurable).
- **FileNameRule**: ensures that file name use snake_case (configurable).
- **VariableNameRule**: ensures that variable name use snake_case (configurable).

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
