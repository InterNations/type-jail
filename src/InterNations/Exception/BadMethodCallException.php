<?php
namespace InterNations\Component\TypeJail\Exception;

use BadMethodCallException as BaseBadMethodCallException;
use ReflectionClass;

class BadMethodCallException extends BaseBadMethodCallException implements ExceptionInterface
{
    /**
     * @param string $methodName
     * @param string $class
     * @param string $proxyClass
     * @return BadMethodCallException
     */
    public static function jailedMethod($methodName, $class, $proxyClass)
    {
        return new static(
            sprintf(
                'Jailed method "%s::%s()" invoked on proxy restricted to "%s". '
                . 'Check file "%s" to find out which method calls are allowed',
                $class,
                $methodName,
                $proxyClass,
                (new ReflectionClass($proxyClass))->getFileName()
            )
        );
    }
}
