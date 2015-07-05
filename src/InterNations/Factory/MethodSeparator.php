<?php
namespace InterNations\Component\TypeJail\Factory;

use ReflectionClass;
use ReflectionMethod;

class MethodSeparator
{
    /**
     * @param ReflectionClass $class
     * @param ReflectionClass $superClass
     * @return array Pair. The first element contains a list of class methods, the second the super class methods
     */
    public function separateMethods(ReflectionClass $class, ReflectionClass $superClass)
    {
        $classMethods = $superClassMethods = [];

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();

            if ($method->isDestructor() || $superClass->hasMethod($name)) {
                $superClassMethods[] = $name;
            } else {
                $classMethods[] = $name;
            }
        }

        sort($classMethods);
        sort($superClassMethods);

        return [$classMethods, $superClassMethods];
    }
}
