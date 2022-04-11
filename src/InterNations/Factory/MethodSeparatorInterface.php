<?php
namespace InterNations\Component\TypeJail\Factory;

use ReflectionClass;

interface MethodSeparatorInterface
{
    /**
     * @return string[]
     * @no-named-arguments
     */
    public function separateMethods(ReflectionClass $class, ReflectionClass $superClass): array;
}
