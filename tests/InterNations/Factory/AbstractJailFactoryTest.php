<?php
namespace InterNations\Component\TypeJail\Tests\Factory;

use ArrayIterator;
use InterNations\Component\TypeJail\Exception\JailException;
use InterNations\Component\TypeJail\Exception\ExceptionInterface;
use InterNations\Component\TypeJail\Exception\HierarchyException;
use InterNations\Component\TypeJail\Exception\InvalidArgumentException;
use InterNations\Component\TypeJail\Factory\JailFactoryInterface;
use InterNations\Component\TypeJail\Tests\Fixtures\AbstractBaseClass;
use InterNations\Component\TypeJail\Tests\Fixtures\AbstractBaseClass1;
use InterNations\Component\TypeJail\Tests\Fixtures\AbstractBaseClass2;
use InterNations\Component\TypeJail\Tests\Fixtures\BaseClass;
use InterNations\Component\TypeJail\Tests\Fixtures\ClassImplementsInterface;
use InterNations\Component\TypeJail\Tests\Fixtures\ClassWithPublicDestructor;
use InterNations\Component\TypeJail\Tests\Fixtures\ExtendsAndImplementsClass;
use InterNations\Component\TypeJail\Tests\Fixtures\ExtendsAndImplementsInterface1;
use InterNations\Component\TypeJail\Tests\Fixtures\ExtendsAndImplementsInterface2;
use InterNations\Component\TypeJail\Tests\Fixtures\ExtendsClass;
use InterNations\Component\TypeJail\Tests\Fixtures\InterfaceForClass;
use InterNations\Component\Testing\AbstractTestCase;
use InterNations\Component\TypeJail\Tests\Fixtures\InterfaceForPublicDestructorClass;
use ProxyManager\Proxy\ProxyInterface;
use stdClass;

abstract class AbstractJailFactoryTest extends AbstractTestCase
{
    protected JailFactoryInterface $factory;

    public static function getInstanceScenarios(): array
    {
        return [
            [
                new BaseClass(),
                BaseClass::class,
                ['baseMethod', 'staticBaseMethod'],
                []
            ],
            [
                new ExtendsClass(),
                AbstractBaseClass::class,
                ['baseMethod', 'abstractBaseMethod', 'staticBaseMethod'],
                ['extendedMethod']
            ],
            [
                new ClassImplementsInterface(),
                InterfaceForClass::class,
                ['interfaceMethod'],
                ['additionalMethod']
            ],
            [
                new ExtendsAndImplementsClass(),
                ExtendsAndImplementsClass::class,
                ['extendsMethod', 'interfaceMethod1', 'interfaceMethod2', 'abstractMethod1', 'abstractMethod2'],
                []
            ],
            [
                new ExtendsAndImplementsClass(),
                AbstractBaseClass2::class,
                ['abstractMethod1', 'abstractMethod2'],
                ['extendsMethod', 'interfaceMethod1', 'interfaceMethod2']
            ],
            [
                new ExtendsAndImplementsClass(),
                AbstractBaseClass1::class,
                ['abstractMethod1'],
                ['abstractMethod2', 'extendsMethod', 'interfaceMethod1', 'interfaceMethod2']
            ],
            [
                new ExtendsAndImplementsClass(),
                ExtendsAndImplementsInterface1::class,
                ['interfaceMethod1'],
                ['abstractMethod1', 'abstractMethod2', 'extendsMethod', 'interfaceMethod2']
            ],
            [
                new ExtendsAndImplementsClass(),
                ExtendsAndImplementsInterface2::class,
                ['interfaceMethod1', 'interfaceMethod2'],
                ['abstractMethod1', 'abstractMethod2', 'extendsMethod']
            ],
            [
                new ClassWithPublicDestructor(),
                InterfaceForPublicDestructorClass::class,
                ['interfaceMethod'],
                ['additionalMethod'],
            ],
        ];
    }

    /**
     * @param string[] $allowedMethods
     * @param string[] $jailedMethods
     * @dataProvider getInstanceScenarios
     */
    public function testJailScenarios(object $instance, string $class, array $allowedMethods, array $jailedMethods): void
    {
        $proxy = $this->factory->createInstanceJail($instance, $class);
        self::assertIsObject($proxy);
        static::assertProxyInstanceOf($proxy, get_class($instance), $class);

        $this->assertMethodsCalls($proxy, $allowedMethods, $jailedMethods);
    }

    public static function getAggregateScenarios(): array
    {
        $newParamList = [];

        $paramList = static::getInstanceScenarios();
        foreach ($paramList as $params) {
            $listParams = $params;
            $listParams[0] = [$listParams[0], $listParams[0], $listParams[0]];
            $traversableParams = $listParams;
            $traversableParams[0] = new ArrayIterator($traversableParams[0]);
            $newParamList[] = $listParams;
            $newParamList[] = $traversableParams;
        }

        return $newParamList;
    }

    /**
     * @param object[] $list
     * @param string[] $allowedMethods
     * @param string[] $jailedMethods
     * @dataProvider getAggregateScenarios
     */
    public function testJailAggregate(iterable $list, string $class, array $allowedMethods, array $jailedMethods): void
    {
        $proxies = $this->factory->createAggregateJail($list, $class);
        foreach ($proxies as $proxy) {
            $this->assertMethodsCalls($proxy, $allowedMethods, $jailedMethods);
        }
    }

    public static function getHierarchyScenarios(): array
    {
        return [
            [new ArrayIterator(), stdClass::class],
            [new BaseClass(), ExtendsAndImplementsInterface1::class],
        ];
    }

    /**
     * @dataProvider getHierarchyScenarios
     */
    public function testInvalidInheritanceHierarchy(object $instance, string $class): void
    {
        $this->expectException(HierarchyException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot create proxy for "%1$s" as "%2$s" is not part of the inheritance hierarchy of "%1$s". ',
                get_class($instance),
                $class
            )
        );

        $this->factory->createInstanceJail($instance, $class);
    }

    public function testCreateProxyFromAJail(): void
    {
        $proxy = $this->factory->createInstanceJail(new BaseClass(), BaseClass::class);
        self::assertSame($proxy, $this->factory->createInstanceJail($proxy, BaseClass::class));
    }

    public function testCreateProxyFromAProxy(): void
    {
        $proxy = $this->createMock(ProxyInterface::class);
        self::assertSame($proxy, $this->factory->createInstanceJail($proxy, ProxyInterface::class));
    }

    private function assertMethodsCalls(ProxyInterface $proxy, array $allowedMethods, array $jailedMethods): void
    {
        foreach ($allowedMethods as $allowedMethod) {
            self::assertSame($allowedMethod, $proxy->{$allowedMethod}());
        }

        foreach ($jailedMethods as $jailedMethod) {
            static::assertJailedMethod($proxy, $jailedMethod);
        }
    }

    protected static function assertProxyInstanceOf(ProxyInterface $proxy, string $baseClass, string $superClass): void
    {
        self::assertInstanceOf($superClass, $proxy);

        if ($baseClass !== $superClass) {
            self::assertNotInstanceOf($baseClass, $proxy);
        }
    }

    protected static function assertJailedMethod(ProxyInterface $proxy, string $method): void
    {
        try {
            $proxy->{$method}();
            self::fail('Expected exception');
        } catch (JailException $e) {
            self::assertInstanceOf(ExceptionInterface::class, $e);
        }
    }
}
