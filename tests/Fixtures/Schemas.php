<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests\Fixtures;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

final class Schemas
{
    public static function example(): Schema
    {
        $authors = Table::editor()
            ->setUnquotedName('author')
            ->setColumns(
                Column::editor()->setUnquotedName('id')->setTypeName('integer')->setAutoincrement(true)->create(),
                Column::editor()->setUnquotedName('region')->setTypeName('string')->setLength(2)->create(),
            )
            ->setPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id', 'region')->create())
            ->create();
        $books = Table::editor()
            ->setUnquotedName('book')
            ->setColumns(
                Column::editor()->setUnquotedName('id')->setTypeName('integer')->create(),
                Column::editor()->setUnquotedName('author_id')->setTypeName('integer')->create(),
                Column::editor()->setUnquotedName('author_region')->setTypeName('string')->setLength(2)->create(),
                Column::editor()
                    ->setUnquotedName('title')
                    ->setTypeName('string')
                    ->setLength(120)
                    ->setNotNull(false)
                    ->setDefaultValue('<draft> & "quoted"')
                    ->create(),
                Column::editor()->setUnquotedName('zero')->setTypeName('integer')->setDefaultValue(0)->create(),
                Column::editor()->setUnquotedName('zero_string')->setTypeName('string')->setDefaultValue('0')->create(),
                Column::editor()->setUnquotedName('enabled')->setTypeName('boolean')->setDefaultValue(false)->create(),
                Column::editor()->setUnquotedName('published')->setTypeName('boolean')->setDefaultValue(true)->create(),
                Column::editor()->setUnquotedName('price')->setTypeName('decimal')->setDefaultValue(1.25)->create(),
            )
            ->setPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create())
            ->addForeignKeyConstraint(
                ForeignKeyConstraint::editor()
                    ->setUnquotedName('fk_author')
                    ->setUnquotedReferencedTableName('author')
                    ->setUnquotedReferencingColumnNames('author_id', 'author_region')
                    ->setUnquotedReferencedColumnNames('id', 'region')
                    ->create(),
            )
            ->addForeignKeyConstraint(
                ForeignKeyConstraint::editor()
                    ->setUnquotedName('fk_self')
                    ->setUnquotedReferencedTableName('book')
                    ->setUnquotedReferencingColumnNames('id')
                    ->setUnquotedReferencedColumnNames('id')
                    ->create(),
            )
            ->create();

        return new Schema([$authors, $books]);
    }

    public static function translations(): Schema
    {
        $base = Table::editor()
            ->setUnquotedName('article')
            ->setColumns(Column::editor()->setUnquotedName('id')->setTypeName('integer')->create())
            ->create();
        $translations = Table::editor()
            ->setUnquotedName('article_translation')
            ->setColumns(
                Column::editor()->setUnquotedName('foreign_key')->setTypeName('integer')->create(),
                Column::editor()->setUnquotedName('content')->setTypeName('text')->create(),
            )
            ->create();

        return new Schema([$base, $translations]);
    }

    public static function logs(): Schema
    {
        $base = Table::editor()
            ->setUnquotedName('article')
            ->setColumns(Column::editor()->setUnquotedName('id')->setTypeName('integer')->create())
            ->create();
        $logs = Table::editor()
            ->setUnquotedName('article_log')
            ->setColumns(
                Column::editor()->setUnquotedName('id')->setTypeName('integer')->create(),
                Column::editor()->setUnquotedName('object_id')->setTypeName('string')->setLength(64)->create(),
                Column::editor()->setUnquotedName('object_class')->setTypeName('string')->setLength(191)->create(),
                Column::editor()->setUnquotedName('data')->setTypeName('text')->create(),
            )
            ->create();

        return new Schema([$base, $logs]);
    }
}
