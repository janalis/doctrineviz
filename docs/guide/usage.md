# Command reference

## Generate a diagram

```sh
php bin/console doctrine:schema:graphviz > schema.dot
dot -Tsvg schema.dot -o schema.svg
```

DOT is written to stdout as plain text, without a success banner or console
formatting. The command returns `0` on success and `1` if inspection or rendering
fails. Failures use a sanitized error message; underlying exceptions and
connection parameters are not printed, even with verbose output.

The schema comes from the live database, not entity mappings. Tables and columns
follow DBAL schema order. DBAL schema filters configured by the host application
apply to introspection.

## Options

| Option | Default | Behavior |
| --- | --- | --- |
| `--connection=NAME` | Default Doctrine connection | Inspect a named DBAL connection. |
| `--infer-gedmo-translations` | Off | Infer eligible translation links without database foreign keys. |

```sh
php bin/console doctrine:schema:graphviz --connection=reporting > reporting.dot
php bin/console doctrine:schema:graphviz --infer-gedmo-translations > schema.dot
```

## Reading the diagram

Each row contains the column name, DBAL type and optional length, nullability,
and default. Primary-key column names are **bold**; other foreign-key column
names are *italic*. Auto-increment columns show `auto_increment`.

Foreign-key edges pair local and referenced columns in constraint order,
including composite keys. References to tables outside the introspected schema
are omitted. Generated node and port identifiers keep SQL names out of DOT
syntax; label content is escaped for Graphviz HTML labels.

The optional Gedmo convention links a `<base>_translation.foreign_key` column to
`<base>.id` only if both tables and columns exist and the translation table has
no declared foreign keys. Actual database foreign keys always take precedence.

## Render other formats

```sh
dot -Tpng schema.dot -o schema.png
dot -Tpdf schema.dot -o schema.pdf
```

Diagram files contain table names, column names and schema defaults. Review them
before sharing. Keep private diagrams out of public repositories and issue reports.

## Troubleshooting

If inspection fails, check the selected connection, driver extension, database
availability, metadata permissions and registered custom DBAL types in the host
application. Use a synthetic reproduction when reporting a problem.
