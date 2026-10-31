# Command reference

## Generate a diagram

```sh
php bin/console doctrine:schema:graphviz > schema.dot
php bin/console doctrine:schema:graphviz --output=schema.svg
```

Without `--output`, the diagram source is written to stdout as plain text, without
a success banner or console formatting. With it, the file is written and nothing is
printed. The command returns `0` on success, `1` if inspection or writing fails, and
`2` for an unknown `--format` or `--engine`. Inspection failures use a sanitized
error message; underlying exceptions and connection parameters are not printed,
even with verbose output.

The schema comes from the live database, not entity mappings. Tables and columns
follow DBAL schema order. DBAL schema filters configured by the host application
apply to introspection.

## Options

| Option | Default | Behavior |
| --- | --- | --- |
| `--connection=NAME` | Default Doctrine connection | Inspect a named DBAL connection. |
| `--infer-gedmo-translations` | Off | Infer eligible translation links without database foreign keys. |
| `--infer-gedmo-logs` | Off | Infer eligible log links without database foreign keys. |
| `--format=FORMAT` | From the `--output` extension, else `dot` | `dot`, `mermaid` or `plantuml`. |
| `--output=PATH`, `-o` | stdout | Write to a file. `.svg`, `.png` and `.pdf` render an image; any other extension writes the source. |
| `--engine=ENGINE` | `auto` | How images render: `auto` (natively, then the command-line tool), `native` or `cli`. |

```sh
php bin/console doctrine:schema:graphviz --connection=reporting > reporting.dot
php bin/console doctrine:schema:graphviz --infer-gedmo-translations > schema.dot
php bin/console doctrine:schema:graphviz --infer-gedmo-translations --infer-gedmo-logs > schema.dot
```

## Reading the diagram

Each row contains the column name, DBAL type and optional length, nullability,
and default. Primary-key column names are **bold**; other foreign-key column
names are *italic*. Auto-increment columns show `auto_increment`.

Foreign-key edges pair local and referenced columns in constraint order,
including composite keys. References to tables outside the introspected schema
are omitted. Generated node and port identifiers keep SQL names out of DOT
syntax; label content is escaped for Graphviz HTML labels.

The optional Gedmo conventions link a column to `<base>.id` only if both tables
and the named columns exist and the inferred table has no declared foreign keys:

- translations: `<base>_translation.foreign_key`;
- logs: `<base>_log.object_id`, when the table also has an `object_class` column.

Actual database foreign keys always take precedence. Tables whose name does not
match the base table, such as the shared `ext_translations` and
`ext_log_entries` tables, are not linked.

## Mermaid and PlantUML

`--format=mermaid` writes a Mermaid `erDiagram`, and `--format=plantuml` a PlantUML
entity diagram, laid out left to right. Each column lists its type and length, its
keys (`PK`, `FK`, and `UK` for a column unique on its own) and a comment with its
nullability and default. A foreign key is a relationship from the referencing table:

- one to one (`|o`) when its columns are the primary key or a unique index, else many (`}o`);
- to exactly one (`||`) when none of its columns is nullable, else to zero or one (`o|`);
- solid when its columns are part of the primary key (identifying), dashed otherwise.

Inferred Gedmo links are dashed, from many to zero or one. A `.mmd` or `.mermaid`
output picks Mermaid, and a `.puml`, `.plantuml` or `.pu` output PlantUML.

```sh
php bin/console doctrine:schema:graphviz --format=mermaid > schema.mmd
php bin/console doctrine:schema:graphviz --output=schema.puml
```

## Images

`--output` with an `.svg`, `.png` or `.pdf` extension renders the diagram, in the
chosen format, with [viz](https://janalis.github.io/viz/). By default it is drawn
natively in PHP, with no Graphviz, Node or Java needed, and falls back to the
language's command-line tool (`dot`, `mmdc` or `plantuml`) for what the native
renderer does not draw. `--engine=native` uses the native renderer alone, and
`--engine=cli` the tool alone.

```sh
php bin/console doctrine:schema:graphviz --output=schema.svg
php bin/console doctrine:schema:graphviz --format=mermaid --output=schema.png
php bin/console doctrine:schema:graphviz --output=schema.pdf --engine=cli
```

The file is written once the image has rendered in full. A rendering failure
prints `Unable to write PATH:` and the reason.

Diagram files contain table names, column names and schema defaults. Review them
before sharing. Keep private diagrams out of public repositories and issue reports.

## Troubleshooting

If inspection fails, check the selected connection, driver extension, database
availability, metadata permissions and registered custom DBAL types in the host
application. Use a synthetic reproduction when reporting a problem.
