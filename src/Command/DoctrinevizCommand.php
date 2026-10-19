<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Command;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Janalis\Doctrineviz\SchemaDotRenderer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'doctrine:schema:graphviz', description: 'Generate Graphviz DOT from the database schema.')]
final class DoctrinevizCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly SchemaDotRenderer $renderer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'connection',
                null,
                InputOption::VALUE_REQUIRED,
                'Doctrine DBAL connection name (defaults to the default connection).',
            )
            ->addOption(
                'infer-gedmo-translations',
                null,
                InputOption::VALUE_NONE,
                'Infer translation links without database foreign keys.',
            )
            ->addOption(
                'infer-gedmo-logs',
                null,
                InputOption::VALUE_NONE,
                'Infer log links without database foreign keys.',
            )
            ->setHelp('bin/console doctrine:schema:graphviz > schema.dot && dot -Tsvg schema.dot -o schema.svg');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            /** @var string|null $name */
            $name = $input->getOption('connection');
            /** @var Connection $connection */
            $connection = $this->registry->getConnection($name);
            $schema = $connection->createSchemaManager()->introspectSchema();
            /** @var bool $translations */
            $translations = $input->getOption('infer-gedmo-translations');
            /** @var bool $logs */
            $logs = $input->getOption('infer-gedmo-logs');
            $dot = $this->renderer->render($schema, $translations, $logs);
        } catch (\Throwable) {
            // Driver exceptions may contain credentials, DSNs or private schema details.
            ($output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output)->writeln(
                '<error>Unable to inspect the database schema. Check the Doctrine connection and database permissions.</error>',
            );

            return Command::FAILURE;
        }

        $output->writeln($dot, OutputInterface::OUTPUT_RAW);

        return Command::SUCCESS;
    }
}
