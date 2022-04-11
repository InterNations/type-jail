<?php
namespace InterNations\Component\TypeJail\Factory;

use InterNations\Component\TypeJail\Exception\JailException;
use InterNations\Component\TypeJail\Exception\HierarchyException;
use InterNations\Component\TypeJail\Util\TypeUtil;
use ProxyManager\Configuration;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Proxy\ProxyInterface;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\Version;
use ReflectionClass;

abstract class AbstractJailFactory implements JailFactoryInterface
{
    protected Configuration $configuration;
    /** @var string[] */
    private array $checkedClasses = [];
    private ?ProxyGeneratorInterface $generator = null;
    private ?MethodSeparatorInterface $methodSeparator = null;

	/** @no-named-arguments */
    public function __construct(?Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration();
    }

	/** @no-named-arguments */
    final public function createInstanceJail(object $instance, string $class): object
    {
        if ($instance instanceof ProxyInterface) {
            return $instance;
        }

        $instanceClass = new ReflectionClass($instance);
        $superClass = new ReflectionClass($class);

        if (!TypeUtil::isSuperTypeOf($instanceClass, $superClass)) {
            throw HierarchyException::hierarchyMismatch($instanceClass, $superClass);
        }

        [$prohibitedMethods] = $this->getMethodSeparator()->separateMethods($instanceClass, $superClass);

        /**
         * @param object $instance
         * @param mixed[] $params
         */
        $deny = static function (
            ProxyInterface $proxy,
            object $instance,
            string $method,
            array $params,
            bool &$returnEarly // @codingStandardsIgnoreLine
        ) use ($class): void {
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
	 * @no-named-arguments
     */
    final public function createAggregateJail(iterable $instanceAggregate, string $class): iterable // @codingStandardsIgnoreLine
    {
        foreach ($instanceAggregate as $instance) {
            yield $this->createInstanceJail($instance, $class);
        }
    }

	/** @no-named-arguments */
    abstract protected function getBaseClass(ReflectionClass $class, ReflectionClass $superClass): ReflectionClass;

	/** @no-named-arguments */
    abstract protected function getSurrogateClassName(ReflectionClass $class, ReflectionClass $superClass): string;

    abstract protected function createGenerator(): ProxyGeneratorInterface;

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator = $this->generator ?? $this->createGenerator();
    }

	/** @no-named-arguments */
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

    /**
	 * @param mixed[] $proxyParameters
	 * @no-named-arguments
	 */
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

    private function getMethodSeparator(): MethodSeparatorInterface
    {
        return $this->methodSeparator ?: $this->methodSeparator = $this->createMethodSeparator();
    }

    protected function createMethodSeparator(): MethodSeparatorInterface
    {
        return new MethodSeparator();
    }
}
