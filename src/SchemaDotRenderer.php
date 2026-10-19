<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

final class SchemaDotRenderer
{
    public function render(Schema $schema, bool $inferGedmoTranslations = false, bool $inferGedmoLogs = false): string
    {
        $lines = ['digraph database {', '  graph [rankdir=LR];', '  node [shape=plain];'];
        /** @var array<int, string> $nodes */
        $nodes = [];
        /** @var array<int, string> $ports */
        $ports = [];

        foreach ($schema->getTables() as $index => $table) {
            $node = 't' . $index;
            $nodes[spl_object_id($table)] = $node;
            $lines[] = '  ' . $node . ' [label=<';
            $lines[] = '    <TABLE BORDER="1" CELLBORDER="1" CELLSPACING="0">';
            $lines[] =
                '      <TR><TD BGCOLOR="lightgray" COLSPAN="4"><B>'
                . $this->escape($table->getObjectName()->toString())
                . '</B></TD></TR>';
            $primaryColumns = array_map(
                static fn(UnqualifiedName $name): string => $name->toString(),
                $table->getPrimaryKeyConstraint()?->getColumnNames() ?? [],
            );
            $foreignColumns = [];
            foreach ($table->getForeignKeys() as $foreignKey) {
                foreach ($foreignKey->getReferencingColumnNames() as $name) {
                    $foreignColumns[] = $name->toString();
                }
            }

            foreach ($table->getColumns() as $columnIndex => $column) {
                $port = 'c' . $columnIndex;
                $ports[spl_object_id($column)] = $port;
                $name = $column->getObjectName()->toString();
                $label = $this->escape($name);
                $label = match (true) {
                    in_array($name, $primaryColumns, true) => '<B>' . $label . '</B>',
                    in_array($name, $foreignColumns, true) => '<I>' . $label . '</I>',
                    default => $label,
                };
                $type = $column->getTypeName();
                $length = $column->getLength();
                if ($length !== null) {
                    $type .= '(' . $length . ')';
                }
                $lines[] = sprintf(
                    '      <TR><TD ALIGN="LEFT" PORT="%s">%s</TD><TD ALIGN="LEFT">%s</TD><TD ALIGN="LEFT">%s</TD><TD ALIGN="LEFT">%s</TD></TR>',
                    $port,
                    $label,
                    $this->escape($type),
                    $column->getNotnull() ? 'NOT NULL' : 'NULL',
                    $this->escape($this->defaultValue($column)),
                );
            }
            $lines[] = '    </TABLE>';
            $lines[] = '  >];';
        }

        foreach ($schema->getTables() as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                $targetName = $foreignKey->getReferencedTableName()->toString();
                if (!$schema->hasTable($targetName)) {
                    continue;
                }
                $target = $schema->getTable($targetName);
                $referenced = $foreignKey->getReferencedColumnNames();
                foreach ($foreignKey->getReferencingColumnNames() as $index => $local) {
                    $lines[] = $this->edge(
                        $table,
                        $local->toString(),
                        $target,
                        $referenced[$index]->toString(),
                        $nodes,
                        $ports,
                    );
                }
            }

            $inferred = [];
            if ($inferGedmoTranslations) {
                $inferred[] = $this->inferredEdge($schema, $table, '_translation', ['foreign_key'], $nodes, $ports);
            }
            if ($inferGedmoLogs) {
                $inferred[] = $this->inferredEdge(
                    $schema,
                    $table,
                    '_log',
                    ['object_id', 'object_class'],
                    $nodes,
                    $ports,
                );
            }
            array_push($lines, ...array_filter($inferred));
        }

        $lines[] = '}';

        return implode("\n", $lines);
    }

    /** Links `<base><suffix>.<first column>` to `<base>.id` for a table without declared foreign keys.
     *
     * @param list<string>       $columns
     * @param array<int, string> $nodes
     * @param array<int, string> $ports
     */
    private function inferredEdge(
        Schema $schema,
        Table $table,
        string $suffix,
        array $columns,
        array $nodes,
        array $ports,
    ): ?string {
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

        return $this->edge($table, $columns[0], $schema->getTable($base), 'id', $nodes, $ports);
    }

    /** @param array<int, string> $nodes
     *  @param array<int, string> $ports
     */
    private function edge(Table $from, string $local, Table $to, string $foreign, array $nodes, array $ports): string
    {
        return sprintf(
            '  %s:%s:w -> %s:%s:w;',
            $nodes[spl_object_id($from)],
            $ports[spl_object_id($from->getColumn($local))],
            $nodes[spl_object_id($to)],
            $ports[spl_object_id($to->getColumn($foreign))],
        );
    }

    private function defaultValue(Column $column): string
    {
        if ($column->getAutoincrement()) {
            return 'auto_increment';
        }
        /** @var scalar|null $default */
        $default = $column->getDefault();

        return match ($default) {
            true => 'true',
            false => 'false',
            default => (string) $default,
        };
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_XML1, 'UTF-8');
    }
}
