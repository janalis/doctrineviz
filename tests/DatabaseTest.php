<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Janalis\Doctrineviz\SchemaDotRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(SchemaDotRenderer::class)]
final class DatabaseTest extends TestCase
{
    public function testMariaDbIntrospection(): void
    {
        $url = getenv('DOCTRINEVIZ_TEST_DATABASE_URL');
        if ($url === false || $url === '') {
            self::markTestSkipped('Set DOCTRINEVIZ_TEST_DATABASE_URL to an empty disposable MariaDB database.');
        }
        $params = new DsnParser(['mysql' => 'pdo_mysql'])->parse($url);
        self::assertMatchesRegularExpression('/^doctrineviz_test(?:_[a-z0-9]+)?$/', $params['dbname'] ?? '');
        $connection = DriverManager::getConnection($params);
        self::assertSame(
            [],
            $connection->createSchemaManager()->introspectSchema()->getTables(),
            'The test database must be empty.',
        );
        try {
            $connection->executeStatement(
                'CREATE TABLE author (id INT NOT NULL, region VARCHAR(2) NOT NULL, PRIMARY KEY (id, region))',
            );
            $connection->executeStatement(
                'CREATE TABLE book (id INT AUTO_INCREMENT PRIMARY KEY, author_id INT NOT NULL, author_region VARCHAR(2) NOT NULL, total INT DEFAULT 0, title VARCHAR(40) NULL, CONSTRAINT fk_author FOREIGN KEY (author_id, author_region) REFERENCES author (id, region))',
            );
            $schema = $connection->createSchemaManager()->introspectSchema();
            $dot = new SchemaDotRenderer()->render($schema);
            self::assertStringContainsString('<B>author</B>', $dot);
            self::assertStringContainsString('<I>author_id</I>', $dot);
            self::assertStringContainsString('auto_increment', $dot);
            self::assertStringContainsString(
                '>total</TD><TD ALIGN="LEFT">integer</TD><TD ALIGN="LEFT">NULL</TD><TD ALIGN="LEFT">0</TD>',
                $dot,
            );
            self::assertSame(2, substr_count($dot, ' -> '));
            self::assertStringContainsString('t1:c1:w -> t0:c0:w;', $dot);
            self::assertStringContainsString('t1:c2:w -> t0:c1:w;', $dot);
            $process = new Process(['dot', '-Tsvg']);
            $process->setInput($dot);
            $process->mustRun();
            self::assertSame('', $process->getErrorOutput());
        } finally {
            $connection->executeStatement('DROP TABLE IF EXISTS book');
            $connection->executeStatement('DROP TABLE IF EXISTS author');
            $connection->close();
        }
    }
}
