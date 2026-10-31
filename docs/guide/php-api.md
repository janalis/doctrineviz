# PHP API

The renderer can run without a Symfony kernel:

```php
use Janalis\Doctrineviz\SchemaDotRenderer;

// $connection is an existing Doctrine DBAL Connection.
$schema = $connection->createSchemaManager()->introspectSchema();
$renderer = new SchemaDotRenderer();
$dot = $renderer->render($schema);
file_put_contents('schema.dot', $dot);
```

`SchemaDotRenderer::render(Schema $schema, bool $inferGedmoTranslations = false, bool $inferGedmoLogs = false): string`
returns a complete DOT graph with no trailing newline. It does not connect to a
database, launch Graphviz or write files. The console command adds a trailing newline.

Pass `true` as the second or third argument to opt in to translation or log inference:

```php
$dot = $renderer->render($schema, inferGedmoTranslations: true, inferGedmoLogs: true);
```

## Mermaid, PlantUML and images

`SchemaExporter` writes any of the three languages; `SchemaEntityDiagram` builds the
viz entity model that Mermaid and PlantUML are written from:

```php
use Janalis\Doctrineviz\SchemaExporter;
use Janalis\Viz\Source\SourceFormat;
use Janalis\Viz\Viz;

$exporter = new SchemaExporter();
$mermaid = $exporter->source($schema, SourceFormat::Mermaid, inferGedmoTranslations: false, inferGedmoLogs: false);
$plantuml = $exporter->source($schema, SourceFormat::PlantUml, false, false);

// Render it with viz: natively, with the command-line tool as the fallback.
new Viz()->export($mermaid, SourceFormat::Mermaid, 'schema.svg');
```

`SchemaExporter::source(Schema $schema, SourceFormat $format, bool $inferGedmoTranslations, bool $inferGedmoLogs): string`
returns the source with no trailing newline; `SourceFormat::Dot` returns what
`SchemaDotRenderer` renders.

Register custom types with DBAL before creating or introspecting the schema.
The diagram uses their registered DBAL type names. Exceptions from the renderer
propagate to PHP callers; the console command catches and sanitizes failures.
