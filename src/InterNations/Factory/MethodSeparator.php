<?php
namespace InterNations\Component\TypePolice\Factory;

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
            if ($superClass->hasMethod($method->getName())) {
                $superClassMethods[] = $method->getName();
            } else {
                $classMethods[] = $method->getName();
            }
        }

        sort($classMethods);
        sort($superClassMethods);

        return [$classMethods, $superClassMethods];
    }
}
