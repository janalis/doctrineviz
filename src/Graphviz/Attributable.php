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

trait Attributable
{
    /** @var Attribute[] */
    protected $attributes = [];

    /**
     * Indent all.
     *
     * @param array $strings
     * @param int   $spaces
     *
     * @return array
     */
    protected function indentAll(array $strings = null, int $spaces = 2): array
    {
        $strings = $strings ?: [];
        foreach ($strings as $key => $string) {
            $strings[$key] = $this->indent((string) $string, $spaces);
        }

        return $strings;
    }

    /**
     * Indent.
     *
     * @param string $string
     * @param int    $spaces
     *
     * @return string
     */
    protected function indent(string $string, int $spaces = 2): string
    {
        $pad = str_repeat(' ', $spaces);

        return $pad.implode(PHP_EOL."$pad", explode(PHP_EOL, $string));
    }

    /**
     * Get attributes.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array
    {
        return $this->attributes ? array_values($this->attributes) : [];
    }

    /**
     * Set attributes.
     *
     * @param AttributeInterface[] $attributes
     */
    public function setAttributes(array $attributes): void
    {
        foreach ($attributes as $attribute) {
            $this->attributes[$attribute->getId()] = $attribute;
        }
    }

    /**
     * Get attribute.
     *
     * @param string $id
     *
     * @return AttributeInterface|null
     */
    public function getAttribute(string $id): ?AttributeInterface
    {
        if (!array_key_exists($id, $this->attributes)) {
            return null;
        }

        return $this->attributes[$id];
    }

    /**
     * Create attribute.
     *
     * @param string $id
     * @param string $value
     *
     * @return AttributeInterface
     */
    public function createAttribute(string $id, string $value): AttributeInterface
    {
        $attribute = new Attribute($id, $value);
        $this->attributes[$id] = $attribute;

        return $attribute;
    }

    /**
     * Delete attribute.
     *
     * @param string $id
     */
    public function deleteAttribute(string $id): void
    {
        unset($this->attributes[$id]);
    }

    /**
     * Add attribute.
     *
     * @param AttributeInterface $attribute
     */
    public function addAttribute(AttributeInterface $attribute): void
    {
        $this->attributes[$attribute->getId()] = $attribute;
    }

    /**
     * Remove attribute.
     *
     * @param AttributeInterface $attribute
     */
    public function removeAttribute(AttributeInterface $attribute): void
    {
        unset($this->attributes[$attribute->getId()]);
    }
}
