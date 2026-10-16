# Changelog

User-visible changes are recorded here. Versions follow semantic versioning;
0.4.0 introduces breaking changes while the project remains pre-1.0.

## [Unreleased]

### Fixed

- Use the version tag alone as the GitHub release title.

## [0.4.1] - 2026-10-16

### Added

- Makefile developer commands for setup, quality gates, tests, coverage, mutation testing and documentation, shared with GitHub Actions.

### Changed

- Update pinned GitHub Actions for Node.js setup, artifact transfers and GitHub Pages.

### Fixed

- Remove the artificial `book.id` self-reference from the documentation example diagram.
- Retire obsolete Scrutinizer, StyleCI and Travis hooks so legacy checks no longer report failures alongside GitHub Actions.
- Explicitly run CI on generated release commits and require success before publishing the GitHub release.
- Enable ShellCheck validation for workflow shell scripts.

## [0.4.0] - 2026-10-16

### Changed

- Replace ORM mapping visualization with live Doctrine DBAL schema introspection and Graphviz DOT output.
- Require PHP 8.4+, Symfony 7.4/8.x, DoctrineBundle 3.3+ and DBAL 4.5+.
- Remove the legacy command, renderer API and image-output options; document migration.

### Added

- Reusable schema renderer, named connections and opt-in Gedmo translation inference.
- Safe labels, composite foreign-key pairing and complete scalar defaults.
- PHPUnit regression and integration coverage, Mago checks and Infection with 100% MSI thresholds.
- GitHub Actions CI, dry-run releases, Packagist verification and dependency updates.
- VitePress documentation on GitHub Pages, synthetic diagrams and contributor templates.

## [0.3.1] - 2017-11-30

- Correct command argument types.

## [0.3.0] - 2017-11-30

- Require PHP 7.1 and update Symfony and Doctrine dependencies.

## [0.2.0] - 2017-03-31

- Improve renderer code style and regression coverage.

## [0.1.2] - 2017-03-20

- Improve relationship cardinality labels.

## [0.1.1] - 2017-03-20

- Fix duplicate column names in diagrams.

## [0.1.0] - 2017-03-20

- Repair continuous integration and update the package.

## [0.0.6] - 2017-01-27

- Update project badges.

## [0.0.5] - 2017-01-27

- Fix renderer source code.

## [0.0.4] - 2017-01-25

- Improve DOT output handling.

## [0.0.3] - 2017-01-24

- Add output format, Graphviz binary and output path options.

## [0.0.2] - 2017-01-23

- Fix graph edges.

## [0.0.1] - 2017-01-21

- Initial Doctrine mapping visualization bundle.
