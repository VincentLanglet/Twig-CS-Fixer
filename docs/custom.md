# How to write a custom rule

## Token-based rules

### Token types

In order to write a custom rule, you first need to understand how the twig file is parsed.
The `TwigCsFixer\Token\Tokenizer` transform the file into a list of tokens which can be:

- **TwigCsFixer\Token\Token::EOF_TYPE**:

  This token is the last one of the file.

- **TwigCsFixer\Token\Token::TEXT_TYPE**:

  Any basic text which are not inside `{#`, `{{`, `{%` delimiters. Does not include whitespaces.

- **TwigCsFixer\Token\Token::BLOCK_START_TYPE**:

  The `{%` delimiter.

- **TwigCsFixer\Token\Token::VAR_START_TYPE**:

  The `{{` delimiter.

- **TwigCsFixer\Token\Token::BLOCK_END_TYPE**:

  The `%}` delimiter.

- **TwigCsFixer\Token\Token::VAR_END_TYPE**:

  The `}}` delimiter.

- **TwigCsFixer\Token\Token::NAME_TYPE**:

  Any variable inside `{%` or `{{` delimiters. Like name in `{{ name }}` or `{% if foo(name) %}`

- **TwigCsFixer\Token\Token::NUMBER_TYPE**:

  Any number inside `{%` or `{{` delimiters. Like 42 in `{{ 42 }}` or `{% if foo(42) %}`

- **TwigCsFixer\Token\Token::STRING_TYPE**:

  Any single quote string or double quote string without interpolation string inside `{%` or `{{` delimiters.
  Like `'string'`/`"string"` in `{{ 'string' }}`, `{% if foo('string') %}`, `{{ "string" }}` or `{% if foo("string") %}`.
  It can also include part of string with interpolation, like both string in `{{ 'string#{interpolation}string' }}`.

- **TwigCsFixer\Token\Token::OPERATOR_TYPE**:

  Any twig operator like `+`, `-`, `and`, `or`, etc. Also include `?` and `:` when used in ternary.

- **TwigCsFixer\Token\Token::PUNCTUATION_TYPE**:

  One of the `(`, `)`, `[`, `]`, `{`, `}`, `:`, `.`, `,`, `|` characters. For `:`, only when it's not a ternary.

- **TwigCsFixer\Token\Token::INTERPOLATION_START_TYPE**:

  The characters `#{` inside a double-quoted string. Like `{{ "string #{interpolation}" }}`.

- **TwigCsFixer\Token\Token::INTERPOLATION_END_TYPE**:

  The characters `}` inside a double-quoted string. Like `{{ "string #{interpolation}" }}`.

- **TwigCsFixer\Token\Token::ARROW_TYPE**:

  The `=>` used for arrow functions.

- **TwigCsFixer\Token\Token::SPREAD_TYPE**:

  The `...` spread operator.

- **TwigCsFixer\Token\Token::DQ_STRING_START_TYPE**:

  The `"` used at the start of double-quoted string with interpolation. Like `{{ "string#{interpolation}" }}`.

- **TwigCsFixer\Token\Token::DQ_STRING_END_TYPE**:

  The `"` used at the end of double-quoted string with interpolation. Like `{{ "string#{interpolation}" }}`.

- **TwigCsFixer\Token\Token::BLOCK_NAME_TYPE**:

  The first non-empty element after the `{%` token. Like if in `{% if ... %}` or block in `{% block ... %}`.

- **TwigCsFixer\Token\Token::FUNCTION_NAME_TYPE**:

  The name of a function. Like in `{{ function(foo) }}`.

- **TwigCsFixer\Token\Token::FILTER_NAME_TYPE**:

  The name of a filter function. Like in `{{ foo|filter(bar) }}`.

- **TwigCsFixer\Token\Token::MACRO_NAME_TYPE**:

  The name used in the definition of a macro function. Like `foo` in `{% macro foo() %}`.

- **TwigCsFixer\Token\Token::MACRO_VAR_NAME_TYPE**:

  The name used for params of a macro function. Like `bar` in `{% macro foo(bar) %}`.

- **TwigCsFixer\Token\Token::TEST_NAME_TYPE**:

  The name of a test function. Like in `{% if foo is test(bar) %}`.

