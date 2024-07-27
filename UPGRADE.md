UPGRADE FROM 2.x to 3.0
=======================

- The `checkstyle` and `junit` reporter now try to use absolute path rather than relative path.
- In debug mode, the report now contains both the identifier and the message of the error.
- The position of `TrailingCommaMultiLineRule` error changed.
- The position of `TrailingCommaSingleLineRule` error changed.
- `TwigCsFixer\Command\TwigCsFixerCommand` class moved to `TwigCsFixer\Console\Command` folder.
- `TwigCsFixer\Report\Reporter\ReporterInterface` now require a `getName` method.

If you never implemented a custom rule, nothing else changed. Otherwise, ...

### AbstractRule

```diff
- $this->isTokenMatching($token, $type, $value)
+ $token->isTokenMatching($type, $value)
```

```diff
- $this->findNext($type, $tokens, $start)
+ $tokens->findNext($type, $start)

- $this->findNext($type, $tokens, $start, true)
+ $tokens->findNext($type, $start, null, true)
```

```diff
- $this->findPrevious($type, $tokens, $start)
+ $tokens->findPrevious($type, $start)

- $this->findPrevious($type, $tokens, $start, true)
+ $tokens->findPrevious($type, $start, null, true)
```

```diff
- protected function process(int $tokenPosition, array $tokens): void;
+ protected function process(int $tokenIndex, Tokens $tokens): ?int;
```

### AbstractSpacingRule

```diff
- protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int;
+ protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int;
```

```diff
- protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int;
+ protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int;
```

### RuleInterface

```diff
- public function lintFile(array $tokens, Report $report, array $ignoredViolations = []): void;
+ public function lintFile(Tokens $tokens, Report $report): void;
```

### FixableRuleInterface

```diff
- public function fixFile(array $tokens, FixerInterface $fixer, array $ignoredViolations = []): void;
+ public function fixFile(Tokens $tokens, FixerInterface $fixer): void;
```

### TokenizerInterface

```diff
- /**
-   * @return array{list<Token>, list<ViolationId>}
-   */
-  public function tokenize(Source $source): array;
+  public function tokenize(Source $source): Tokens;
```

### Token

The `Token::NAME_TYPE` has been split in four:
- `Token::FILTER_NAME_TYPE`
- `Token::FUNCTION_NAME_TYPE`
- `Token::TEST_NAME_TYPE`
- `Token::NAME_TYPE`

```diff
- $token->getPosition();
+ $token->getLinePosition();
```

### Directory

```diff
- (new Directory($dir))->getRelativePathTo($file);
+ FileHelper::getRelativePath($file, $dir);
```
