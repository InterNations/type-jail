<?php
namespace InterNations\Component\TypeJail\Factory;

use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ReflectionClass;

class SuperTypeFactory extends AbstractJailFactory
{
    protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass)
    {
        return $superClass;
    }

    protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass)
    {
        return $superClass->getName();
    }

    protected function getGenerator()
    {
        return $this->generator ?: $this->generator = new AccessInterceptorValueHolderGenerator();
    }
}
