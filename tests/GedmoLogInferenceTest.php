<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableEditor;
use Janalis\Doctrineviz\ColumnDefault;
use Janalis\Doctrineviz\SchemaDotRenderer;
use Janalis\Doctrineviz\SchemaLink;
use Janalis\Doctrineviz\SchemaLinks;
use Janalis\Doctrineviz\Tests\Fixtures\Schemas;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaDotRenderer::class)]
#[CoversClass(SchemaLinks::class)]
#[UsesClass(SchemaLink::class)]
#[UsesClass(ColumnDefault::class)]
final class GedmoLogInferenceTest extends TestCase
{
    public function testGedmoLogInferenceIsOptInAndIndependent(): void
    {
        $schema = Schemas::logs();
        $renderer = new SchemaDotRenderer();
        self::assertStringNotContainsString(' -> ', $renderer->render($schema));
        self::assertStringNotContainsString(' -> ', $renderer->render($schema, true));
        self::assertSame($renderer->render($schema, true), $renderer->render($schema, true, false));
        $dot = $renderer->render($schema, false, true);
        self::assertSame(1, substr_count($dot, ' -> '));
        self::assertStringContainsString('  t1:c1:w -> t0:c0:w;', $dot);
    }

    public function testLogInferenceDoesNotLinkTranslationTables(): void
    {
        $renderer = new SchemaDotRenderer();
        self::assertSame(
            $renderer->render(Schemas::translations()),
            $renderer->render(Schemas::translations(), false, true),
        );
    }

    public function testBothInferencesTogether(): void
    {
        $translation = Table::editor()
            ->setUnquotedName('article_translation')
            ->setColumns(Column::editor()->setUnquotedName('foreign_key')->setTypeName('integer')->create())
            ->create();
        $schema = Schemas::logs()->edit()->addTable($translation)->create();
        $dot = new SchemaDotRenderer()->render($schema, true, true);
        self::assertSame(2, substr_count($dot, ' -> '));
        self::assertStringContainsString("  t1:c1:w -> t0:c0:w;\n  t2:c0:w -> t0:c0:w;", $dot);
    }

    public function testLogInferenceRequiresTheSuffixAndBothColumnsAndTheBaseTable(): void
    {
        $renderer = new SchemaDotRenderer();
        foreach (['object_id', 'object_class'] as $column) {
            $schema = Schemas::logs()
                ->edit()
                ->modifyTableByUnquotedName('article_log', static function (TableEditor $table) use ($column): void {
                    $table->dropColumnByUnquotedName($column);
                })
                ->create();
            self::assertStringNotContainsString(' -> ', $renderer->render($schema, false, true), $column);
        }
        $schema = Schemas::logs()
            ->edit()
            ->modifyTableByUnquotedName('article', static function (TableEditor $table): void {
                $table
                    ->addColumn(Column::editor()->setUnquotedName('other')->setTypeName('integer')->create())
                    ->dropColumnByUnquotedName('id');
            })
            ->create();
        self::assertStringNotContainsString(' -> ', $renderer->render($schema, false, true));
        $schema = Schemas::logs()->edit()->dropTableByUnquotedName('article')->create();
        self::assertStringNotContainsString(' -> ', $renderer->render($schema, false, true));
        $schema = Schemas::logs()->edit()->renameTableByUnquotedName('article_log', 'article_logs')->create();
        self::assertStringNotContainsString(' -> ', $renderer->render($schema, false, true));
    }

    public function testDeclaredForeignKeysTakePrecedenceOverLogInference(): void
    {
        $schema = Schemas::logs()
            ->edit()
            ->modifyTableByUnquotedName('article_log', static function (TableEditor $table): void {
                $table->addForeignKeyConstraint(
                    ForeignKeyConstraint::editor()
                        ->setUnquotedReferencedTableName('article')
                        ->setUnquotedReferencingColumnNames('id')
                        ->setUnquotedReferencedColumnNames('id')
                        ->create(),
                );
            })
            ->create();
        $dot = new SchemaDotRenderer()->render($schema, false, true);
        self::assertSame(1, substr_count($dot, ' -> '));
        self::assertStringContainsString('  t1:c0:w -> t0:c0:w;', $dot);
    }
}
