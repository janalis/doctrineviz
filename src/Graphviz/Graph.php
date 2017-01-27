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
        return 'digraph g {'.PHP_EOL.(!count($this->getAttributes()) ? '' :
            implode(PHP_EOL, $this->indentAll($this->getAttributes())).
            PHP_EOL).(!count($this->getVertices()) ? '' :
            implode(PHP_EOL, $this->indentAll($this->getVertices())).
            PHP_EOL).(!count($this->getEdges()) ? '' :
            implode(PHP_EOL, $this->indentAll($this->getEdges())).
            PHP_EOL).'}';
    }

    /**
     * Create vertex.
     *
     * @param string $id
     *
     * @return Vertex
     */
    public function createVertex($id)
    {
        return new Vertex($id, $this);
    }

    /**
     * Get vertices.
     *
     * @return Vertex[]
     */
    public function getVertices()
    {
        return $this->vertices ? array_values($this->vertices) : [];
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
     * Remove vertex.
     *
     * @param Vertex $vertex
     */
    public function removeVertex(Vertex $vertex)
    {
        unset($this->vertices[$vertex->getId()]);
    }

    /**
     * Get edges.
     *
     * @return Edge[]
     */
    public function getEdges()
    {
        return $this->edges ? array_values($this->edges) : [];
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
     * Add edge.
     *
     * @param Edge $edge
     */
    public function addEdge(Edge $edge)
    {
        $this->edges[(string) $edge] = $edge;
    }

    /**
     * Remove edge.
     *
     * @param Edge $edge
     */
    public function removeEdge(Edge $edge)
    {
        unset($this->edges[(string) $edge]);
    }
}
