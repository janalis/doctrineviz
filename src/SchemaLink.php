<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz;

use Doctrine\DBAL\Schema\Table;

/** A link from a table's columns to the columns of the table they reference. */
final readonly class SchemaLink
{
    /**
     * @param non-empty-list<array{string, string}> $columns each referencing column with the column it references
     * @param bool $inferred whether the link was inferred from Gedmo naming rather than declared as a foreign key
     */
    public function __construct(
        public Table $from,
        public Table $to,
        public array $columns,
        public bool $inferred,
    ) {}
}