- **TwigCsFixer\Token\Token::HASH_KEY_NAME_TYPE**:

  Any hash key represented as name. Like `foo` in `{ foo: bar }` or `{ foo }` but not in `{ 'foo': bar }`.

- **TwigCsFixer\Token\Token::TYPE_NAME_TYPE**:

  Similar to hash key, but in types definition. Like `foo` in `{% types { foo: 'bar' } %}`.

- **TwigCsFixer\Token\Token::WHITESPACE_TYPE**:

  Any whitespace separating text or expressions. Does not include commented whitespaces.

- **TwigCsFixer\Token\Token::TAB_TYPE**:

  Any tabulation separating text or expressions. Does not include commented tabulations.

- **TwigCsFixer\Token\Token::EOL_TYPE**:

  Any end of line except commented end of lines.

- **TwigCsFixer\Token\Token::COMMENT_START_TYPE**:

  The `{#` delimiter.

- **TwigCsFixer\Token\Token::COMMENT_TEXT_TYPE**:

  Any commented text. Does not include whitespaces.

- **TwigCsFixer\Token\Token::COMMENT_WHITESPACE_TYPE**:

  Any commented whitespace.

- **TwigCsFixer\Token\Token::COMMENT_TAB_TYPE**:

  Any commented tabulation.

- **TwigCsFixer\Token\Token::COMMENT_EOL_TYPE**:

  Any commented end of line.

- **TwigCsFixer\Token\Token::COMMENT_END_TYPE**:

  The `#}` delimiter.

- **TwigCsFixer\Token\Token::INLINE_COMMENT_START_TYPE**:

  The `#` delimiter.

- **TwigCsFixer\Token\Token::INLINE_COMMENT_TEXT_TYPE**:

  Any commented text inside an inline comment. Does not include whitespaces.

- **TwigCsFixer\Token\Token::INLINE_COMMENT_WHITESPACE_TYPE**:

  Any commented whitespace inside an inline comment.

- **TwigCsFixer\Token\Token::INLINE_COMMENT_TAB_TYPE**:

  Any commented tabulation inside an inline comment.

- **TwigCsFixer\Token\Token::NAMED_ARGUMENT_SEPARATOR_TYPE**:

  The `=` or `:` separator used when using named argument. Like `{{ foo(bar=true, baz: false) }}`.

### Rule

Then, the easiest way to write a custom rule is to implement the `TwigCsFixer\Rules\AbstractRule` class
or the `TwigCsFixer\Rules\AbstractFixableRule` if the rule can be automatically fixed.

```php
final class MyCustomRule extends \TwigCsFixer\Rules\AbstractRule {
    protected function process(int $tokenIndex, \TwigCsFixer\Token\Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(...)) {
            // Skip if the token is not matching some conditions.
            return;
        }
        
        $nextToken = $tokens->findNext(...); // Look for next token based on conditions.
        $previousToken = $tokens->findPrevious(...); // Look for previous token based on conditions.
        if (...) {
            // Skip if the token is valid.
            return;
        }
        
        // Use $this->addError(...) if the error is not fixable.
        $fixer = $this->addFixableError('Custom message.', $token);
        if (null === $fixer) {
            // Fixer is null when you're linting the file.
            return;
        }
        
        $fixer->beginChangeSet();
        // Use some of the following functions base of the wanted behavior:
        // $fixer->replaceToken(...);
        // $fixer->addNewline(...);
        // $fixer->addNewlineBefore(...);
        // $fixer->addContent(...);
        // $fixer->addContentBefore(...);
        $fixer->endChangeSet();
    }
}
```

## Node-based rules

Rules can also be based on the Twig Node and NodeVisitor logic. Because they are
different from the default token based rules, these rules have some limitations:
- they cannot be fixable.
- they can only report the line with the error but not the token position.

Still, these rules can be easier to be written for some static analysis.
You can get inspiration from the `src/Rules/Node` folder.

```php
final class MyCustomRule extends \TwigCsFixer\Rules\Node\AbstractNodeRule {
    public function enterNode(\Twig\Node\Node $node, \Twig\Environment $env): Node
    {
        // Do some logic
    }
}
```
