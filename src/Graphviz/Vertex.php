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
    use Edgeable;

    /** @var string */
    protected $id;

    /** @var Record[] */
    protected $records = [];

    /**
     * @return string
     */
    public function __toString()
    {
        return "$this->id [".PHP_EOL.
            implode(PHP_EOL, $this->indentAll($this->getAttributes())).
            PHP_EOL.']';
    }

    public function getAttributes()
    {
        $this->createAttribute('label', count($this->records) ? mb_convert_case($this->id, MB_CASE_UPPER).'|'.implode('|', $this->records) : $this->id);

        return parent::getAttributes();
    }

    /**
     * Vertex constructor.
     *
     * @param string $id
     * @param Graph  $graph
     */
    public function __construct($id = null, $graph = null)
    {
        $this->id = $id;
        if ($graph) {
            $graph->addVertex($this);
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
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get records.
     *
     * @return Record[]
     */
    public function getRecords()
    {
        return $this->records ? array_values($this->records) : [];
    }

    /**
     * Set records.
     *
     * @param Record[] $records
     */
    public function setRecords($records)
    {
        foreach ($records as $record) {
            $this->addRecord($record);
        }
    }

    /**
     * Add record.
     *
     * @param Record $record
     */
    public function addRecord(Record $record)
    {
        $record->setGraph($this->graph);
        $record->setVertex($this);
        $this->records[$record->getId()] = $record;
    }

    /**
     * Remove record.
     *
     * @param Record $record
     */
    public function removeRecord(Record $record)
    {
        unset($this->records[$record->getId()]);
    }

    /**
     * Create record.
     *
     * @param string $id
     *
     * @return Record
     */
    public function createRecord($id)
    {
        return new Record($id, $this);
    }

    /**
     * Delete record.
     *
     * @param string $id
     */
    public function deleteRecord($id)
    {
        unset($this->records[$id]);
    }

    /**
     * Get record.
     *
     * @param $id
     *
     * @return Record
     */
    public function getRecord($id)
    {
        if (!array_key_exists($id, $this->records)) {
            return null;
        }

        return $this->records[$id];
    }
}
