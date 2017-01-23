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
        return "<$this->id>$this->id";
    }

    /**
     * Record constructor.
     *
     * @param null|string $id
     * @param null|Vertex $parent
     */
    public function __construct($id = null, Vertex $parent = null)
    {
        $this->id = $id;
        $this->parent = $parent;
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