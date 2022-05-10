<?php
namespace InterNations\Component\TypeJail\Util;

use ReflectionClass;

final class TypeUtil
{
    /**
     * Returns true if the given type is a super type
     * @no-named-arguments
     */
    public static function isSuperTypeOf(ReflectionClass $class, ReflectionClass $superClass): bool
    {
        return $class->getName() === $superClass->getName()
            || $class->isSubclassOf($superClass)
            || in_array($superClass, $class->getInterfaces(), true);
    }

    /**
     * Return a list of super type names (interfaces and classes)
     *
     * @return string[]
     * @no-named-arguments
     */
    public static function getSuperTypeNames(ReflectionClass $class): array
    {
        $interfaceNames = $class->getInterfaceNames();

        $superTypeNames = [];

        do {
            $superTypeNames[] = $class->getName();

        } while ($class = $class->getParentClass());

        return array_merge($superTypeNames, $interfaceNames);
    }
}
