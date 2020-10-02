<?php
namespace InterNations\Component\TypeJail\Factory;

use InterNations\Component\TypeJail\Generator\SuperTypeJailGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;

final class SuperTypeJailFactory extends AbstractJailFactory
{
    protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass): ReflectionClass
    {
        return $class;
    }

    protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass): string
    {
        return 'JailedSuperType' . hash('sha256', $class->getName() . '\\__SURROGATE__\\' . $superClass->getName());
    }

    protected function createGenerator(): ProxyGeneratorInterface
    {
        return new SuperTypeJailGenerator();
    }
}
