<?php
namespace InterNations\Component\TypeJail\Factory;

use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;

final class SuperTypeFactory extends AbstractJailFactory
{
    protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass): ReflectionClass
    {
        return $superClass;
    }

    protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass): string
    {
        return $superClass->getName();
    }

    protected function createGenerator(): ProxyGeneratorInterface
    {
        return new AccessInterceptorValueHolderGenerator();
    }
}
