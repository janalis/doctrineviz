<?php

namespace Janalis\Doctrineviz\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Doctrineviz test case.
 */
class DoctrinevizTestCase extends WebTestCase
{
    /**
     * Call protected method.
     *
     * @param object $object
     * @param string $methodName
     * @param array  ...$arguments
     *
     * @return mixed
     */
    protected function callProtectedMethod($object, $methodName, ...$arguments)
    {
        $method = $this->setMethodAccessible(get_class($object), $methodName);

        return $method->invokeArgs($object, $arguments);
    }

    /**
     * Set method accessible.
     *
     * @param string $className
     * @param string $methodName
     *
     * @return \ReflectionMethod
     */
    protected function setMethodAccessible($className, $methodName)
    {
        $class = new \ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}