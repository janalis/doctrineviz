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
use Janalis\Doctrineviz\Graphviz\Record;
use Janalis\Doctrineviz\Graphviz\Vertex;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Edge test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Graphviz\Edge
 */
class EdgeTest extends WebTestCase
{
    /**
     * Test accessors.
     *
     * @group graphviz
     */
    public function testAccessors()
    {
        // init values
        $from = $this->getMockBuilder(Vertex::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId',
            ])
            ->getMock();
        $from->expects($this->atLeastOnce())->method('getId')->will($this->returnValue('foo'));
        $to = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId',
                'getVertex',
            ])
            ->getMock();
        $to->expects($this->once())->method('getId')->will($this->returnValue('bar'));
        $to->expects($this->once())->method('getVertex')->will($this->returnValue($from));
        // constructor
        $edge = new Edge($from, $to);
        $this->assertEquals('foo -> foo:bar;', (string) $edge);
        $this->assertEquals($from, $edge->getFrom());
        $this->assertEquals($to, $edge->getTo());
        // getters and setters
        $edge->setFrom(null);
        $edge->setTo(null);
        $this->assertNull($edge->getFrom());
        $this->assertNull($edge->getTo());
    }
}
