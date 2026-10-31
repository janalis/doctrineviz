<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Index\IndexType;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\UniqueConstraint;
use Janalis\Doctrineviz\ColumnDefault;
use Janalis\Doctrineviz\SchemaEntityDiagram;
use Janalis\Doctrineviz\SchemaLink;
use Janalis\Doctrineviz\SchemaLinks;
use Janalis\Doctrineviz\Tests\Fixtures\Schemas;
use Janalis\Viz\Model\Direction;
use Janalis\Viz\Model\Entity\Attribute;
use Janalis\Viz\Model\Entity\Cardinality;
use Janalis\Viz\Model\Entity\Entity;
use Janalis\Viz\Model\Entity\Key;
use Janalis\Viz\Model\Entity\Relationship;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaEntityDiagram::class)]
#[CoversClass(ColumnDefault::class)]
#[CoversClass(SchemaLinks::class)]
#[CoversClass(SchemaLink::class)]
final class SchemaEntityDiagramTest extends TestCase
{
    public function testTablesBecomeEntitiesListingTheirColumns(): void
    {
        $diagram = new SchemaEntityDiagram()->build(Schemas::example(), false, false);

        self::assertSame(Direction::LeftRight, $diagram->direction);
        self::assertSame(
            ['author', 'book'],
            array_map(static fn(Entity $entity): string => $entity->id, $diagram->entities),
        );
        self::assertNull($diagram->entities[0]->label);
        self::assertEquals(
            [
                new Attribute('integer', 'id', [Key::Primary], 'NOT NULL, auto_increment'),
                new Attribute('string(2)', 'region', [Key::Primary], 'NOT NULL'),
            ],
            $diagram->entities[0]->attributes,
        );
        self::assertEquals(
            [
                new Attribute('integer', 'id', [Key::Primary, Key::Foreign], 'NOT NULL'),
                new Attribute('integer', 'author_id', [Key::Foreign], 'NOT NULL'),
                new Attribute('string(2)', 'author_region', [Key::Foreign], 'NOT NULL'),
                new Attribute('string(120)', 'title', [], 'NULL, default <draft> & "quoted"'),
                new Attribute('integer', 'zero', [], 'NOT NULL, default 0'),
                new Attribute('string', 'zero_string', [], 'NOT NULL, default 0'),
                new Attribute('boolean', 'enabled', [], 'NOT NULL, default false'),
                new Attribute('boolean', 'published', [], 'NOT NULL, default true'),
                new Attribute('decimal', 'price', [], 'NOT NULL, default 1.25'),
            ],
            $diagram->entities[1]->attributes,
        );
    }

    public function testForeignKeysBecomeRelationshipsWithTheirCardinalities(): void
    {
        self::assertEquals(
            [
                // Not unique, not nullable, not part of the primary key.
                new Relationship('book', 'author', Cardinality::ZeroOrMore, Cardinality::ExactlyOne, false),
                // The primary key itself: one to one, and identifying.
                new Relationship('book', 'book', Cardinality::ZeroOrOne, Cardinality::ExactlyOne, true),
            ],
            new SchemaEntityDiagram()->build(Schemas::example(), false, false)->relationships,
        );
    }

