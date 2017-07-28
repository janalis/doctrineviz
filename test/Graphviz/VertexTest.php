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

declare(strict_types=1);

namespace Janalis\Doctrineviz\Test\Graphviz;

use Janalis\Doctrineviz\Graphviz\Graph;
use Janalis\Doctrineviz\Graphviz\Record;
use Janalis\Doctrineviz\Graphviz\Vertex;
use Janalis\Doctrineviz\Test\DoctrinevizTestCase;

/**
 * Vertex test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Graphviz\Vertex
 */
class VertexTest extends DoctrinevizTestCase
{
    /**
     * Test accessors.
     *
     * @group graphviz
     */
    public function testAccessors()
    {
        // init values
        $record = new Record('bar');
        $records = [$record];
        $graph = new Graph();
        // constructor
        $vertex = new Vertex('foo');
        $this->assertEquals('foo ['.PHP_EOL.
            '  label="foo"'.PHP_EOL.
            ']', (string) $vertex);
        // getters and setters
        $vertex = new Vertex(null);
        $vertex->setRecords($records);
        $vertex->setGraph($graph);
        $vertex->setId('baz');
        $this->assertEquals($records, $vertex->getRecords());
        $this->assertEquals($graph, $vertex->getGraph());
        $this->assertEquals('baz', $vertex->getId());
        // creators and deletors
        $vertex = new Vertex('foo');
        $this->assertEmpty($vertex->getRecords());
        $vertex->createRecord('bar');
        $this->assertCount(1, $vertex->getRecords());
        $this->assertEquals(new Record('bar', $vertex), $vertex->getRecord('bar'));
        $vertex->deleteRecord('bar');
        $this->assertEmpty($vertex->getRecords());
        // adders and removers
        $vertex = new Vertex('foo');
        $vertex->addRecord($record);
        $this->assertEquals($record, $vertex->getRecord($record->getId()));
        $vertex->removeRecord($record);
        $this->assertNull($vertex->getRecord($record->getId()));
        // to string
        $vertex = new Vertex('foo');
        $vertex->addRecord($record);
        $this->assertEquals('foo ['.PHP_EOL.
            '  label="FOO|<bar> bar\l"'.PHP_EOL.
            ']', (string) $vertex);
    }
}
