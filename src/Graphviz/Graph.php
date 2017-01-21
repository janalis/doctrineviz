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

class Graph extends Node
{
    /** @var Vertex[] */
    protected $vertices;

    /** @var Edge[] */
    protected $edges;

    /**
     * @return string
     */
    public function __toString()
    {
        return "digraph g {\r\n".
            implode("\r\n", $this->indentAll($this->attributes)).
            "\r\n".
            implode("\r\n", $this->indentAll($this->vertices)).
            "\r\n".
            implode("\r\n", $this->indentAll($this->edges)).
            "\r\n}";
    }

    /**
     * Create vertex.
     *
     * @param $id
     */
    public function createVertex($id)
    {
        $vertex = new Vertex($id);
        $this->addVertex($vertex);

        return $vertex;
    }

    /**
     * Get vertices.
     *
     * @return array
     */
    public function getVertices()
    {
        return $this->vertices;
    }

    /**
     * Set vertices.
     *
     * @param array $vertices
     */
    public function setVertices($vertices)
    {
        foreach ($vertices as $vertex) {
            $this->addVertex($vertex);
        }
    }

    /**
     * Get vertex.
     *
     * @return string|null
     */
    public function getVertex($id)
    {
        if (!array_key_exists($id, $this->vertices)) {
            return null;
        }

        return $this->vertices[$id];
    }

    /**
     * Add vertex.
     *
     * @param Vertex $vertex
     */
    public function addVertex(Vertex $vertex)
    {
        $vertex->setGraph($this);
        $this->vertices[$vertex->getId()] = $vertex;
    }

    /**
     * Get edges.
     *
     * @return array
     */
    public function getEdges()
    {
        return $this->edges;
    }

    /**
     * Set edges.
     *
     * @param array $edges
     */
    public function setEdges($edges)
    {
        $this->edges = $edges;
    }

    /**
     * Get edge.
     *
     * @return string|null
     */
    public function getEdge($id)
    {
        if (!array_key_exists($id, $this->edges)) {
            return null;
        }

        return $this->edges[$id];
    }

    /**
     * Set edge.
     *
     * @param Edge $edge
     */
    public function setEdge(Edge $edge)
    {
        $this->edges[(string) $edge] = $edge;
    }
}
