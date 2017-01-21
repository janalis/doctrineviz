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
    /** @var Vertex */
    protected $from;

    /** @var Vertex */
    protected $to;

    /*
     * @return string
     */
    public function __toString()
    {
        return "{$this->getVertexId($this->from)} -> {$this->getVertexId($this->to)};";
    }

    /**
     * Get vertex id.
     *
     * @param Vertex $vertex
     *
     * @return string
     */
    protected function getVertexId(Vertex $vertex)
    {
        return ($vertex->getParent() ? $vertex->getParent()->getId() : '').":{$vertex->getId()}";
    }

    /**
     * Edge constructor.
     *
     * @param Vertex $from
     * @param Vertex $to
     */
    public function __construct(Vertex $from, Vertex $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get from.
     *
     * @return Vertex
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set from.
     *
     * @param Vertex $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Get to.
     *
     * @return Vertex
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set to.
     *
     * @param Vertex $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }
}
