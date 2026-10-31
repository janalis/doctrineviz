# Migration

## From 0.4.x

The next release requires PHP 8.5, as [viz](https://janalis.github.io/viz/) does; Symfony
7.4 and 8.x remain supported. The command's output is unchanged unless you pass the new
`--format`, `--output` or `--engine` options. A PHP integration that constructs
`DoctrinevizCommand` itself passes a `SchemaExporter` (or nothing) as its second argument
instead of a `SchemaDotRenderer`.

## From 0.3.x


Version 0.4.0 is a breaking overhaul. It requires PHP 8.4+, Symfony 7.4/8.x,
DoctrineBundle 3.3+ and DBAL 4.5+. The Composer package and bundle class remain
`janalis/doctrineviz` and `Janalis\Doctrineviz\DoctrinevizBundle`.

### Replace the command

Use `doctrine:schema:graphviz`. It introspects the live database rather than
reading ORM entity mappings. Ensure the database schema is available and current.
Tables outside your ORM mappings can appear in the result.

### Render with Graphviz

In 0.4 the command emits DOT only. The old `--binary`, `--format` and `--output-path` options
are removed (since 0.5, `--format` and `--output` are back with a new meaning). Redirect stdout and run Graphviz separately:

```sh
php bin/console doctrine:schema:graphviz > schema.dot
dot -Tpng schema.dot -o schema.png
```

The old entity `--pattern` filter is removed. Host-application DBAL schema filters
can limit introspection. Use `--connection` to select a named connection.

### Update integrations

The legacy `Janalis\Doctrineviz\Graphviz` classes are removed. PHP integrations
use [SchemaDotRenderer](./php-api). Relationship labels now represent database
foreign-key columns rather than inferred ORM association multiplicities.

Gedmo translation inference is optional; enable `--infer-gedmo-translations`
only when you use that convention.
