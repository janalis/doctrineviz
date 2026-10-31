<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\Schema;

final class SchemaDotRenderer
{
    public function __construct(
        private readonly SchemaLinks $links = new SchemaLinks(),
    ) {}

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
                    $this->escape(ColumnDefault::of($column)),
                );
            }
            $lines[] = '    </TABLE>';
            $lines[] = '  >];';
        }

        foreach ($this->links->find($schema, $inferGedmoTranslations, $inferGedmoLogs) as $link) {
            foreach ($link->columns as [$local, $foreign]) {
                $lines[] = sprintf(
                    '  %s:%s:w -> %s:%s:w;',
                    $nodes[spl_object_id($link->from)],
                    $ports[spl_object_id($link->from->getColumn($local))],
                    $nodes[spl_object_id($link->to)],
                    $ports[spl_object_id($link->to->getColumn($foreign))],
                );
            }
        }

        $lines[] = '}';

        return implode("\n", $lines);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_XML1, 'UTF-8');
    }
}
