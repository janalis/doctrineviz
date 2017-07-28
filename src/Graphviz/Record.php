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

class Record extends Element implements RecordInterface
{
    use Edgeable;

    /** @var Vertex */
    protected $vertex;

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('<%s> %s\\l', explode(' ', $this->getId())[0], $this->getId());
    }

    /**
     * Get graph.
     *
     * @return GraphInterface|null
     */
    public function getGraph(): ?GraphInterface
    {
        return $this->graph;
    }

    /**
     * Record constructor.
     *
     * @param null|string $id
     * @param null|VertexInterface $vertex
     */
    public function __construct(string $id = null, VertexInterface $vertex = null)
    {
        $this->id = $id;
        if ($vertex) {
            $vertex->addRecord($this);
        }
    }

    /**
     * Get vertex.
     *
     * @return VertexInterface
     */
    public function getVertex(): ?VertexInterface
    {
        return $this->vertex;
    }

    /**
     * Set vertex.
     *
     * @param VertexInterface $vertex
     */
    public function setVertex(VertexInterface $vertex): void
    {
        $this->vertex = $vertex;
    }
}
