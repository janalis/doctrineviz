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

interface EdgeInterface
{
    /**
     * Get attributes.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array;

    /**
     * Get from.
     *
     * @return ElementInterface|null
     */
    public function getFrom(): ?ElementInterface;

    /**
     * Set from.
     *
     * @param ElementInterface $from
     */
    public function setFrom(ElementInterface $from): void;

    /**
     * Get to.
     *
     * @return ElementInterface|null
     */
    public function getTo(): ?ElementInterface;

    /**
     * Set to.
     *
     * @param Vertex|Record $to
     */
    public function setTo($to): void;

    /**
     * Get label.
     *
     * @return string|null
     */
    public function getLabel(): ?string;

    /**
     * Set label.
     *
     * @param string|null $label
     */
    public function setLabel(string $label = null): void;
}