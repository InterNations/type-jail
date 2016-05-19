<?php
namespace InterNations\Component\TypeJail\Util;

use ReflectionClass;
use Traversable;

final class TypeUtil
{
    /**
     * Returns true if the given type is a super type
     *
     * @param ReflectionClass $class
     * @param ReflectionClass $superClass
     * @return boolean
     */
    public static function isSuperTypeOf(ReflectionClass $class, ReflectionClass $superClass)
    {
        return $class->getName() === $superClass->getName()
            || $class->isSubclassOf($superClass)
            || in_array($superClass, $class->getInterfaces(), true);
    }

    /**
     * Return a list of super type names (interfaces and classes)
     *
     * @param ReflectionClass $class
     * @return array
     */
    public static function getSuperTypeNames(ReflectionClass $class)
    {
        $interfaceNames = $class->getInterfaceNames();

        $superTypeNames = [];

        do {
            $superTypeNames[] = $class->getName();

        } while ($class = $class->getParentClass());

        return array_merge($superTypeNames, $interfaceNames);
    }

    /**
     * Returns true if a value is traversable
     *
     * @param mixed $value
     * @return boolean
     */
    public static function isTraversable($value)
    {
        return is_array($value) || $value instanceof Traversable;
    }
}