    public function testUniqueColumnsAndNullableReferences(): void
    {
        $owner = Table::editor()
            ->setUnquotedName('owner')
            ->setColumns(
                Column::editor()->setUnquotedName('id')->setTypeName('integer')->create(),
                Column::editor()->setUnquotedName('code')->setTypeName('string')->create(),
            )
            ->setPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create())
            ->create();
        $pet = Table::editor()
            ->setUnquotedName('pet')
            ->setColumns(
                Column::editor()->setUnquotedName('id')->setTypeName('integer')->create(),
                Column::editor()->setUnquotedName('owner_id')->setTypeName('integer')->setNotNull(false)->create(),
                Column::editor()->setUnquotedName('owner_code')->setTypeName('string')->create(),
                Column::editor()->setUnquotedName('chip')->setTypeName('string')->create(),
                Column::editor()->setUnquotedName('tag')->setTypeName('string')->create(),
                Column::editor()->setUnquotedName('name')->setTypeName('string')->create(),
            )
            ->setPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create())
            ->setIndexes(
                Index::editor()
                    ->setUnquotedName('uniq_chip')
                    ->setType(IndexType::UNIQUE)
                    ->setUnquotedColumnNames('chip')
                    ->create(),
                Index::editor()
                    ->setUnquotedName('idx_name')
                    ->setType(IndexType::REGULAR)
                    ->setUnquotedColumnNames('name')
                    ->create(),
                Index::editor()
                    ->setUnquotedName('uniq_owner')
                    ->setType(IndexType::UNIQUE)
                    ->setUnquotedColumnNames('owner_id', 'owner_code')
                    ->create(),
            )
            ->setUniqueConstraints(
                UniqueConstraint::editor()->setUnquotedName('uniq_tag')->setUnquotedColumnNames('tag')->create(),
                UniqueConstraint::editor()->setUnquotedName('uniq_id')->setUnquotedColumnNames('id')->create(),
            )
            ->addForeignKeyConstraint(
                ForeignKeyConstraint::editor()
                    ->setUnquotedName('fk_owner')
                    ->setUnquotedReferencedTableName('owner')
                    ->setUnquotedReferencingColumnNames('owner_code', 'owner_id')
                    ->setUnquotedReferencedColumnNames('code', 'id')
                    ->create(),
            )
            ->addForeignKeyConstraint(
                ForeignKeyConstraint::editor()
                    ->setUnquotedName('fk_name')
                    ->setUnquotedReferencedTableName('owner')
                    ->setUnquotedReferencingColumnNames('name')
                    ->setUnquotedReferencedColumnNames('code')
                    ->create(),
            )
            ->create();
        // Its key's columns out of order, its index's in order: compared as sets either way.
        $collar = Table::editor()
            ->setUnquotedName('collar')
            ->setColumns(
                Column::editor()->setUnquotedName('owner_code')->setTypeName('string')->create(),
                Column::editor()->setUnquotedName('owner_id')->setTypeName('integer')->create(),
            )
            ->setIndexes(
                Index::editor()
                    ->setUnquotedName('uniq_collar')
                    ->setType(IndexType::UNIQUE)
                    ->setUnquotedColumnNames('owner_code', 'owner_id')
                    ->create(),
            )
            ->addForeignKeyConstraint(
                ForeignKeyConstraint::editor()
                    ->setUnquotedName('fk_collar')
                    ->setUnquotedReferencedTableName('owner')
                    ->setUnquotedReferencingColumnNames('owner_id', 'owner_code')
                    ->setUnquotedReferencedColumnNames('id', 'code')
                    ->create(),
            )
            ->create();
        $diagram = new SchemaEntityDiagram()->build(new Schema([$owner, $pet, $collar]), false, false);

        self::assertSame(
            [[Key::Primary], [Key::Foreign], [Key::Foreign], [Key::Unique], [Key::Unique], [Key::Foreign]],
            array_map(static fn(Attribute $attribute): array => $attribute->keys, $diagram->entities[1]->attributes),
            'a column unique alone, by index or constraint, is marked so; a primary key is not marked again',
        );
        self::assertEquals(
            [
                // Unique in that order or another, and one of them nullable.
                new Relationship('pet', 'owner', Cardinality::ZeroOrOne, Cardinality::ZeroOrOne, false),
                // An ordinary index makes no column unique.
                new Relationship('pet', 'owner', Cardinality::ZeroOrMore, Cardinality::ExactlyOne, false),
                new Relationship('collar', 'owner', Cardinality::ZeroOrOne, Cardinality::ExactlyOne, false),
            ],
            $diagram->relationships,
        );
    }

    public function testInferredLinksPromiseNothing(): void
    {
        self::assertEquals(
            [new Relationship(
                'article_translation',
                'article',
                Cardinality::ZeroOrMore,
                Cardinality::ZeroOrOne,
                false,
            )],
            new SchemaEntityDiagram()->build(Schemas::translations(), true, false)->relationships,
        );
        self::assertEquals(
            [new Relationship('article_log', 'article', Cardinality::ZeroOrMore, Cardinality::ZeroOrOne, false)],
            new SchemaEntityDiagram()->build(Schemas::logs(), false, true)->relationships,
        );
        self::assertSame([], new SchemaEntityDiagram()->build(Schemas::logs(), false, false)->relationships);
    }
}
