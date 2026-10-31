<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Index\IndexedColumn;
use Doctrine\DBAL\Schema\Index\IndexType;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Janalis\Viz\Model\Direction;
use Janalis\Viz\Model\Entity\Attribute;
use Janalis\Viz\Model\Entity\Cardinality;
use Janalis\Viz\Model\Entity\Entity;
use Janalis\Viz\Model\Entity\EntityDiagram;
use Janalis\Viz\Model\Entity\Key;
use Janalis\Viz\Model\Entity\Relationship;

/** A schema as an entity-relationship diagram, which viz writes as Mermaid or PlantUML. */
final class SchemaEntityDiagram
{
    public function __construct(
        private readonly SchemaLinks $links = new SchemaLinks(),
    ) {}

    public function build(Schema $schema, bool $inferGedmoTranslations, bool $inferGedmoLogs): EntityDiagram
    {
        return new EntityDiagram(
            array_map($this->entity(...), $schema->getTables()),
            array_map($this->relationship(...), $this->links->find($schema, $inferGedmoTranslations, $inferGedmoLogs)),
            direction: Direction::LeftRight,
        );
    }

    private function entity(Table $table): Entity
    {
        $primary = self::primary($table);
        $foreign = [];
        foreach ($table->getForeignKeys() as $foreignKey) {
            foreach ($foreignKey->getReferencingColumnNames() as $name) {
                $foreign[] = $name->toString();
            }
        }
        $unique = array_merge(...array_filter(
            self::uniqueSets($table),
            static fn(array $columns): bool => count($columns) === 1,
        ));

        return new Entity($table->getObjectName()->toString(), attributes: array_map(
            static fn(Column $column): Attribute => self::attribute($column, $primary, $foreign, $unique),
            $table->getColumns(),
        ));
    }

    /**
     * @param list<string> $primary
     * @param list<string> $foreign
     * @param list<string> $unique
     */
    private static function attribute(Column $column, array $primary, array $foreign, array $unique): Attribute
    {
        $name = $column->getObjectName()->toString();
        $type = $column->getTypeName();
        $length = $column->getLength();
        if ($length !== null) {
            $type .= '(' . $length . ')';
        }
        $keys = array_keys(array_filter([
            Key::Primary->value => in_array($name, $primary, true),
            Key::Foreign->value => in_array($name, $foreign, true),
            Key::Unique->value => in_array($name, $unique, true) && !in_array($name, $primary, true),
        ]));
        $default = ColumnDefault::of($column);
        $default = $default === '' || $column->getAutoincrement() ? $default : 'default ' . $default;

        return new Attribute(
            $type,
            $name,
            array_map(Key::from(...), $keys),
            implode(', ', array_filter(
                [$column->getNotnull() ? 'NOT NULL' : 'NULL', $default],
                static fn(string $part): bool => $part !== '',
            )),
        );
    }

    /**
     * A foreign key's referencing table holds one row per referenced row when its columns are
     * unique, and must reference one when none of them is nullable; it is identifying when they
     * are part of its primary key. An inferred link holds no such guarantees.
     */
    private function relationship(SchemaLink $link): Relationship
    {
        $from = $link->from->getObjectName()->toString();
        $to = $link->to->getObjectName()->toString();
        if ($link->inferred) {
            return new Relationship($from, $to, Cardinality::ZeroOrMore, Cardinality::ZeroOrOne, false);
        }
        $columns = array_column($link->columns, 0);
        sort($columns);
        $unique = array_any(self::uniqueSets($link->from), static function (array $set) use ($columns): bool {
            sort($set);

            return $set === $columns;
        });
        $required = array_all($columns, static fn(string $column): bool => $link
            ->from
            ->getColumn($column)
            ->getNotnull());

        return new Relationship(
            $from,
            $to,
            $unique ? Cardinality::ZeroOrOne : Cardinality::ZeroOrMore,
            $required ? Cardinality::ExactlyOne : Cardinality::ZeroOrOne,
            array_diff($columns, self::primary($link->from)) === [],
        );
    }

    /** @return list<string> */
    private static function primary(Table $table): array
    {
        return array_map(
            static fn(UnqualifiedName $name): string => $name->toString(),
            $table->getPrimaryKeyConstraint()?->getColumnNames() ?? [],
        );
    }

    /** @return list<list<string>> the column sets of the table's unique indexes and constraints */
    private static function uniqueSets(Table $table): array
    {
        $sets = [];
        $unique = array_filter(
            $table->getIndexes(),
            static fn(Index $index): bool => $index->getType() === IndexType::UNIQUE,
        );
        foreach ($unique as $index) {
            $sets[] = array_map(static fn(IndexedColumn $column): string => $column
                ->getColumnName()
                ->toString(), $index->getIndexedColumns());
        }
        foreach ($table->getUniqueConstraints() as $constraint) {
            $sets[] = array_map(
                static fn(UnqualifiedName $name): string => $name->toString(),
                $constraint->getColumnNames(),
            );
        }

        return $sets;
    }
}
