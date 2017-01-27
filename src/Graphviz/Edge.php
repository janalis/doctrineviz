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

namespace Janalis\Doctrineviz\Graphviz;

class Edge
{
    /** @var Vertex|Record */
    protected $from;

    /** @var Vertex|Record */
    protected $to;

    /*
     * @return string
     */
    public function __toString()
    {
        return "{$this->getId($this->from)} -> {$this->getId($this->to)};";
    }

    /**
     * Get vertex id.
     *
     * @param Vertex|Record $element
     *
     * @return string
     */
    protected function getId($element)
    {
        return $element instanceof Record ? "{$element->getVertex()->getId()}:{$element->getId()}" : "{$element->getId()}";
    }

    /**
     * Edge constructor.
     *
     * @param Vertex|Record $from
     * @param Vertex|Record $to
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get from.
     *
     * @return Vertex|Record
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set from.
     *
     * @param Vertex|Record $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Get to.
     *
     * @return Vertex|Record
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set to.
     *
     * @param Vertex|Record $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }
}
