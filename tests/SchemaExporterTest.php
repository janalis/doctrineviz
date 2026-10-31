<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests;

use Janalis\Doctrineviz\ColumnDefault;
use Janalis\Doctrineviz\SchemaDotRenderer;
use Janalis\Doctrineviz\SchemaEntityDiagram;
use Janalis\Doctrineviz\SchemaExporter;
use Janalis\Doctrineviz\SchemaLink;
use Janalis\Doctrineviz\SchemaLinks;
use Janalis\Doctrineviz\Tests\Fixtures\Schemas;
use Janalis\Viz\Render\Engine;
use Janalis\Viz\Render\Format;
use Janalis\Viz\Source\SourceFormat;
use Janalis\Viz\Viz;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaExporter::class)]
#[UsesClass(SchemaDotRenderer::class)]
#[UsesClass(SchemaEntityDiagram::class)]
#[UsesClass(SchemaLinks::class)]
#[UsesClass(SchemaLink::class)]
#[UsesClass(ColumnDefault::class)]
final class SchemaExporterTest extends TestCase
{
    /** @return iterable<string, array{SourceFormat, string}> */
    public static function formats(): iterable
    {
        yield 'dot' => [SourceFormat::Dot, 'schema.dot'];
        yield 'mermaid' => [SourceFormat::Mermaid, 'schema.mmd'];
        yield 'plantuml' => [SourceFormat::PlantUml, 'schema.puml'];
    }

    #[DataProvider('formats')]
    public function testEachFormatMatchesItsSnapshotAndRendersNatively(SourceFormat $format, string $snapshot): void
    {
        $source = new SchemaExporter()->source(Schemas::example(), $format, false, false);

        self::assertSame(file_get_contents(__DIR__ . '/Fixtures/' . $snapshot), $source);
        self::assertStringContainsString(
            '<svg',
            new Viz(engine: Engine::Native)->render($source, $format, Format::Svg)->bytes,
        );
    }

    public function testDotIsTheDefaultAndInferenceReachesEveryFormat(): void
    {
        $exporter = new SchemaExporter();
        self::assertSame(
            new SchemaDotRenderer()->render(Schemas::example()),
            $exporter->source(Schemas::example(), SourceFormat::Dot, false, false),
        );
        self::assertStringContainsString(' -> ', $exporter->source(
            Schemas::translations(),
            SourceFormat::Dot,
            true,
            false,
        ));
        self::assertStringContainsString(' -> ', $exporter->source(Schemas::logs(), SourceFormat::Dot, false, true));
        self::assertStringContainsString('article_translation }o..o| article', $exporter->source(
            Schemas::translations(),
            SourceFormat::Mermaid,
            true,
            false,
        ));
        self::assertStringContainsString('article_log }o..o| article', $exporter->source(
            Schemas::logs(),
            SourceFormat::Mermaid,
            false,
            true,
        ));
    }
}
