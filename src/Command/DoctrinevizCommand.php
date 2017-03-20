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
use Doctrine\ORM\Mapping\ClassMetadataInfo;
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
            ->addOption('output-path', 'o', InputOption::VALUE_OPTIONAL, 'Output path');
    }

    /**
     * Execute.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int Status code
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
            if (!$metadata->getFieldNames()) {
                continue;
            }
            $table = $graph->createVertex($metadata->getTableName());
            $table->createAttribute('shape', 'record');
            $table->createAttribute('width', '4');
            foreach ($metadata->getFieldNames() as $fieldName) {
                $fieldMapping = $metadata->getFieldMapping($fieldName);
                $table->addRecord(new Record($this->getFieldMappingDisplayName($fieldMapping)));
            }
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
                            $record = new Record($this->getFieldMappingDisplayName($joinColumn, 'name'));
                            $table->addRecord($record);
                            if (array_key_exists($sourceEntity, $tables)) {
                                $name = $this->getFieldMappingDisplayName($joinColumn, 'referencedColumnName');
                                $to = $graph->getVertex($tables[$sourceEntity]->getId())->getRecord($name);
                                if (!$to) {
                                    $to = new Record($name);
                                    $tables[$sourceEntity]->addRecord($to);
                                }
                            }
                            $record->addEdgeTo($to, '* 1');
                        }
                    }
                    if (array_key_exists('inverseJoinColumns', $joinTable)) {
                        $targetEntity = $associationMapping['targetEntity'];
                        $inverseJoinColumns = $joinTable['inverseJoinColumns'];
                        foreach ($inverseJoinColumns as $inverseJoinColumn) {
                            $record = new Record($this->getFieldMappingDisplayName($inverseJoinColumn, 'name'));
                            $table->addRecord($record);
                            if (array_key_exists($targetEntity, $tables)) {
                                $name = $this->getFieldMappingDisplayName($inverseJoinColumn, 'referencedColumnName');
                                $to = $graph->getVertex($tables[$targetEntity]->getId())->getRecord($name);
                                if (!$to) {
                                    $to = new Record($name);
                                    $tables[$targetEntity]->addRecord($to);
                                }
                            }
                            $record->addEdgeTo($to, '* 1');
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
                        $to = new Record($this->getFieldMappingDisplayName([
                            'columnName' => array_values($columns)[0],
                        ]));
                        $tables[$targetEntity]->addRecord($to);
                    }
                    $from = $graph->getVertex($tables[$entity]->getId())->getRecord(array_keys($columns)[0]);
                    if (!$from) {
                        $from = new Record($this->getFieldMappingDisplayName([
                            'columnName' => array_keys($columns)[0],
                        ]));
                        $tables[$entity]->addRecord($from);
                    }
                    $from->addEdgeTo($to, $this->getCardinality($associationMapping));
                }
            }
        }
        ksort($tables);
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
            return (int) !file_put_contents($path, $graphviz->createImageData($graph));
        }
    }

    protected function getCardinality(array $fieldMapping)
    {
        if (!array_key_exists('type', $fieldMapping)) {
            return null;
        }

        switch ($fieldMapping['type']) {
            case ClassMetadataInfo::ONE_TO_ONE:
                return '1 1';
            case ClassMetadataInfo::ONE_TO_MANY:
                return '1 *';
            case ClassMetadataInfo::MANY_TO_ONE:
                return '* 1';
            case ClassMetadataInfo::MANY_TO_MANY:
                return '* *';
        }
    }

    /**
     * Get field mapping display name.
     *
     * @param array  $fieldMapping
     * @param string $nameKey
     *
     * @return string
     */
    protected function getFieldMappingDisplayName(array $fieldMapping, $nameKey = 'columnName')
    {
        $name = $fieldMapping[$nameKey];
        $type = array_key_exists('type', $fieldMapping) ? $fieldMapping['type'] : 'integer';

        return sprintf('%s : %s', $name, $type);
    }
}
