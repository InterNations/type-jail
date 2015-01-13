<?php
namespace InterNations\Component\TypePolice\Factory;

use InterNations\Component\TypePolice\Generator\PolicedSuperProxyGenerator;
use ReflectionClass;

class PolicedSuperProxyFactory extends AbstractProxyFactory
{
    protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass)
    {
        return $class;
    }

    protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass)
    {
        return 'PolicedSuperType' . hash('sha256', $class->getName() . '\\__SURROGATE__\\' . $superClass->getName());
    }

    protected function getGenerator()
    {
        return $this->generator ?: $this->generator = new PolicedSuperProxyGenerator();
    }
}
