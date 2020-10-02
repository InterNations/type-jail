<?php
namespace InterNations\Component\TypeJail\Factory;

use ReflectionClass;

interface MethodSeparatorInterface
{
    /** @return string[] */
    public function separateMethods(ReflectionClass $class, ReflectionClass $superClass): array;
}
