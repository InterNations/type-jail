<?php
namespace InterNations\Component\TypePolice\Factory;

use InterNations\Component\TypePolice\Exception\BadMethodCallException;
use InterNations\Component\TypePolice\Exception\HierarchyException;
use InterNations\Component\TypePolice\Exception\InvalidArgumentException;
use InterNations\Component\TypePolice\Util\TypeUtil;
use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ReflectionClass;

class PolicedProxyFactory extends AbstractBaseFactory implements PolicedProxyFactoryInterface
{
    /** @var AccessInterceptorValueHolderGenerator */
    private $generator;

    /** @var MethodSeparator */
    private $methodSeparator;

    public function policeInstance($instance, $class)
    {
        if (!is_object($instance)) {
            throw InvalidArgumentException::invalidType($instance, 'object');
        }

        if (!is_string($class)) {
            throw InvalidArgumentException::invalidType($class, 'string');
        }

        $instanceClass = new ReflectionClass($instance);
        $superClass = new ReflectionClass($class);

        if (!TypeUtil::isSuperTypeOf($instanceClass, $superClass)) {
            throw HierarchyException::hierarchyMismatch($instanceClass, $superClass);
        }

        list($prohibitedMethods) = $this->getMethodSeparator()->separateMethods($instanceClass, $superClass);

        $deny = static function ($proxy, $instance, $method, $params, &$returnEarly) use ($class) {
            throw BadMethodCallException::policedMethod($method, get_class($instance), $class);
        };

        $proxyClassName = $this->generateProxy(get_class($instance));
        return new $proxyClassName(
            $instance,
            count($prohibitedMethods) > 0
                ? array_combine($prohibitedMethods, array_fill(0, count($prohibitedMethods), $deny))
                : []
        );
    }

    public function policeAggregate($instanceAggregate, $class)
    {
        if (!TypeUtil::isTraversable($instanceAggregate)) {
            throw InvalidArgumentException::invalidType($instanceAggregate, ['array', 'Traversable']);
        }

        if (!is_string($class)) {
            throw InvalidArgumentException::invalidType($class, 'string');
        }

        $proxyAggregate = [];

        foreach ($instanceAggregate as $instance) {
            $proxyAggregate[] = $this->policeInstance($instance, $class);
        }

        return $proxyAggregate;
    }

    protected function getGenerator()
    {
        return $this->generator ?: $this->generator = new AccessInterceptorValueHolderGenerator();
    }

    protected function getMethodSeparator()
    {
        return $this->methodSeparator ?: $this->methodSeparator = new MethodSeparator();
    }
}
