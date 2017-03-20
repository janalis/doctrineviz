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

trait Edgeable
{
    /** @var Graph */
    protected $graph;

    /**
     * Get graph.
     *
     * @return Graph
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * Set graph.
     *
     * @param Graph $graph
     */
    public function setGraph($graph)
    {
        $this->graph = $graph;
    }

    /**
     * Add edge to.
     *
     * @param Vertex|Record $element
     * @param null|string   $label
     *
     * @return Edge
     */
    public function addEdgeTo($element, $label = null)
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }

        return $this->graph->addEdge(new Edge($this, $element, $label));
    }

    /**
     * Remove edge to.
     *
     * @param Vertex|Record $element
     * @param null|string   $label
     */
    public function removeEdgeTo($element, $label = null)
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }
        $this->graph->removeEdge(new Edge($this, $element, $label));
    }

    /**
     * Add edge from.
     *
     * @param Vertex|Record $element
     * @param null|string   $label
     *
     * @return Edge
     */
    public function addEdgeFrom($element, $label = null)
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }

        return $this->graph->addEdge(new Edge($element, $this, $label));
    }

    /**
     * Remove edge from.
     *
     * @param Vertex|Record $element
     * @param null|string   $label
     */
    public function removeEdgeFrom($element, $label = null)
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }
        $this->graph->removeEdge(new Edge($element, $this, $label));
    }
}
