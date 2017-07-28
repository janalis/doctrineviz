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

use Janalis\Doctrineviz\Graphviz\Attribute;
use Janalis\Doctrineviz\Graphviz\Vertex;
use Janalis\Doctrineviz\Test\DoctrinevizTestCase;

/**
 * Node test.
 */
class AttributableTest extends DoctrinevizTestCase
{
    /**
     * Test accessors.
     *
     * @group graphviz
     */
    public function testAccessors()
    {
        // init values
        $attribute = new Attribute('bat', 'bax');
        $attributes = [$attribute];
        // constructor
        $vertex = new Vertex('foo');
        $this->assertEquals('foo ['.PHP_EOL.
            '  label="foo"'.PHP_EOL.
            ']', (string) $vertex);
        // getters and setters
        $vertex = new Vertex(null);
        $vertex->setAttributes($attributes);
        $vertex->setId('baz');
        $this->assertContains($attribute, $vertex->getAttributes());
        $this->assertEquals('baz', $vertex->getId());
        // creators and deletors
        $vertex = new Vertex('bar');
        $vertex->createAttribute('foo', 'bar');
        $this->assertEquals(new Attribute('foo', 'bar'), $vertex->getAttribute('foo'));
        $vertex->deleteAttribute('foo');
        $this->assertNull($vertex->getAttribute('foo'));
        // adders and removers
        $vertex = new Vertex('foo');
        $vertex->addAttribute($attribute);
        $this->assertEquals($attribute, $vertex->getAttribute($attribute->getId()));
        $vertex->removeAttribute($attribute);
        $this->assertNull($vertex->getAttribute($attribute->getId()));
        // to string
        $vertex = new Vertex('foo');
        $vertex->addAttribute($attribute);
        $this->assertEquals('foo ['.PHP_EOL.
            '  bat="bax"'.PHP_EOL.
            '  label="foo"'.PHP_EOL.
            ']', (string) $vertex);
    }
}
