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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Attribute test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Graphviz\Attribute
 */
class AttributeTest extends WebTestCase
{
    /**
     * Test accessors.
     *
     * @group graphviz
     */
    public function testAccessors()
    {
        // init values
        $id = 'foo';
        $value = 'bar';
        // constructor
        $attr = new Attribute($id, $value);
        $this->assertEquals($id, $attr->getId());
        $this->assertEquals($value, $attr->getValue());
        // getters and setters
        $attr->setId($id);
        $attr->setValue($value);
        $this->assertEquals($id, $attr->getId());
        $this->assertEquals($value, $attr->getValue());
        // to string
        $this->assertEquals('foo="bar"', (string) $attr);
    }
}
