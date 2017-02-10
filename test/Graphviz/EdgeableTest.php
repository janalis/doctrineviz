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

namespace Janalis\Doctrineviz\Test\Graphviz;

use Janalis\Doctrineviz\Graphviz\Edge;
use Janalis\Doctrineviz\Graphviz\Graph;
use Janalis\Doctrineviz\Graphviz\Vertex;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Edgeable test.
 *
 * @coversDefaultClass Janalis\Doctrineviz\Graphviz\Edgeable
 */
class EdgeableTest extends WebTestCase
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
        $this->assertNull($vertex->getGraph());
        $vertex->setGraph($graph);
        $this->assertEquals($graph, $vertex->getGraph());
    }

    /**
     * Test edge to.
     *
     * @group graphviz
     */
    public function testEdgeTo()
    {
        $vertex1 = new Vertex('foo');
        $vertex2 = new Vertex('bar');
        $graph = new Graph();
        $graph->addVertex($vertex1);
        $graph->addVertex($vertex2);
        // edge to
        $this->assertEmpty($graph->getEdges());
        $vertex1->addEdgeTo($vertex2);
        $this->assertCount(1, $graph->getEdges());
        $this->assertEquals(new Edge($vertex1, $vertex2), $graph->getEdge('foo -> bar;'));
        $vertex1->removeEdgeTo($vertex2);
        $this->assertEmpty($graph->getEdges());
        // exceptions
        $vertex1->setGraph(null);
        $vertex2->setGraph(null);
        try {
            $vertex1->addEdgeTo($vertex2);
            $this->fail('Exception should have been thrown.');
        } catch (\Exception $ignore) {
        }
        try {
            $vertex1->removeEdgeTo($vertex2);
            $this->fail('Exception should have been thrown.');
        } catch (\Exception $ignore) {
        }
    }

    /**
     * Test edge from.
     *
     * @group graphviz
     */
    public function testEdgeFrom()
    {
        $vertex1 = new Vertex('foo');
        $vertex2 = new Vertex('bar');
        $graph = new Graph();
        $graph->addVertex($vertex1);
        $graph->addVertex($vertex2);
        // edge from
        $this->assertEmpty($graph->getEdges());
        $vertex1->addEdgeFrom($vertex2);
        $this->assertCount(1, $graph->getEdges());
        $this->assertEquals(new Edge($vertex2, $vertex1), $graph->getEdge('bar -> foo;'));
        $vertex1->removeEdgeFrom($vertex2);
        $this->assertEmpty($graph->getEdges());
        // exceptions
        $vertex1->setGraph(null);
        $vertex2->setGraph(null);
        try {
            $vertex1->addEdgeFrom($vertex2);
            $this->fail('Exception should have been thrown.');
        } catch (\Exception $ignore) {
        }
        try {
            $vertex1->removeEdgeFrom($vertex2);
            $this->fail('Exception should have been thrown.');
        } catch (\Exception $ignore) {
        }
    }
}
