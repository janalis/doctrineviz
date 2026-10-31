<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use Janalis\Doctrineviz\ColumnDefault;
use Janalis\Doctrineviz\Command\DoctrinevizCommand;
use Janalis\Doctrineviz\SchemaDotRenderer;
use Janalis\Doctrineviz\SchemaEntityDiagram;
use Janalis\Doctrineviz\SchemaExporter;
use Janalis\Doctrineviz\SchemaLink;
use Janalis\Doctrineviz\SchemaLinks;
use Janalis\Viz\Source\SourceFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DoctrinevizCommand::class)]
#[UsesClass(SchemaDotRenderer::class)]
#[UsesClass(SchemaExporter::class)]
#[UsesClass(SchemaLinks::class)]
#[UsesClass(SchemaLink::class)]
#[UsesClass(ColumnDefault::class)]
#[UsesClass(SchemaEntityDiagram::class)]
final class CommandExportTest extends TestCase
{
    public function testFormatsGoToTheStandardOutput(): void
    {
        $connection = self::connection();
        $schema = $connection->createSchemaManager()->introspectSchema();
        foreach ([
            'mermaid' => SourceFormat::Mermaid,
            'plantuml' => SourceFormat::PlantUml,
            'dot' => SourceFormat::Dot,
        ] as $name => $format) {
            $tester = $this->tester($connection);
            $tester->execute(['--format' => $name], ['capture_stderr_separately' => true]);
            self::assertSame(0, $tester->getStatusCode());
            self::assertSame('', $tester->getErrorOutput());
            self::assertSame(
                new SchemaExporter()->source($schema, $format, false, false) . "\n",
                $tester->getDisplay(),
            );
        }
    }

    public function testOutputWritesTheSourceInTheFormatItsExtensionNames(): void
    {
        $connection = self::connection();
        $schema = $connection->createSchemaManager()->introspectSchema();
        $directory = self::directory();
        foreach ([
            'schema.mmd' => SourceFormat::Mermaid,
            'schema.puml' => SourceFormat::PlantUml,
            'schema.txt' => SourceFormat::Dot,
        ] as $file => $format) {
            $tester = $this->tester($connection);
            $tester->execute(['--output' => $directory . '/' . $file]);
            self::assertSame(0, $tester->getStatusCode());
            self::assertSame('', $tester->getDisplay());
            self::assertSame(
                new SchemaExporter()->source($schema, $format, false, false) . "\n",
                file_get_contents($directory . '/' . $file),
            );
        }
        $tester = $this->tester($connection);
        $tester->execute(['--output' => $directory . '/forced.mmd', '--format' => 'dot']);
        self::assertStringStartsWith('digraph database {', (string) file_get_contents($directory . '/forced.mmd'));
    }

    public function testImagesRenderNativelyOrWithTheCommandLineTool(): void
    {
        $connection = self::connection();
        $directory = self::directory();
        $tester = $this->tester($connection);
        $tester->execute(['--output' => $directory . '/schema.svg', '--format' => 'mermaid', '--engine' => 'native']);
        self::assertSame(0, $tester->getStatusCode());
        self::assertSame('', $tester->getDisplay());
        self::assertStringContainsString('<svg', (string) file_get_contents($directory . '/schema.svg'));
        $tester = $this->tester($connection);
        $tester->execute(['--output' => $directory . '/schema.png', '--engine' => 'cli']);
        self::assertSame(0, $tester->getStatusCode());
        self::assertStringStartsWith("\x89PNG", (string) file_get_contents($directory . '/schema.png'));
        $tester = $this->tester($connection);
        $tester->execute(['--output' => $directory . '/schema.pdf']);
        self::assertSame(0, $tester->getStatusCode());
        self::assertStringStartsWith('%PDF', (string) file_get_contents($directory . '/schema.pdf'));
    }

    public function testUnknownFormatsAndEnginesAreRefusedBeforeConnecting(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::never())->method('getConnection');
        $command = new DoctrinevizCommand($registry);
        foreach ([
            ['--format' => 'svg'],
            ['--format' => 'svg', '--engine' => 'nope'],
            ['--output' => 'schema.svg', '--format' => 'nope'],
        ] as $options) {
            $tester = new CommandTester($command);
            $tester->execute($options, ['capture_stderr_separately' => true]);
            self::assertSame(2, $tester->getStatusCode());
            self::assertSame('', $tester->getDisplay());
            self::assertSame("Unknown --format: use dot, mermaid or plantuml.\n", $tester->getErrorOutput());
        }
        $tester = new CommandTester($command);
        $tester->execute(['--engine' => 'nope'], ['capture_stderr_separately' => true]);
        self::assertSame(2, $tester->getStatusCode());
        self::assertSame("Unknown --engine: use auto, native or cli.\n", $tester->getErrorOutput());
        $tester = new CommandTester($command);
        $tester->execute(['--engine' => 'nope']);
        self::assertSame("Unknown --engine: use auto, native or cli.\n", $tester->getDisplay());
    }

    public function testAFileThatCannotBeWrittenFails(): void
    {
        $directory = self::directory();
        file_put_contents($directory . '/file', '');
        foreach (['schema.dot', 'schema.svg'] as $file) {
            $tester = $this->tester(self::connection());
            $tester->execute(['--output' => $directory . '/file/' . $file], ['capture_stderr_separately' => true]);
            self::assertSame(1, $tester->getStatusCode());
            self::assertSame('', $tester->getDisplay());
            self::assertStringStartsWith(
                'Unable to write ' . $directory . '/file/' . $file . ': ',
                $tester->getErrorOutput(),
            );
        }
        $tester = $this->tester(self::connection());
        $tester->execute(['--output' => $directory . '/file/schema.dot']);
        self::assertStringStartsWith('Unable to write ', $tester->getDisplay());
    }

    private static function connection(): Connection
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE author (id INTEGER PRIMARY KEY, name VARCHAR(20) NOT NULL)');
        $connection->executeStatement(
            'CREATE TABLE book (id INTEGER PRIMARY KEY, author_id INTEGER REFERENCES author (id))',
        );

        return $connection;
    }

    private function tester(Connection $connection): CommandTester
    {
        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getConnection')->willReturn($connection);

        return new CommandTester(new DoctrinevizCommand($registry));
    }

    private static function directory(): string
    {
        $directory = sys_get_temp_dir() . '/doctrineviz-' . bin2hex(random_bytes(6));
        mkdir($directory);

        return $directory;
    }
}
