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

interface ElementInterface
{
    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): ?string;

    /**
     * Set id.
     *
     * @param string $id
     */
    public function setId(string $id): void;

    /**
     * Get graph.
     *
     * @return GraphInterface|null
     */
    public function getGraph(): ?GraphInterface;

    /**
     * Set graph.
     *
     * @param GraphInterface
     */
    public function setGraph(GraphInterface $graph): void;
}
