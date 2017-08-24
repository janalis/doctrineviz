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

declare(strict_types = 1);

namespace Janalis\Doctrineviz\Graphviz;

trait Edgeable
{
    /** @var GraphInterface */
    protected $graph;

    /**
     * Get graph.
     *
     * @return GraphInterface
     */
    public function getGraph(): ?GraphInterface
    {
        return $this->graph;
    }

    /**
     * Set graph.
     *
     * @param GraphInterface|null $graph
     */
    public function setGraph(GraphInterface $graph = null): void
    {
        $this->graph = $graph;
    }

    /**
     * Add edge to.
     *
     * @param ElementInterface $element
     * @param null|string      $label
     *
     * @return EdgeInterface
     *
     * @throws \RuntimeException
     */
    public function addEdgeTo(ElementInterface $element, string $label = null): EdgeInterface
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }

        return $this->graph->addEdge(new Edge($this, $element, $label));
    }

    /**
     * Remove edge to.
     *
     * @param ElementInterface $element
     * @param null|string      $label
     *
     * @throws \RuntimeException
     */
    public function removeEdgeTo(ElementInterface $element, string $label = null): void
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }
        $this->graph->removeEdge(new Edge($this, $element, $label));
    }

    /**
     * Add edge from.
     *
     * @param ElementInterface $element
     * @param null|string      $label
     *
     * @return EdgeInterface
     *
     * @throws \RuntimeException
     */
    public function addEdgeFrom(ElementInterface $element, string $label = null): EdgeInterface
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
     *
     * @throws \RuntimeException
     */
    public function removeEdgeFrom($element, string $label = null): void
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }
        $this->graph->removeEdge(new Edge($element, $this, $label));
    }
}
