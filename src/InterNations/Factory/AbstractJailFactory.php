<?php
namespace InterNations\Component\TypeJail\Factory;

use InterNations\Component\TypeJail\Exception\JailException;
use InterNations\Component\TypeJail\Exception\HierarchyException;
use InterNations\Component\TypeJail\Exception\InvalidArgumentException;
use InterNations\Component\TypeJail\Util\TypeUtil;
use ProxyManager\Configuration;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Proxy\ProxyInterface;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\Version;
use ReflectionClass;

abstract class AbstractJailFactory implements JailFactoryInterface
{
    /** @var array */
    private $checkedClasses = [];

    /** @var AccessInterceptorValueHolderGenerator */
    protected $generator;

    /** @var MethodSeparator */
    protected $methodSeparator;

    /** @var Configuration */
    protected $configuration;

    public function __construct(?Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration();
    }

    /**
     * @param object $instance
     * @return object
     */
    public function createInstanceJail($instance, string $class)
    {
        if (!is_object($instance)) {
            throw InvalidArgumentException::invalidType($instance, 'object');
        }

        if ($instance instanceof ProxyInterface) {
            return $instance;
        }

        $instanceClass = new ReflectionClass($instance);
        $superClass = new ReflectionClass($class);

        if (!TypeUtil::isSuperTypeOf($instanceClass, $superClass)) {
            throw HierarchyException::hierarchyMismatch($instanceClass, $superClass);
        }

        [$prohibitedMethods] = $this->getMethodSeparator()->separateMethods($instanceClass, $superClass);

        $deny = static function ($proxy, $instance, $method, $params, &$returnEarly) use ($class) {
            throw JailException::jailedMethod($method, get_class($instance), $class);
        };

        $proxyClassName = $this->generateProxyForSuperClass($instanceClass, $superClass);

        return $proxyClassName::staticProxyConstructor(
            $instance,
            count($prohibitedMethods) > 0
                ? array_combine($prohibitedMethods, array_fill(0, count($prohibitedMethods), $deny))
                : []
        );
    }

    /**
     * @param object[] $instanceAggregate
     * @return object[]
     */
    public function createAggregateJail(iterable $instanceAggregate, string $class): array
    {
        $proxyAggregate = [];

        foreach ($instanceAggregate as $instance) {
            $proxyAggregate[] = $this->createInstanceJail($instance, $class);
        }

        return $proxyAggregate;
    }

    abstract protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass): ReflectionClass;

    abstract protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass): string;

    abstract protected function getGenerator(): ProxyGeneratorInterface;

    private function generateProxyForSuperClass(ReflectionClass $class, ReflectionClass $superClass): string
    {
        $cacheKey = $this->getSurrogateClassName($class, $superClass);

        if (isset($this->checkedClasses[$cacheKey])) {
            return $this->checkedClasses[$cacheKey];
        }

        $proxyParameters = [
            'cacheKey'           => $cacheKey,
            'factory'             => get_class($this),
            'proxyManagerVersion' => Version::getVersion(),
        ];
        $proxyClassName = $this
            ->configuration
            ->getClassNameInflector()
            ->getProxyClassName($cacheKey, $proxyParameters);

        if (!class_exists($proxyClassName)) {
            $baseClass = $this->getBaseClass($class, $superClass);
            $this->generateProxyClass($proxyClassName, $baseClass, $superClass, $proxyParameters);
        }

        $this
            ->configuration
            ->getSignatureChecker()
            ->checkSignature(new ReflectionClass($proxyClassName), $proxyParameters);

        return $this->checkedClasses[$cacheKey] = $proxyClassName;
    }

    /** @param mixed[] $proxyParameters */
    private function generateProxyClass(
        string $proxyClassName,
        ReflectionClass $baseClass,
        ReflectionClass $superClass,
        array $proxyParameters
    ): void
    {
        $className = $this->configuration->getClassNameInflector()->getUserClassName($baseClass->getName());
        $phpClass = new ClassGenerator($proxyClassName);

        $this->getGenerator()->generate(new ReflectionClass($className), $phpClass, $superClass);

        $phpClass = $this->configuration->getClassSignatureGenerator()->addSignature($phpClass, $proxyParameters);

        $this->configuration->getGeneratorStrategy()->generate($phpClass);
        $this->configuration->getProxyAutoloader()->__invoke($proxyClassName);
    }

    protected function getMethodSeparator(): MethodSeparator
    {
        return $this->methodSeparator ?: $this->methodSeparator = new MethodSeparator();
    }
}
