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
    /**
     * Configure.
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:generate:viz')
            ->setHelp('Generates database mapping')
            ->addOption('pattern', 'p', InputOption::VALUE_OPTIONAL, 'Filter entities that match that PCRE pattern', '.*')
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
        $graph->setAttribute('rankdir', 'LR');
        $graph->setAttribute('ranksep', '3');
        /** @var Vertex[] $tables */
        $tables = [];
        // Add vertices
        foreach ($entities as $entity) {
            $metadata = $em->getClassMetadata($entity);
            $table = $graph->createVertex($metadata->getTableName());
            $table->setAttribute('shape', 'record');
            $table->setAttribute('width', '4');
            array_map(function ($fieldName) use ($metadata, $table) {
                $table->addChild(new Vertex($metadata->getFieldMapping($fieldName)['columnName']));
            }, $metadata->getFieldNames());
            $tables[$entity] = $table;
        }
        // Add edges
        foreach ($entities as $entity) {
            $metadata = $em->getClassMetadata($entity);
            foreach ($metadata->getAssociationMappings() as $associationMapping) {
                $targetEntity = $associationMapping['targetEntity'];
                if (!array_key_exists($targetEntity, $tables) || !array_key_exists('sourceToTargetKeyColumns', $associationMapping)) {
                    continue;
                }
                $columns = $associationMapping['sourceToTargetKeyColumns'];
                $from = $graph->getVertex($tables[$entity]->getId())->getChild(array_keys($columns)[0]);
                if (!$from) {
                    $from = new Vertex(array_keys($columns)[0]);
                    $tables[$entity]->addChild($from);
                }
                $to = $graph->getVertex($tables[$targetEntity]->getId())->getChild(array_values($columns)[0]);
                if (!$to) {
                    $to = new Vertex(array_values($columns)[0]);
                    $tables[$targetEntity]->addChild($to);
                }
                $from->addEdgeTo($to);
            }
        }
        $graphviz = new Graphviz();
        $graphviz->display($graph);
    }
}
