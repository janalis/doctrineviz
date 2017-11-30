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
 * Record test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Graphviz\Record
 */
class RecordTest extends DoctrinevizTestCase
{
    /**
     * Test accessors.
     *
     * @group graphviz
     */
    public function testAccessors()
    {
        $graph = new Graph();
        $vertex = new Vertex('foo');
        $vertex->setGraph($graph);
        // constructor
        $record = new Record('bar', $vertex);
        $this->assertEquals('bar', $record->getId());
        $this->assertEquals($vertex, $record->getVertex());
        $this->assertEquals($graph, $record->getGraph());
        // getters and setters
        $record = new Record();
        $record->setId('bar');
        $record->setVertex($vertex);
        $record->setGraph($graph);
        $this->assertEquals('bar', $record->getId());
        $this->assertEquals($vertex, $record->getVertex());
        $this->assertEquals($graph, $record->getGraph());
    }
}
