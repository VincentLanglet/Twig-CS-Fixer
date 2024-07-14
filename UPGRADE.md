UPGRADE FROM 2.x to 3.0
=======================

If you never implemented a custom rule, nothing change. Otherwise, ...

### AbstractRule

```diff
- $this->isTokenMatching($token, $type, $value)
+ $token->isTokenMatching($type, $value)
```

```diff
- $this->findNext($type, $tokens, $start)
+ $tokens->findNext($type, $start)

- $this->findNext($type, $tokens, $start, true)
- $tokens->findNext($type, $start, null, true)
```

```diff
- $this->findPrevious($type, $tokens, $start)
+ $tokens->findPrevious($type, $start)

- $this->findPrevious($type, $tokens, $start, true)
- $tokens->findPrevious($type, $start, null, true)
```

```diff
- protected function process(int $tokenPosition, array $tokens): void;
+ protected function process(int $tokenPosition, Tokens $tokens): ?int;
```

### AbstractSpacingRule

```diff
- protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int;
+ protected function getSpaceAfter(int $tokenPosition, Tokens $tokens): ?int;
```

```diff
- protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int;
+ protected function getSpaceBefore(int $tokenPosition, Tokens $tokens): ?int;
```

### RuleInterface

```diff
- public function lintFile(array $tokens, Report $report, array $ignoredViolations = []): void;
+ public function lintFile(Tokens $tokens, Report $report, array $ignoredViolations = []): void;
```

### FixableRuleInterface

```diff
- public function fixFile(array $tokens, FixerInterface $fixer, array $ignoredViolations = []): void;
+ public function fixFile(Tokens $tokens, FixerInterface $fixer, array $ignoredViolations = []): void;
```

### TokenizerInterface

```diff
- /**
-   * @return array{list<Token>, list<ViolationId>}
-   */
-  public function tokenize(Source $source): array;
+ /**
+   * @return array{Tokens, list<ViolationId>}
+   */
+  public function tokenize(Source $source): array;
```

### Token

The `Token::NAME_TYPE` has been split in four:
- `Token::FILTER_NAME_TYPE`
- `Token::FUNCTION_NAME_TYPE`
- `Token::TEST_NAME_TYPE`
- `Token::NAME_TYPE`

### Reporters

The `checkstyle` and `junit` reporter now try to use absolute path rather than relative path.
