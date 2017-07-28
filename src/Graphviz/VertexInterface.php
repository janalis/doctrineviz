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

interface VertexInterface extends ElementInterface
{
    /**
     * Get attributes.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array;

    /**
     * Get records.
     *
     * @return RecordInterface[]
     */
    public function getRecords(): array;

    /**
     * Set records.
     *
     * @param RecordInterface[] $records
     */
    public function setRecords(array $records): void;

    /**
     * Add record.
     *
     * @param RecordInterface $record
     */
    public function addRecord(RecordInterface $record): void;

    /**
     * Remove record.
     *
     * @param RecordInterface $record
     */
    public function removeRecord(RecordInterface $record): void;

    /**
     * Create record.
     *
     * @param string $id
     *
     * @return RecordInterface
     */
    public function createRecord(string $id): RecordInterface;

    /**
     * Delete record.
     *
     * @param string $id
     */
    public function deleteRecord(string $id): void;

    /**
     * Get record.
     *
     * @param string $id
     *
     * @return RecordInterface
     */
    public function getRecord(string $id): ?RecordInterface;
}