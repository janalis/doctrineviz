<?php

declare(strict_types=1);

namespace Janalis\Doctrineviz\Command;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Janalis\Doctrineviz\SchemaExporter;
use Janalis\Viz\Render\Engine;
use Janalis\Viz\Render\Format;
use Janalis\Viz\Source\SourceFormat;
use Janalis\Viz\Viz;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'doctrine:schema:graphviz',
    description: 'Generate a Graphviz, Mermaid or PlantUML diagram of the database schema.',
)]
final class DoctrinevizCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly SchemaExporter $exporter = new SchemaExporter(),
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
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Diagram language: dot, mermaid or plantuml (defaults to the output extension, else dot).',
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Write to this file instead of the standard output; .svg, .png and .pdf render an image.',
            )
            ->addOption(
                'engine',
                null,
                InputOption::VALUE_REQUIRED,
                'Image renderer: auto (native, then the command-line tool), native or cli.',
                Engine::Auto->value,
            )
            ->setHelp(<<<'HELP'
                bin/console doctrine:schema:graphviz > schema.dot
                bin/console doctrine:schema:graphviz --format=mermaid > schema.mmd
                bin/console doctrine:schema:graphviz --output=schema.svg
                bin/console doctrine:schema:graphviz --format=plantuml --output=schema.png --engine=native
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $errors = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        /** @var string|null $path */
        $path = $input->getOption('output');
        /** @var string|null $formatName */
        $formatName = $input->getOption('format');
        /** @var string $engineName */
        $engineName = $input->getOption('engine');
        $format = $formatName === null
            ? ($path === null ? null : SourceFormat::forPath($path)) ?? SourceFormat::Dot
            : SourceFormat::tryFrom($formatName);
        $engine = Engine::tryFrom($engineName);
        if ($format === null || $engine === null) {
            $errors->writeln(
                $format === null
                    ? '<error>Unknown --format: use dot, mermaid or plantuml.</error>'
                    : '<error>Unknown --engine: use auto, native or cli.</error>',
            );

            return Command::INVALID;
        }

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
            $source = $this->exporter->source($schema, $format, $translations, $logs);
        } catch (\Throwable) {
            // Driver exceptions may contain credentials, DSNs or private schema details.
            $errors->writeln(
                '<error>Unable to inspect the database schema. Check the Doctrine connection and database permissions.</error>',
            );

            return Command::FAILURE;
        }

        if ($path === null) {
            $output->writeln($source, OutputInterface::OUTPUT_RAW);

            return Command::SUCCESS;
        }
        try {
            $this->write($path, $source, $format, $engine);
        } catch (\Throwable $failure) {
            $errors->writeln(sprintf('<error>Unable to write %s: %s</error>', $path, $failure->getMessage()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /** Writes the source to the file, or the image it renders when the path names an image format. */
    private function write(string $path, string $source, SourceFormat $format, Engine $engine): void
    {
        if (Format::forPath($path) === null) {
            new Filesystem()->dumpFile($path, $source . "\n");

            return;
        }
        new Viz(engine: $engine)->export($source, $format, $path);
    }
}
