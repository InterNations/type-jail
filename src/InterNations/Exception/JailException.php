<?php
namespace InterNations\Component\TypeJail\Exception;

use ReflectionClass;

class JailException extends RuntimeException
{
    /** @no-named-arguments */
    public static function jailedMethod(string $methodName, string $class, string $proxyClass): self
    {
        return new self(
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
