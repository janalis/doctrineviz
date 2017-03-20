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

use Janalis\Doctrineviz\Graphviz\Attribute;
use Janalis\Doctrineviz\Graphviz\Edge;
use Janalis\Doctrineviz\Graphviz\Graph;
use Janalis\Doctrineviz\Graphviz\Vertex;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Graph test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Graphviz\Graph
 */
class GraphTest extends WebTestCase
{
    /**
     * Test accessors.
     *
     * @group graphviz
     */
    public function testAccessors()
    {
        // init values
        $vertex1 = new Vertex('foo');
        $vertex2 = new Vertex('bar');
        $vertices = [$vertex2, $vertex1];
        $edge = new Edge($vertex1, $vertex2);
        $edges = [$edge];
        // getters and setters
        $graph = new Graph();
        $graph->setVertices($vertices);
        $graph->setEdges($edges);
        $this->assertEquals($vertices, $graph->getVertices());
        $this->assertEquals($edges, $graph->getEdges());
        // creators and deletors
        $graph = new Graph();
        $graph->createAttribute('foo', 'bar');
        $graph->createVertex('baz');
        $this->assertEquals(new Attribute('foo', 'bar'), $graph->getAttribute('foo'));
        $this->assertEquals(new Vertex('baz', $graph), $graph->getVertex('baz'));
        // adders and removers
        $graph = new Graph();
        $graph->addVertex($vertex1);
        $graph->addVertex($vertex2);
        $graph->addEdge($edge);
        $this->assertEquals($vertex1, $graph->getVertex('foo'));
        $this->assertEquals($edge, $graph->getEdge('foo -> bar;'));
        $graph->removeVertex($vertex1);
        $graph->removeEdge($edge);
        $this->assertNull($graph->getVertex('foo'));
        $this->assertNull($graph->getEdge('foo -> bar;'));
        // to string
        $graph = new Graph();
        $graph->addVertex($vertex1);
        $graph->createVertex('bar');
        $graph->createAttribute('bee', 'zoo');
        $graph->addEdge($edge);
        $this->assertEquals('digraph g {'.PHP_EOL.
            '  bee="zoo"'.PHP_EOL.
            '  bar ['.PHP_EOL.
            '    label="bar"'.PHP_EOL.
            '  ]'.PHP_EOL.
            '  foo ['.PHP_EOL.
            '    label="foo"'.PHP_EOL.
            '  ]'.PHP_EOL.
            '  foo -> bar;'.PHP_EOL.
            '}', (string) $graph);
    }
}
