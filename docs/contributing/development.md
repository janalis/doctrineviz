# Development

## Setup

Use PHP 8.4+, Composer 2, GNU Make, Python 3, Graphviz, Node.js 22+ and npm.
Install actionlint and ShellCheck for workflow checks. SQLite support is needed
for tests. Tests run on disposable in-memory schemas; no application database is required.

```sh
make install
make verify
make docs-dev
```

## Quality checks

```sh
make fmt           # apply Mago formatting
make fmt-check     # check formatting without changes
make lint          # Mago lint rules
make analyze       # Mago type analysis, without a baseline
make workflows     # actionlint and ShellCheck
make test          # PHPUnit and release-helper tests
make audit         # Composer dependency security advisories
make verify        # complete PHP and workflow quality gate
make help          # list all developer commands
```

Mago's configuration sets the PHP target and code style. Heuristic complexity
metrics are configured separately from correctness checks.

## Coverage and mutation testing

Install PCOV or Xdebug to collect coverage. The Makefile enables PCOV for coverage
commands and sets `XDEBUG_MODE=coverage` for Xdebug. Both test runs and Infection's
initial coverage process receive the same PHP flags:

```sh
make coverage
make infection
make infection INFECTION_THREADS=4
```

`make coverage` requires **100% statement coverage**. Both overall MSI and
covered MSI must be **100%**. Strengthen tests for escaped
mutations. Any proven equivalent mutation needs a narrowly scoped exclusion and
a written explanation; do not lower the thresholds or exclude entire classes.

Tests cover the reusable renderer, escaping, composite keys, command options,
sanitized failures, bundle service registration and a minimal Symfony consumer
without ORM. Graphviz validates the resulting DOT. CI also introspects a
disposable MariaDB schema; it never uses an application database.

The release helper has standard-library Python tests:

```sh
make test-release
```

## Documentation

VitePress builds the Markdown in `docs/`. Search runs locally in the browser;
there are no analytics integrations. Dead internal links fail the build.

```sh
make docs
make docs-dev
make docs-preview
```

## Tool overrides and cleanup

`COMPOSER`, `PHP`, `PYTHON`, `NPM` and `ACTIONLINT` can be overridden on the command
line. `COVERAGE_PHP_FLAGS` defaults to `-d pcov.enabled=1`; override it when your
PHP setup needs different coverage options. For example:

```sh
make workflows ACTIONLINT=/path/to/actionlint
make coverage COVERAGE_PHP_FLAGS="-d pcov.enabled=1 -d memory_limit=1G"
```

`make clean` removes generated coverage, mutation reports, PHPUnit caches and
VitePress build/cache directories. Installed dependencies are retained. Checks
run serially, including when Make is invoked with `-j`, to avoid shared-cache races.
Composer scripts remain available for individual PHP checks.

## Before submitting

Add focused regression tests, update relevant documentation and record user-visible
changes in `CHANGELOG.md` under `[Unreleased]`. Use the pull request template.
All fixtures and illustrations must use synthetic data.
