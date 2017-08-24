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

interface AttributeInterface
{
    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set id.
     *
     * @param string $id
     */
    public function setId(string $id): void;

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue(): string;

    /**
     * Set value.
     *
     * @param string $value
     */
    public function setValue(string $value): void;
}
