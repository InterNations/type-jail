<?php
namespace InterNations\Component\TypePolice\Factory;

use InterNations\Component\TypePolice\Exception\BadMethodCallException;
use InterNations\Component\TypePolice\Exception\HierarchyException;
use InterNations\Component\TypePolice\Exception\InvalidArgumentException;
use InterNations\Component\TypePolice\Generator\PolicedProxyGenerator;
use InterNations\Component\TypePolice\Util\TypeUtil;
use ProxyManager\Configuration;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\Version;
use ReflectionClass;

class PolicedProxyFactory implements PolicedProxyFactoryInterface
{
    private $checkedClasses = [];

    /** @var AccessInterceptorValueHolderGenerator */
    private $generator;

    /** @var MethodSeparator */
    private $methodSeparator;

    /** @var Configuration */
    protected $configuration;

    /** @param Configuration $configuration */
    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration();
        $this->generator = new PolicedProxyGenerator();
        $this->methodSeparator = new MethodSeparator();
    }

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

        list($prohibitedMethods) = $this->methodSeparator->separateMethods($instanceClass, $superClass);

        $deny = static function ($proxy, $instance, $method, $params, &$returnEarly) use ($class) {
            throw BadMethodCallException::policedMethod($method, get_class($instance), $class);
        };

        $proxyClassName = $this->generateProxyForSuperClass($instanceClass, $superClass);
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

    private function generateProxyForSuperClass(ReflectionClass $class, ReflectionClass $superClass)
    {
        $cacheKey = $class->getName() . $superClass->getName();
        if (isset($this->checkedClasses[$cacheKey])) {
            return $this->checkedClasses[$cacheKey];
        }

        $proxyParameters = [
            'cacheKey'           => $cacheKey,
            'factory'             => get_class($this),
            'proxyManagerVersion' => Version::VERSION
        ];
        $proxyClassName = $this
            ->configuration
            ->getClassNameInflector()
            ->getProxyClassName($cacheKey, $proxyParameters);

        if (!class_exists($proxyClassName)) {
            $this->generateProxyClass($proxyClassName, $class, $superClass, $proxyParameters);
        }

        $this
            ->configuration
            ->getSignatureChecker()
            ->checkSignature(new ReflectionClass($proxyClassName), $proxyParameters);

        return $this->checkedClasses[$cacheKey] = $proxyClassName;
    }

    private function generateProxyClass(
        $proxyClassName,
        ReflectionClass $class,
        ReflectionClass $superClass,
        array $proxyParameters
    )
    {
        $className = $this->configuration->getClassNameInflector()->getUserClassName($class->getName());
        $phpClass = new ClassGenerator($proxyClassName);

        $this->generator->generate(new ReflectionClass($className), $phpClass, $superClass);

        $phpClass = $this->configuration->getClassSignatureGenerator()->addSignature($phpClass, $proxyParameters);

        $this->configuration->getGeneratorStrategy()->generate($phpClass);
        $this->configuration->getProxyAutoloader()->__invoke($proxyClassName);
    }
}
