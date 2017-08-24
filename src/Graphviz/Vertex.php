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

class Vertex extends Element implements VertexInterface
{
    use Attributable;
    use Edgeable;

    /** @var RecordInterface[] */
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

    /**
     * Get attributes.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array
    {
        $this->createAttribute('label', count($this->records) ? mb_convert_case($this->id, MB_CASE_UPPER).'|'.implode('|', $this->records) : $this->id);

        return $this->attributes ? array_values($this->attributes) : [];
    }

    /**
     * Vertex constructor.
     *
     * @param string         $id
     * @param GraphInterface $graph
     */
    public function __construct(string $id = null, GraphInterface $graph = null)
    {
        $this->id = $id;
        if ($graph) {
            $graph->addVertex($this);
        }
    }

    /**
     * Get records.
     *
     * @return RecordInterface[]
     */
    public function getRecords(): array
    {
        return $this->records ? array_values($this->records) : [];
    }

    /**
     * Set records.
     *
     * @param RecordInterface[] $records
     */
    public function setRecords(array $records): void
    {
        foreach ($records as $record) {
            $this->addRecord($record);
        }
    }

    /**
     * Add record.
     *
     * @param RecordInterface $record
     */
    public function addRecord(RecordInterface $record): void
    {
        $record->setGraph($this->graph);
        $record->setVertex($this);
        $this->records[$record->getId()] = $record;
    }

    /**
     * Remove record.
     *
     * @param RecordInterface $record
     */
    public function removeRecord(RecordInterface $record): void
    {
        unset($this->records[$record->getId()]);
    }

    /**
     * Create record.
     *
     * @param string $id
     *
     * @return RecordInterface
     */
    public function createRecord(string $id): RecordInterface
    {
        return new Record($id, $this);
    }

    /**
     * Delete record.
     *
     * @param string $id
     */
    public function deleteRecord(string $id): void
    {
        unset($this->records[$id]);
    }

    /**
     * Get record.
     *
     * @param string $id
     *
     * @return RecordInterface
     */
    public function getRecord(string $id): ?RecordInterface
    {
        if (!array_key_exists($id, $this->records)) {
            return null;
        }

        return $this->records[$id];
    }
}
