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

class Vertex extends Node
{
    /** @var string */
    protected $id;

    /** @var Vertex */
    protected $parent;

    /** @var Vertex[] */
    protected $children = [];

    /** @var Graph */
    protected $graph;

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->parent) {
            return "<$this->id>$this->id";
        }
        $this->buildAttributes();

        return "$this->id [\r\n".
            implode("\r\n", $this->indentAll($this->attributes)).
            "\r\n]";
    }

    /**
     * Vertex constructor.
     *
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Build attributes.
     */
    protected function buildAttributes()
    {
        $this->setAttribute('label', count($this->children) ? mb_convert_case($this->id, MB_CASE_UPPER).'|'.implode('|', $this->children) : $this->id);
    }

    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get parent.
     *
     * @return Vertex
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent.
     *
     * @param Vertex $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get children.
     *
     * @return Vertex[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set children.
     *
     * @param Vertex[] $children
     */
    public function setChildren($children)
    {
        foreach ($children as $child) {
            $child->addChild($child);
        }
    }

    /**
     * Add child.
     *
     * @param Vertex $child
     */
    public function addChild(Vertex $child)
    {
        if ($this->parent) {
            throw new \InvalidArgumentException('Vertex can only have one ancestor');
        }
        $child->setGraph($this->graph);
        $child->setParent($this);
        $this->children[$child->getId()] = $child;
    }

    /**
     * Get child.
     *
     * @param $id
     *
     * @return Vertex
     */
    public function getChild($id)
    {
        if (!array_key_exists($id, $this->children)) {
            return null;
        }

        return $this->children[$id];
    }

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
     * @param Vertex $vertex
     */
    public function addEdgeTo(Vertex $vertex)
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }
        $this->graph->setEdge(new Edge($this, $vertex));
    }

    /**
     * Add edge from.
     *
     * @param Vertex $vertex
     */
    public function addEdgeFrom(Vertex $vertex)
    {
        if (!$this->graph) {
            throw new \RuntimeException('Graph is not defined');
        }
        $this->graph->setEdge(new Edge($vertex, $this));
    }
}
