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

use Janalis\Doctrineviz\Graphviz\Graph;
use Janalis\Doctrineviz\Graphviz\Graphviz;
use Janalis\Doctrineviz\Test\DoctrinevizTestCase;

/**
 * Graphviz test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Graphviz\Graphviz
 */
class GraphvizTest extends DoctrinevizTestCase
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

    /**
     * Test execute.
     *
     * @covers ::execute
     * @group graphviz
     */
    public function testExecute()
    {
        $graphviz = new Graphviz();
        $process = $this->callProtectedMethod(
            $graphviz,
            'execute',
            '%s',
            'date' // date is a common command between dos and unix
        );
        $this->assertEquals(0, $process->getExitCode());
        $this->assertNotEmpty($process->getOutput());
    }

    /**
     * Test create image file.
     *
     * @covers ::createImageFile
     * @group graphviz
     */
    public function testCreateImageFile()
    {
        $graph = new Graph();
        $this->setMethodAccessible(Graphviz::class, 'execute');
        $graphviz = $this->getMockBuilder(Graphviz::class)
            ->setMethods([
                'execute',
            ])
            ->getMock();
        $graphviz->expects($this->once())->method('execute');
        $graphviz->createImageFile($graph);
    }

    /**
     * Test display.
     *
     * @covers ::display
     * @group graphviz
     */
    public function testDisplay()
    {
        $graph = new Graph();
        $this->setMethodAccessible(Graphviz::class, 'execute');
        $graphviz = $this->getMockBuilder(Graphviz::class)
            ->setMethods([
                'createImageFile',
                'execute',
            ])
            ->getMock();
        $graphviz->expects($this->once())
            ->method('createImageFile')
            ->will($this->returnValue('foo'));
        $graphviz->expects($this->once())
            ->method('execute')
            ->will($this->returnValue('bar'));
        $graphviz->display($graph);
    }

    /**
     * Test create image data.
     *
     * @covers ::createImageData
     * @group graphviz
     */
    public function testCreateImageData()
    {
        $graph = new Graph();
        // test png format
        $this->setMethodAccessible(Graphviz::class, 'execute');
        $graphviz = $this->getMockBuilder(Graphviz::class)
            ->setMethods([
                'createImageFile',
                'execute',
            ])
            ->getMock();
        $path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'junk.txt';
        $content = 'foo';
        file_put_contents($path, $content);
        $this->assertFileExists($path);
        $graphviz->expects($this->once())
            ->method('createImageFile')
            ->will($this->returnValue($path));
        $this->assertEquals($content, $graphviz->createImageData($graph));
        $this->assertFileNotExists($path);
        // test dot format
        $this->setMethodAccessible(Graphviz::class, 'execute');
        $graphviz = $this->getMockBuilder(Graphviz::class)
            ->setConstructorArgs([
                'dot',
            ])
            ->setMethods([
                'createImageFile',
                'execute',
            ])
            ->getMock();
        $graphviz->expects($this->never())
            ->method('createImageFile')
            ->will($this->returnValue($path));
        $this->assertEquals((string) $graph, $graphviz->createImageData($graph));
    }

    /**
     * Test create image src.
     *
     * @covers ::createImageSrc
     * @group graphviz
     */
    public function testCreateImageSrc()
    {
        $graph = new Graph();
        // test png format
        $this->setMethodAccessible(Graphviz::class, 'execute');
        $graphviz = $this->getMockBuilder(Graphviz::class)
            ->setMethods([
                'createImageData',
            ])
            ->getMock();
        $data = 'foo';
        $graphviz->expects($this->once())
            ->method('createImageData')
            ->will($this->returnValue($data));
        $expected = 'data:image/png;base64,'.base64_encode($data);
        $this->assertEquals($expected, $graphviz->createImageSrc($graph));
    }

    /**
     * Test create image html.
     *
     * @covers ::createImageHtml
     * @group graphviz
     */
    public function testCreateImageHtml()
    {
        $graph = new Graph();
        // test png format
        $this->setMethodAccessible(Graphviz::class, 'execute');
        $graphviz = $this->getMockBuilder(Graphviz::class)
            ->setMethods([
                'createImageData',
            ])
            ->getMock();
        $data = 'foo';
        $graphviz->expects($this->once())
            ->method('createImageData')
            ->will($this->returnValue($data));
        $expected = '<img src="data:image/png;base64,'.base64_encode($data).'" />';
        $this->assertEquals($expected, $graphviz->createImageHtml($graph));
        // test svg format
        $this->setMethodAccessible(Graphviz::class, 'execute');
        $graphviz = $this->getMockBuilder(Graphviz::class)
            ->setConstructorArgs([
                'svg',
            ])
            ->setMethods([
                'createImageData',
            ])
            ->getMock();
        $data = 'foo';
        $graphviz->expects($this->once())
            ->method('createImageData')
            ->will($this->returnValue($data));
        $expected = '<object type="image/svg+xml" data="data:image/svg+xml;base64,'.base64_encode($data).'"></object>';
        $this->assertEquals($expected, $graphviz->createImageHtml($graph));
    }
}
