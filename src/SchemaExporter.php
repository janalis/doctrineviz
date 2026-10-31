<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz;

use Doctrine\DBAL\Schema\Schema;
use Janalis\Viz\Source\Converter;
use Janalis\Viz\Source\SourceFormat;

/** A schema's diagram source, in Graphviz DOT, Mermaid or PlantUML. */
final class SchemaExporter
{
    public function __construct(
        private readonly SchemaDotRenderer $dot = new SchemaDotRenderer(),
        private readonly SchemaEntityDiagram $entities = new SchemaEntityDiagram(),
        private readonly Converter $converter = new Converter(),
    ) {}

    public function source(
        Schema $schema,
        SourceFormat $format,
        bool $inferGedmoTranslations,
        bool $inferGedmoLogs,
    ): string {
        if ($format === SourceFormat::Dot) {
            return $this->dot->render($schema, $inferGedmoTranslations, $inferGedmoLogs);
        }

        return rtrim($this->converter->write(
            $this->entities->build($schema, $inferGedmoTranslations, $inferGedmoLogs),
            $format,
        ));
    }
}
