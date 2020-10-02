<?php
namespace InterNations\Component\TypeJail\Factory;

use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;

final class JailFactory extends AbstractJailFactory
{
    protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass): ReflectionClass
    {
        return $class;
    }

    protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass): string
    {
        return $class->getName();
    }

    protected function createGenerator(): ProxyGeneratorInterface
    {
        return new AccessInterceptorValueHolderGenerator();
    }
}
