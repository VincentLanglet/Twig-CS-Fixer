# CLI options

## Reporter

The `--report` option allows to choose the output format for the linter report.

Supported formats are:
- `text` selected by default.
- `checkstyle` following the common checkstyle XML schema.
- `github` if you want annotations on GitHub actions.
- `junit` following JUnit schema XML from Jenkins.
- `null` if you don't want any reporting.

## Debug mode

The `--debug` option displays error identifiers instead of messages. This is
useful if you want to disable a specific error with a comment in your code.

See also [how to disable a rule on a specific file or line](identifiers.md).
