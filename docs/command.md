# CLI options

## Reporter

The `--report` option allows to choose the output format for the linter report.

Supported formats are:
- `text` selected by default.
- `checkstyle` following the common checkstyle XML schema.
- `github` if you want annotations on GitHub actions.
- `junit` following JUnit schema XML from Jenkins.
- `null` if you don't want any reporting.
