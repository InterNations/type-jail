<?php
namespace InterNations\Component\TypeJail\Factory;

use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ReflectionClass;

class JailFactory extends AbstractJailFactory
{
    protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass)
    {
        return $class;
    }

    protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass)
    {
        return $class->getName();
    }

    protected function getGenerator()
    {
        return $this->generator ?: $this->generator = new AccessInterceptorValueHolderGenerator();
    }
}
