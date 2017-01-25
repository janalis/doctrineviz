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
        return $this->attributes;
    }

    /**
     * Set attributes.
     *
     * @param Attribute[]|array $attributes
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $id => $attribute) {
            if ($attribute instanceof Attribute) {
                $this->attributes[$attribute->getId()] = $attribute;
                continue;
            }
            $this->setAttribute($id, $attribute);
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
     * Set attribute.
     *
     * @param string $id
     * @param string $value
     */
    public function setAttribute($id, $value)
    {
        $this->attributes[$id] = new Attribute($id, $value);
    }
}
