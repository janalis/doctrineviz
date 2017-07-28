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

interface RecordInterface extends ElementInterface
{
    /**
     * Get vertex.
     *
     * @return VertexInterface|null
     */
    public function getVertex(): ?VertexInterface;

    /**
     * Set vertex.
     *
     * @param VertexInterface $vertex
     */
    public function setVertex(VertexInterface $vertex): void;
}
