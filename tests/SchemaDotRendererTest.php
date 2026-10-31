<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableEditor;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Janalis\Doctrineviz\ColumnDefault;
use Janalis\Doctrineviz\SchemaDotRenderer;
use Janalis\Doctrineviz\SchemaLink;
use Janalis\Doctrineviz\SchemaLinks;
use Janalis\Doctrineviz\Tests\Fixtures\Schemas;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(SchemaDotRenderer::class)]
#[CoversClass(SchemaLinks::class)]
#[CoversClass(SchemaLink::class)]
#[CoversClass(ColumnDefault::class)]
final class SchemaDotRendererTest extends TestCase
{
    public function testEmptySchema(): void
    {
        self::assertSame(
            "digraph database {\n  graph [rankdir=LR];\n  node [shape=plain];\n}",
            new SchemaDotRenderer()->render(new Schema()),
        );
    }

    public function testCompleteSchemaMatchesSnapshotAndRendersWithGraphviz(): void
    {
        $dot = new SchemaDotRenderer()->render(Schemas::example());
        self::assertSame(file_get_contents(__DIR__ . '/Fixtures/schema.dot'), $dot);
        $process = new Process(['dot', '-Tsvg']);
        $process->setInput($dot);
        $process->mustRun();
        self::assertSame('', $process->getErrorOutput());
        self::assertStringContainsString('<svg', $process->getOutput());
    }

    public function testUnusualIdentifiersCustomTypesAndDefaultsAreSafe(): void
    {
        Type::addType('custom_text', StringType::class);
        $column = Column::editor()
            ->setQuotedName('select : "value" <&>')
            ->setTypeName('custom_text')
            ->setLength(0)
            ->setDefaultValue("O'Reilly")
            ->create();
        $key = PrimaryKeyConstraint::editor()->setQuotedColumnNames('select : "value" <&>')->create();
        $foreignKey = ForeignKeyConstraint::editor()
            ->setQuotedReferencedTableName('odd <table> & "name"')
            ->setQuotedReferencingColumnNames('select : "value" <&>')
            ->setQuotedReferencedColumnNames('select : "value" <&>')
            ->create();
        $table = Table::editor()
            ->setQuotedName('odd <table> & "name"')
            ->setColumns($column)
            ->setPrimaryKeyConstraint($key)
            ->addForeignKeyConstraint($foreignKey)
            ->create();
        $schema = new Schema([$table]);
        $dot = new SchemaDotRenderer()->render($schema);
        self::assertStringContainsString('custom_text(0)', $dot);
        self::assertStringContainsString('O&apos;Reilly', $dot);
        self::assertStringContainsString('&lt;table&gt; &amp; &quot;&quot;name&quot;&quot;', $dot);
        self::assertStringContainsString('select : &quot;&quot;value&quot;&quot; &lt;&amp;&gt;', $dot);
        self::assertStringContainsString('t0:c0:w -> t0:c0:w;', $dot);
        $process = new Process(['dot', '-Tsvg']);
        $process->setInput($dot);
        $process->mustRun();
        self::assertSame('', $process->getErrorOutput());
    }

    public function testInvalidUtf8AndXmlControlCharactersAreReplaced(): void
    {
        $table = Table::editor()
            ->setUnquotedName('example')
            ->setColumns(
                Column::editor()
                    ->setUnquotedName('label')
                    ->setTypeName('string')
                    ->setDefaultValue("\x00\x01\xFF<&")
                    ->create(),
            )
            ->create();
        $dot = new SchemaDotRenderer()->render(new Schema([$table]));
        self::assertStringContainsString('���&lt;&amp;', $dot);
        $process = new Process(['dot', '-Tsvg']);
        $process->setInput($dot);
        $process->mustRun();
        self::assertSame('', $process->getErrorOutput());
    }

