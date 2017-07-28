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

interface GraphInterface
{
    /**
     * Create vertex.
     *
     * @param string $id
     *
     * @return VertexInterface
     */
    public function createVertex(string $id): VertexInterface;

    /**
     * Get vertices.
     *
     * @return VertexInterface[]
     */
    public function getVertices(): array;

    /**
     * Set vertices.
     *
     * @param VertexInterface[] $vertices
     */
    public function setVertices(array $vertices): void;

    /**
     * Get vertex.
     *
     * @param string $id
     *
     * @return VertexInterface
     */
    public function getVertex(string $id): ?VertexInterface;

    /**
     * Add vertex.
     *
     * @param VertexInterface $vertex
     */
    public function addVertex(VertexInterface $vertex): void;

    /**
     * Remove vertex.
     *
     * @param VertexInterface $vertex
     */
    public function removeVertex(VertexInterface $vertex): void;

    /**
     * Get edges.
     *
     * @return EdgeInterface[]
     */
    public function getEdges(): array;

    /**
     * Set edges.
     *
     * @param EdgeInterface[] $edges
     */
    public function setEdges(array $edges): void;

    /**
     * Get edge.
     *
     * @param string $id
     *
     * @return EdgeInterface|null
     */
    public function getEdge(string $id): ?EdgeInterface;

    /**
     * Add edge.
     *
     * @param EdgeInterface $edge
     *
     * @return EdgeInterface
     */
    public function addEdge(EdgeInterface $edge): EdgeInterface;

    /**
     * Remove edge.
     *
     * @param EdgeInterface $edge
     */
    public function removeEdge(EdgeInterface $edge): void;
}