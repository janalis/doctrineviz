<?php

namespace Janalis\Doctrineviz\Graphviz;

class Record
{
    use Edgeable;

    /** @var string */
    protected $id;

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
     * @return Graph|null
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * Record constructor.
     *
     * @param null|string $id
     * @param null|Vertex $vertex
     */
    public function __construct($id = null, Vertex $vertex = null)
    {
        $this->id = $id;
        if ($vertex) {
            $vertex->addRecord($this);
        }
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
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get vertex.
     *
     * @return Vertex
     */
    public function getVertex()
    {
        return $this->vertex;
    }

    /**
     * Set vertex.
     *
     * @param Vertex $vertex
     */
    public function setVertex($vertex)
    {
        $this->vertex = $vertex;
    }
}
