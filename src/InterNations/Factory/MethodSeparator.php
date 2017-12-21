<?php
namespace InterNations\Component\TypeJail\Factory;

use ReflectionClass;
use ReflectionMethod;

class MethodSeparator
{
    /**
     * Separate methods between class and superclass
     *
     *  Returns a pair. The first element contains a list of class methods, the second the super class methods
     *
     * @return array[]
     */
    public function separateMethods(ReflectionClass $class, ReflectionClass $superClass): array
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
