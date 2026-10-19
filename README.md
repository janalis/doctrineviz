<p align="center">
  <img src="docs/public/logo.svg" alt="doctrineviz" width="160">
</p>

<h1 align="center">doctrineviz</h1>

<p align="center">
  <strong>Your database schema, drawn.</strong><br>
  Doctrine DBAL · Graphviz DOT · Symfony bundle
</p>

<p align="center">
  <a href="https://github.com/janalis/doctrineviz/actions/workflows/ci.yml"><img src="https://github.com/janalis/doctrineviz/actions/workflows/ci.yml/badge.svg" alt="CI"></a>
  <a href="https://janalis.github.io/doctrineviz/"><img src="https://github.com/janalis/doctrineviz/actions/workflows/docs.yml/badge.svg" alt="Docs"></a>
  <a href="https://github.com/janalis/doctrineviz/releases"><img src="https://img.shields.io/github/v/release/janalis/doctrineviz?sort=semver" alt="Latest release"></a>
  <a href="https://packagist.org/packages/janalis/doctrineviz"><img src="https://img.shields.io/packagist/v/janalis/doctrineviz" alt="Packagist"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/janalis/doctrineviz" alt="BSD-3-Clause license"></a>
</p>

<p align="center">
  <a href="https://janalis.github.io/doctrineviz/"><strong>Documentation</strong></a> ·
  <a href="https://janalis.github.io/doctrineviz/guide/installation">Installation</a> ·
  <a href="https://janalis.github.io/doctrineviz/guide/usage">Command reference</a> ·
  <a href="https://janalis.github.io/doctrineviz/guide/migration">Migration</a>
</p>

---

doctrineviz reads your database schema through Doctrine DBAL and generates a
Graphviz DOT diagram with tables, columns, primary keys and foreign keys.
Run it in a Symfony application, or use the renderer directly with a DBAL
schema. Doctrine ORM is optional.

## Install

```sh
composer require --dev janalis/doctrineviz
```

Register `Janalis\Doctrineviz\DoctrinevizBundle::class` for the `dev` and
`test` environments in `config/bundles.php`. Requires PHP 8.4+, Symfony
7.4 or 8.x, DoctrineBundle 3.3+, and DBAL 4.5+.
See [Installation](https://janalis.github.io/doctrineviz/guide/installation).

## Quick start

```sh
php bin/console doctrine:schema:graphviz > schema.dot
# Install Graphviz separately to render the DOT file.
dot -Tsvg schema.dot -o schema.svg
dot -Tpdf schema.dot -o schema.pdf
```

![Example database diagram](docs/public/example.svg)

## Highlights

- **Database schema diagrams** show the actual schema, including tables not mapped by ORM.
- **Column details** include DBAL type, length, nullability, defaults and auto-increment.
- **Relationships** preserve composite foreign-key column pairs and self-references.
- **Named connections** through `--connection=reporting`.
- **Optional Gedmo links** through `--infer-gedmo-translations` and `--infer-gedmo-logs`.
- **Reusable renderer** accepts a DBAL `Schema` without booting Symfony.

## Contributing

Read [CONTRIBUTING.md](CONTRIBUTING.md) and the
[development guide](https://janalis.github.io/doctrineviz/contributing/development).
Use synthetic schemas in reports and tests; redact connection details and private data.

```sh
make install
make verify    # formatting, lint, analysis, workflows, tests, 100% coverage and MSI
make docs      # build the VitePress documentation
make help      # list all developer commands
```

## License

[BSD-3-Clause](LICENSE).
