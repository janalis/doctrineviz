# Contributing

See the [development guide](https://janalis.github.io/doctrineviz/contributing/development)
for setup, testing and quality checks, and the
[release guide](https://janalis.github.io/doctrineviz/contributing/releases)
for publishing.

Run `make install` to set up dependencies, `make verify` for the PHP and workflow
quality gates, and `make docs` to build the documentation. Use `make help` for
individual checks. Coverage requires PCOV or Xdebug; workflow checks require
actionlint and ShellCheck.

Keep changes focused, add regression tests, and record user-visible changes
under `[Unreleased]` in `CHANGELOG.md`. Both Infection MSI thresholds are 100%.

Use synthetic schemas and examples. Do not include credentials, private URLs,
local machine paths, database dumps or personal data in issues, fixtures or logs.
