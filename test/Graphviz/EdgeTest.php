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

use Janalis\Doctrineviz\Graphviz\Edge;
use Janalis\Doctrineviz\Graphviz\Record;
use Janalis\Doctrineviz\Graphviz\Vertex;
use Janalis\Doctrineviz\Test\DoctrinevizTestCase;

/**
 * Edge test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Graphviz\Edge
 */
class EdgeTest extends DoctrinevizTestCase
{
    /**
     * Test constructor.
     *
     * @group graphviz
     */
    public function testConstructor()
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
        $edge->setLabel('baz');
        $this->assertEquals('foo -> foo:bar ['.PHP_EOL.
            '  label="baz"'.PHP_EOL.
            '];', (string) $edge);
        $this->assertEquals($from, $edge->getFrom());
        $this->assertEquals($to, $edge->getTo());
    }
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
            ->getMock();
        $to = $this->getMockBuilder(Record::class)
            ->disableOriginalConstructor()
            ->getMock();
        // getters and setters
        $edge = new Edge();
        $edge->setFrom($from);
        $edge->setTo($to);
        $edge->setLabel(null);
        $this->assertEquals($from, $edge->getFrom());
        $this->assertEquals($to, $edge->getTo());
        $this->assertNull($edge->getLabel());
    }
}
