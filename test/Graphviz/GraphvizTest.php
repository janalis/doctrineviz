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

use Janalis\Doctrineviz\Graphviz\Graphviz;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Graphviz test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Graphviz\Graphviz
 */
class GraphvizTest extends WebTestCase
{
    /**
     * Test accessors.
     *
     * @group graphviz
     */
    public function testAccessors()
    {
        // constructor
        $graphviz = new Graphviz();
        $this->assertEquals('dot', $graphviz->getBinary());
        $this->assertEquals('png', $graphviz->getFormat());
        // getters and setters
        $graphviz = new Graphviz();
        $graphviz->setBinary('foo');
        $graphviz->setFormat('bar');
        $this->assertEquals('foo', $graphviz->getBinary());
        $this->assertEquals('bar', $graphviz->getFormat());
    }
}
