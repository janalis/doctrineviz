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

class Edge implements EdgeInterface
{
    use Attributable;

    /** @var ElementInterface */
    protected $from;

    /** @var ElementInterface */
    protected $to;

    /** @var string */
    protected $label;

    /*
     * @return string
     */
    public function __toString()
    {
        return "{$this->getId($this->from)} -> {$this->getId($this->to)}".(!count($this->getAttributes()) ? '' : ' ['.PHP_EOL.
            implode(PHP_EOL, $this->indentAll($this->getAttributes())).
            PHP_EOL.']').';';
    }

    /**
     * Get attributes.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array
    {
        if (null !== $this->label) {
            $this->createAttribute('label', $this->label);
        }

        return $this->attributes ? array_values($this->attributes) : [];
    }

    /**
     * Get vertex id.
     *
     * @param ElementInterface $element
     *
     * @return string
     */
    protected function getId(ElementInterface $element): string
    {
        return $element instanceof RecordInterface ? "{$element->getVertex()->getId()}:{$element->getId()}" : "{$element->getId()}";
    }

    /**
     * Edge constructor.
     *
     * @param ElementInterface|null $from
     * @param ElementInterface|null $to
     * @param null|string      $label
     */
    public function __construct(ElementInterface $from = null, ElementInterface $to = null, string $label = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->label = $label;
    }

    /**
     * Get from.
     *
     * @return ElementInterface|null
     */
    public function getFrom(): ?ElementInterface
    {
        return $this->from;
    }

    /**
     * Set from.
     *
     * @param ElementInterface $from
     */
    public function setFrom(ElementInterface $from): void
    {
        $this->from = $from;
    }

    /**
     * Get to.
     *
     * @return ElementInterface|null
     */
    public function getTo(): ?ElementInterface
    {
        return $this->to;
    }

    /**
     * Set to.
     *
     * @param ElementInterface $to
     */
    public function setTo($to): void
    {
        $this->to = $to;
    }

    /**
     * Get label.
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set label.
     *
     * @param string|null $label
     */
    public function setLabel(string $label = null): void
    {
        $this->label = $label;
    }
}
