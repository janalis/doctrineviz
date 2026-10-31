<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz;

use Doctrine\DBAL\Schema\Column;

/** A column's default as shown in a diagram: `auto_increment`, the value, or nothing. */
final class ColumnDefault
{
    public static function of(Column $column): string
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
}
