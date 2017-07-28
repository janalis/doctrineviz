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

namespace Janalis\Doctrineviz\Graphviz;

class Graph implements GraphInterface
{
    use Attributable;

    /** @var VertexInterface[] */
    protected $vertices;

    /** @var EdgeInterface[] */
    protected $edges;

    /**
     * @return string
     */
    public function __toString()
    {
        return 'digraph g {'.PHP_EOL.(!count($this->getAttributes()) ? '' : implode(PHP_EOL, $this->indentAll($this->getAttributes())).
            PHP_EOL).(!count($this->getVertices()) ? '' : implode(PHP_EOL, $this->indentAll($this->getVertices())).
            PHP_EOL).(!count($this->getEdges()) ? '' : implode(PHP_EOL, $this->indentAll($this->getEdges())).
            PHP_EOL).'}';
    }

    /**
     * Create vertex.
     *
     * @param string $id
     *
     * @return VertexInterface
     */
    public function createVertex(string $id): VertexInterface
    {
        return new Vertex($id, $this);
    }

    /**
     * Get vertices.
     *
     * @return VertexInterface[]
     */
    public function getVertices(): array
    {
        if ($this->vertices) {
            ksort($this->vertices);
        }

        return $this->vertices ? array_values($this->vertices) : [];
    }

    /**
     * Set vertices.
     *
     * @param VertexInterface[] $vertices
     */
    public function setVertices(array $vertices): void
    {
        foreach ($vertices as $vertex) {
            $this->addVertex($vertex);
        }
    }

    /**
     * Get vertex.
     *
     * @param string $id
     *
     * @return VertexInterface
     */
    public function getVertex(string $id): ?VertexInterface
    {
        if (!array_key_exists($id, $this->vertices)) {
            return null;
        }

        return $this->vertices[$id];
    }

    /**
     * Add vertex.
     *
     * @param VertexInterface $vertex
     */
    public function addVertex(VertexInterface $vertex): void
    {
        $vertex->setGraph($this);
        $this->vertices[$vertex->getId()] = $vertex;
    }

    /**
     * Remove vertex.
     *
     * @param VertexInterface $vertex
     */
    public function removeVertex(VertexInterface $vertex): void
    {
        unset($this->vertices[$vertex->getId()]);
    }

    /**
     * Get edges.
     *
     * @return EdgeInterface[]
     */
    public function getEdges(): array
    {
        if ($this->edges) {
            ksort($this->edges);
        }

        return $this->edges ? array_values($this->edges) : [];
    }

    /**
     * Set edges.
     *
     * @param EdgeInterface[] $edges
     */
    public function setEdges(array $edges): void
    {
        $this->edges = $edges;
    }

    /**
     * Get edge.
     *
     * @param string $id
     *
     * @return EdgeInterface|null
     */
    public function getEdge(string $id): ?EdgeInterface
    {
        if (!array_key_exists($id, $this->edges)) {
            return null;
        }

        return $this->edges[$id];
    }

    /**
     * Add edge.
     *
     * @param EdgeInterface $edge
     *
     * @return EdgeInterface
     */
    public function addEdge(EdgeInterface $edge): EdgeInterface
    {
        $this->edges[(string) $edge] = $edge;

        return $edge;
    }

    /**
     * Remove edge.
     *
     * @param EdgeInterface $edge
     */
    public function removeEdge(EdgeInterface $edge): void
    {
        unset($this->edges[(string) $edge]);
    }
}