    public function testExternalForeignKeyDoesNotHideLaterKnownRelationships(): void
    {
        $table = Table::editor()
            ->setUnquotedName('child')
            ->setColumns(
                Column::editor()->setUnquotedName('parent_id')->setTypeName('integer')->create(),
                Column::editor()->setUnquotedName('id')->setTypeName('integer')->create(),
            )
            ->addForeignKeyConstraint(
                ForeignKeyConstraint::editor()
                    ->setUnquotedName('fk_external')
                    ->setUnquotedReferencedTableName('external_parent')
                    ->setUnquotedReferencingColumnNames('parent_id')
                    ->setUnquotedReferencedColumnNames('id')
                    ->create(),
            )
            ->addForeignKeyConstraint(
                ForeignKeyConstraint::editor()
                    ->setUnquotedName('fk_known')
                    ->setUnquotedReferencedTableName('child')
                    ->setUnquotedReferencingColumnNames('parent_id')
                    ->setUnquotedReferencedColumnNames('id')
                    ->create(),
            )
            ->create();
        $dot = new SchemaDotRenderer()->render(new Schema([$table]));
        self::assertSame(1, substr_count($dot, ' -> '));
        self::assertStringContainsString('t0:c0:w -> t0:c1:w;', $dot);
        self::assertStringNotContainsString('external_parent', $dot);
    }

    public function testGedmoInferenceIsOptIn(): void
    {
        $schema = Schemas::translations();
        $renderer = new SchemaDotRenderer();
        self::assertStringNotContainsString(' -> ', $renderer->render($schema));
        self::assertSame($renderer->render($schema, false), $renderer->render($schema));
        self::assertStringContainsString('  t1:c0:w -> t0:c0:w;', $renderer->render($schema, true));
    }

    public function testInferenceRequiresTheSuffixAndBothColumnsAndTheBaseTable(): void
    {
        $renderer = new SchemaDotRenderer();
        $schema = Schemas::translations();
        $schema = $schema
            ->edit()
            ->modifyTableByUnquotedName('article_translation', static function (TableEditor $table): void {
                $table->dropColumnByUnquotedName('foreign_key');
            })
            ->create();
        self::assertStringNotContainsString(' -> ', $renderer->render($schema, true));
        $schema = Schemas::translations();
        $schema = $schema
            ->edit()
            ->modifyTableByUnquotedName('article', static function (TableEditor $table): void {
                $table
                    ->addColumn(Column::editor()->setUnquotedName('other')->setTypeName('integer')->create())
                    ->dropColumnByUnquotedName('id');
            })
            ->create();
        self::assertStringNotContainsString(' -> ', $renderer->render($schema, true));
        $schema = Schemas::translations();
        $schema = $schema->edit()->dropTableByUnquotedName('article')->create();
        self::assertStringNotContainsString(' -> ', $renderer->render($schema, true));
        $base = Table::editor()
            ->setUnquotedName('document')
            ->setColumns(Column::editor()->setUnquotedName('id')->setTypeName('integer')->create())
            ->create();
        $other = Table::editor()
            ->setUnquotedName('document_translation_extra')
            ->setColumns(Column::editor()->setUnquotedName('foreign_key')->setTypeName('integer')->create())
            ->create();
        $schema = new Schema([$base, $other]);
        self::assertStringNotContainsString(' -> ', $renderer->render($schema, true));
    }

    public function testDeclaredForeignKeysTakePrecedenceOverInference(): void
    {
        $schema = Schemas::translations();
        $schema = $schema
            ->edit()
            ->modifyTableByUnquotedName('article', static function (TableEditor $table): void {
                $table->addColumn(Column::editor()->setUnquotedName('other')->setTypeName('integer')->create());
            })
            ->modifyTableByUnquotedName('article_translation', static function (TableEditor $table): void {
                $table->addForeignKeyConstraint(
                    ForeignKeyConstraint::editor()
                        ->setUnquotedReferencedTableName('article')
                        ->setUnquotedReferencingColumnNames('foreign_key')
                        ->setUnquotedReferencedColumnNames('other')
                        ->create(),
                );
            })
            ->create();
        $dot = new SchemaDotRenderer()->render($schema, true);
        self::assertSame(1, substr_count($dot, ' -> '));
        self::assertStringContainsString('  t1:c0:w -> t0:c1:w;', $dot);
    }
}
