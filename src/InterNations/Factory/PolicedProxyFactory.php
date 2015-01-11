<?php
namespace InterNations\Component\TypePolice\Factory;

use InterNations\Component\TypePolice\Exception\BadMethodCallException;
use InterNations\Component\TypePolice\Exception\ExceptionInterface;
use InterNations\Component\TypePolice\Exception\HierarchyException;
use InterNations\Component\TypePolice\Exception\InvalidArgumentException;
use InterNations\Component\TypePolice\Util\TypeUtil;
use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ReflectionClass;

class PolicedProxyFactory extends AbstractBaseFactory
{
    /** @var AccessInterceptorValueHolderGenerator */
    private $generator;

    /** @var MethodSeparator */
    private $methodSeparator;

    /**
     * Police single instance
     *
     * Returns a proxy with all the methods policed that are not part of the contract of $class. For every method
     * that is invoked on the proxy that does not belong to the contract of $class an exception will be thrown
     *
     * @param object $instance
     * @param string $class
     * @throws ExceptionInterface Any TypePolice exception
     * @throws HierarchyException If a hierarchy error occurs
     * @return object Object of type instance with method calls that vio
     */
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

        $prefixInterceptors = [];
        foreach ($prohibitedMethods as $method) {
            $prefixInterceptors[$method] = $deny;
        }

        $proxyClassName = $this->generateProxy(get_class($instance));

        return new $proxyClassName($instance, $prefixInterceptors);
    }

    /**
     * Create policed proxies for an aggregate
     *
     * @param object[] $instanceAggregate
     * @param string $class
     * @return array
     */
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
