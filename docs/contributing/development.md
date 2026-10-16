# Development

## Setup

Use PHP 8.4+, Composer 2, Graphviz, Node.js 22+ and npm. SQLite support is needed
for tests. Tests run on disposable in-memory schemas; no application database is required.

```sh
composer install
composer verify
npm ci --prefix docs
npm run docs:dev --prefix docs
```

## Quality checks

```sh
composer format         # apply Mago formatting
composer format:check   # check formatting without changes
composer lint           # Mago lint rules
composer analyze        # Mago type analysis, without a baseline
composer test           # PHPUnit unit and integration tests
composer audit
```

Mago's configuration sets the PHP target and code style. Heuristic complexity
metrics are configured separately from correctness checks.

## Coverage and mutation testing

Enable PCOV or Xdebug before collecting coverage:

```sh
php -d pcov.enabled=1 vendor/bin/phpunit --coverage-text
composer test:coverage
composer infection
```

Both overall MSI and covered MSI must be **100%**. Strengthen tests for escaped
mutations. Any proven equivalent mutation needs a narrowly scoped exclusion and
a written explanation; do not lower the thresholds or exclude entire classes.

Tests cover the reusable renderer, escaping, composite keys, command options,
sanitized failures, bundle service registration and a minimal Symfony consumer
without ORM. Graphviz validates the resulting DOT. CI also introspects a
disposable MariaDB schema; it never uses an application database.

The release helper has standard-library Python tests:

```sh
python3 -m unittest discover -s tests -p '*_test.py'
```

## Documentation

VitePress builds the Markdown in `docs/`. Search runs locally in the browser;
there are no analytics integrations. Dead internal links fail the build.

```sh
npm ci --prefix docs
npm run docs:build --prefix docs
npm run docs:preview --prefix docs
```

## Before submitting

Add focused regression tests, update relevant documentation and record user-visible
changes in `CHANGELOG.md` under `[Unreleased]`. Use the pull request template.
All fixtures and illustrations must use synthetic data.
