<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

/** The foreign keys between a schema's tables, and the Gedmo links that can be inferred without them. */
final class SchemaLinks
{
    /** @return list<SchemaLink> each table's foreign keys, then its inferred translation and log links */
    public function find(Schema $schema, bool $inferGedmoTranslations, bool $inferGedmoLogs): array
    {
        $links = [];
        foreach ($schema->getTables() as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                $targetName = $foreignKey->getReferencedTableName()->toString();
                if (!$schema->hasTable($targetName)) {
                    continue;
                }
                $referenced = $foreignKey->getReferencedColumnNames();
                $columns = [];
                foreach ($foreignKey->getReferencingColumnNames() as $index => $local) {
                    $columns[] = [$local->toString(), $referenced[$index]->toString()];
                }
                $links[] = new SchemaLink($table, $schema->getTable($targetName), $columns, inferred: false);
            }
            if ($inferGedmoTranslations) {
                $links[] = $this->inferred($schema, $table, '_translation', ['foreign_key']);
            }
            if ($inferGedmoLogs) {
                $links[] = $this->inferred($schema, $table, '_log', ['object_id', 'object_class']);
            }
        }

        return array_values(array_filter($links));
    }

    /**
     * Links `<base><suffix>.<first column>` to `<base>.id` for a table without declared foreign keys.
     *
     * @param non-empty-list<string> $columns
     */
    private function inferred(Schema $schema, Table $table, string $suffix, array $columns): ?SchemaLink
    {
        $name = $table->getObjectName()->toString();
        if ($table->getForeignKeys() !== [] || !str_ends_with($name, $suffix)) {
            return null;
        }
        foreach ($columns as $column) {
            if (!$table->hasColumn($column)) {
                return null;
            }
        }
        $base = substr($name, 0, -strlen($suffix));
        if (!$schema->hasTable($base) || !$schema->getTable($base)->hasColumn('id')) {
            return null;
        }

        return new SchemaLink($table, $schema->getTable($base), [[$columns[0], 'id']], inferred: true);
    }
}
