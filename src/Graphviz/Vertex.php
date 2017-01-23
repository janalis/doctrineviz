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
        $this->setAttribute('label', count($this->records) ? mb_convert_case($this->id, MB_CASE_UPPER).'|'.implode('|', $this->records) : $this->id);
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
        return $this->records;
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
