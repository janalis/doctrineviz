<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use Janalis\Doctrineviz\Command\DoctrinevizCommand;
use Janalis\Doctrineviz\SchemaDotRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DoctrinevizCommand::class)]
#[UsesClass(SchemaDotRenderer::class)]
final class CommandTest extends TestCase
{
    public function testDefaultConnectionOutputsOnlyRawDot(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement(
            'CREATE TABLE example (id INTEGER PRIMARY KEY, label VARCHAR(20) DEFAULT \'<info>raw</info>\')',
        );
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::once())->method('getConnection')->with(null)->willReturn($connection);
        $tester = new CommandTester(new DoctrinevizCommand($registry, new SchemaDotRenderer()));
        $tester->execute([], ['capture_stderr_separately' => true]);
        self::assertSame(0, $tester->getStatusCode());
        self::assertSame('', $tester->getErrorOutput());
        self::assertSame(
            new SchemaDotRenderer()->render($connection->createSchemaManager()->introspectSchema()) . "\n",
            $tester->getDisplay(),
        );
        self::assertStringContainsString('&lt;info&gt;raw&lt;/info&gt;', $tester->getDisplay());
    }

    public function testNamedConnectionAndInference(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE example (id INTEGER PRIMARY KEY)');
        $connection->executeStatement('CREATE TABLE example_translation (foreign_key INTEGER)');
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::once())->method('getConnection')->with('reporting')->willReturn($connection);
        $tester = new CommandTester(new DoctrinevizCommand($registry, new SchemaDotRenderer()));
        $tester->execute(['--connection' => 'reporting', '--infer-gedmo-translations' => true]);
        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('t1:c0:w -> t0:c0:w;', $tester->getDisplay());
    }

    public function testFailureIsSanitizedEvenWithVerboseOutput(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects(self::once())
            ->method('getConnection')
            ->willThrowException(new \RuntimeException('secret://example.invalid/private'));
        $tester = new CommandTester(new DoctrinevizCommand($registry, new SchemaDotRenderer()));
        $tester->execute([], ['capture_stderr_separately' => true, 'verbosity' => 256]);
        self::assertSame(1, $tester->getStatusCode());
        self::assertSame('', $tester->getDisplay());
        self::assertSame(
            "Unable to inspect the database schema. Check the Doctrine connection and database permissions.\n",
            $tester->getErrorOutput(),
        );
    }

    public function testFailureWithAnOrdinaryOutputStream(): void
    {
        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getConnection')->willThrowException(new \RuntimeException('private'));
        $tester = new CommandTester(new DoctrinevizCommand($registry, new SchemaDotRenderer()));
        $tester->execute([]);
        self::assertSame(1, $tester->getStatusCode());
        self::assertSame(
            "Unable to inspect the database schema. Check the Doctrine connection and database permissions.\n",
            $tester->getDisplay(),
        );
    }

    public function testCommandIsRegisteredWithHelpAndOptions(): void
    {
        $registry = $this->createStub(ManagerRegistry::class);
        $command = new DoctrinevizCommand($registry, new SchemaDotRenderer());
        $application = new Application();
        $application->addCommand($command);
        self::assertSame($command, $application->find('doctrine:schema:graphviz'));
        self::assertSame('Generate Graphviz DOT from the database schema.', $command->getDescription());
        self::assertSame(
            'bin/console doctrine:schema:graphviz > schema.dot && dot -Tsvg schema.dot -o schema.svg',
            $command->getHelp(),
        );
        $definition = $command->getDefinition();
        self::assertSame(
            'Doctrine DBAL connection name (defaults to the default connection).',
            $definition->getOption('connection')->getDescription(),
        );
        self::assertTrue($definition->getOption('connection')->isValueRequired());
        self::assertNull($definition->getOption('connection')->getDefault());
        self::assertSame(
            'Infer translation links without database foreign keys.',
            $definition->getOption('infer-gedmo-translations')->getDescription(),
        );
        self::assertFalse($definition->getOption('infer-gedmo-translations')->acceptValue());
        self::assertFalse($definition->getOption('infer-gedmo-translations')->getDefault());
    }
}
