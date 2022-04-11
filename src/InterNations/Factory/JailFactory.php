<?php
namespace InterNations\Component\TypeJail\Factory;

use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;

final class JailFactory extends AbstractJailFactory
{
	/** @no-named-arguments */
    protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass): ReflectionClass
    {
        return $class;
    }

	/** @no-named-arguments */
    protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass): string
    {
        return $class->getName();
    }

	/** @no-named-arguments */
    protected function createGenerator(): ProxyGeneratorInterface
    {
        return new AccessInterceptorValueHolderGenerator();
    }
}
