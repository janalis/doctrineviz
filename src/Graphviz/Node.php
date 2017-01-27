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

abstract class Node
{
    /** @var Attribute[] */
    protected $attributes;

    /**
     * Indent all.
     *
     * @param array $strings
     * @param int   $spaces
     *
     * @return array
     */
    protected function indentAll(array $strings = null, $spaces = 2)
    {
        $strings = $strings ?: [];
        foreach ($strings as $key => $string) {
            $strings[$key] = $this->indent($string, $spaces);
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
    protected function indent($string, $spaces = 2)
    {
        $pad = str_repeat(' ', $spaces);

        return $pad.implode(PHP_EOL."$pad", explode(PHP_EOL, $string));
    }

    /**
     * Get attributes.
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes ? array_values($this->attributes) : [];
    }

    /**
     * Set attributes.
     *
     * @param Attribute[] $attributes
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $attribute) {
            $this->attributes[$attribute->getId()] = $attribute;
        }
    }

    /**
     * Get attribute.
     *
     * @return string|null
     */
    public function getAttribute($id)
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
     * @return Attribute
     */
    public function createAttribute($id, $value)
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
    public function deleteAttribute($id)
    {
        unset($this->attributes[$id]);
    }

    /**
     * Add attribute.
     *
     * @param Attribute $attribute
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[$attribute->getId()] = $attribute;
    }

    /**
     * Remove attribute.
     *
     * @param Attribute $attribute
     */
    public function removeAttribute(Attribute $attribute)
    {
        unset($this->attributes[$attribute->getId()]);
    }
}
