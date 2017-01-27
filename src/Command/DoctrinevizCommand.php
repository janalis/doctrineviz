<?php

/*
 * This file is part of the doctrineviz package
 *
 * Copyright (c) 2017 Pierre Hennequart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Pierre Hennequart <pierre@janalis.com>
 */

namespace Janalis\Doctrineviz\Command;

use Doctrine\ORM\EntityManager;
use Janalis\Doctrineviz\Graphviz\Graph;
use Janalis\Doctrineviz\Graphviz\Graphviz;
use Janalis\Doctrineviz\Graphviz\Record;
use Janalis\Doctrineviz\Graphviz\Vertex;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * doctrineviz command.
 */
class DoctrinevizCommand extends ContainerAwareCommand
{
    const NAME = 'doctrine:generate:viz';

    /**
     * Configure.
     */
    protected function configure()
    {
        $this
            ->setName(static::NAME)
            ->setHelp('Generates database mapping')
            ->addOption('pattern', 'p', InputOption::VALUE_OPTIONAL, 'Filter entities that match that PCRE pattern', '.*')
            ->addOption('binary', 'b', InputOption::VALUE_OPTIONAL, 'Path to graphviz dot binary')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format', 'png')
            ->addOption('output-path', 'o', InputOption::VALUE_OPTIONAL, 'Output path')
        ;
    }

    /**
     * Execute.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pattern = '/'.$input->getOption('pattern').'/';
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $entities = array_filter($em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames(), function ($entity) use ($pattern) {
            return preg_match($pattern, $entity);
        });
        $graph = new Graph();
        $graph->createAttribute('rankdir', 'LR');
        $graph->createAttribute('ranksep', '3');
        /** @var Vertex[] $tables */
        $tables = [];
        foreach ($entities as $entity) {
            $metadata = $em->getClassMetadata($entity);
            $table = $graph->createVertex($metadata->getTableName());
            $table->createAttribute('shape', 'record');
            $table->createAttribute('width', '4');
            array_map(function ($fieldName) use ($metadata, $table) {
                $fieldMapping = $metadata->getFieldMapping($fieldName);
                $table->addRecord(new Record($fieldMapping['columnName']));
            }, $metadata->getFieldNames());
            $tables[$entity] = $table;
        }
        foreach ($entities as $entity) {
            $metadata = $em->getClassMetadata($entity);
            foreach ($metadata->getAssociationMappings() as $associationMapping) {
                if (array_key_exists('joinTable', $associationMapping) && $associationMapping['joinTable']) {
                    $joinTable = $associationMapping['joinTable'];
                    $table = $graph->createVertex($joinTable['name']);
                    $table->createAttribute('shape', 'record');
                    $table->createAttribute('width', '4');
                    if (array_key_exists('joinColumns', $joinTable)) {
                        $sourceEntity = $associationMapping['sourceEntity'];
                        $joinColumns = $joinTable['joinColumns'];
                        foreach ($joinColumns as $joinColumn) {
                            $record = new Record($joinColumn['name']);
                            $table->addRecord($record);
                            if (array_key_exists($sourceEntity, $tables)) {
                                $name = $joinColumn['referencedColumnName'];
                                $to = $graph->getVertex($tables[$sourceEntity]->getId())->getRecord($name);
                                if (!$to) {
                                    $to = new Record($name);
                                    $tables[$sourceEntity]->addRecord($to);
                                }
                            }
                            $record->addEdgeTo($to);
                        }
                    }
                    if (array_key_exists('inverseJoinColumns', $joinTable)) {
                        $targetEntity = $associationMapping['targetEntity'];
                        $inverseJoinColumns = $joinTable['inverseJoinColumns'];
                        foreach ($inverseJoinColumns as $inverseJoinColumn) {
                            $record = new Record($inverseJoinColumn['name']);
                            $table->addRecord($record);
                            if (array_key_exists($targetEntity, $tables)) {
                                $name = $inverseJoinColumn['referencedColumnName'];
                                $to = $graph->getVertex($tables[$targetEntity]->getId())->getRecord($name);
                                if (!$to) {
                                    $to = new Record($name);
                                    $tables[$targetEntity]->addRecord($to);
                                }
                            }
                            $record->addEdgeTo($to);
                        }
                    }
                    $tables[$table->getId()] = $table;
                } else {
                    $targetEntity = $associationMapping['targetEntity'];
                    if (!array_key_exists($targetEntity, $tables) || !array_key_exists('sourceToTargetKeyColumns', $associationMapping)) {
                        continue;
                    }
                    $columns = $associationMapping['sourceToTargetKeyColumns'];
                    $to = $graph->getVertex($tables[$targetEntity]->getId())->getRecord(array_values($columns)[0]);
                    if (!$to) {
                        $to = new Record(array_values($columns)[0]);
                        $tables[$targetEntity]->addRecord($to);
                    }
                    $from = $graph->getVertex($tables[$entity]->getId())->getRecord(array_keys($columns)[0]);
                    if (!$from) {
                        $from = new Record(array_keys($columns)[0]);
                        $tables[$entity]->addRecord($from);
                    }
                    $from->addEdgeTo($to);
                }
            }
        }
        $format = $input->getOption('format', 'png');
        $binary = $input->getOption('binary', null);
        $path = $input->getOption('output-path', null);
        $graphviz = new Graphviz($format, $binary);
        if (!$path && 'dot' === $format) {
            $output->writeln((string) $graph);

            return 0;
        } elseif (!$path) {
            $graphviz->display($graph);

            return 0;
        } else {
            return !file_put_contents($path, $graphviz->createImageData($graph));
        }
    }
}
