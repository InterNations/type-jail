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
use stdClass;

abstract class AbstractJailFactoryTest extends AbstractTestCase
{
    /** @var JailFactoryInterface */
    protected $factory;

    public static function getInstanceScenarios()
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
     * @param object $instance
     * @param string $class
     * @param array $allowedMethods
     * @param array $jailedMethods
     * @dataProvider getInstanceScenarios
     */
    public function testJailScenarios($instance, $class, array $allowedMethods, array $jailedMethods)
    {
        $proxy = $this->factory->createInstanceJail($instance, $class);
        $this->assertInternalType('object', $proxy);
        $this->assertProxyInstanceOf($proxy, get_class($instance), $class);

        $this->assertMethodsCalls($proxy, $allowedMethods, $jailedMethods);
    }

    public static function getAggregateScenarios()
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
     * @param string $class
     * @param array $allowedMethods
     * @param array $jailedMethods
     * @dataProvider getAggregateScenarios
     */
    public function testJailAggregate($list, $class, array $allowedMethods, array $jailedMethods)
    {
        $proxies = $this->factory->createAggregateJail($list, $class);
        foreach ($proxies as $proxy) {
            $this->assertMethodsCalls($proxy, $allowedMethods, $jailedMethods);
        }
    }

    public static function getHierarchyScenarios()
    {
        return [
            [new ArrayIterator(), stdClass::class],
            [new BaseClass(), ExtendsAndImplementsInterface1::class],
        ];
    }

    /**
     * @param object $instance
     * @param string $class
     * @dataProvider getHierarchyScenarios
     */
    public function testInvalidInheritanceHierarchy($instance, $class)
    {
        $this->setExpectedException(
            HierarchyException::class,
            sprintf(
                'Cannot create proxy for "%1$s" as "%2$s" is not part of the inheritance hierarchy of "%1$s". ',
                get_class($instance),
                $class
            )
        );

        $this->factory->createInstanceJail($instance, $class);
    }

    public function testInvalidTypeForAggregate()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected type to be one of "array", "Traversable", got "stdClass"'
        );
        $this->factory->createAggregateJail(new stdClass(), stdClass::class);
    }

    public function testInvalidTypeForAggregateClass()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected type to be "string", got "array"'
        );
        $this->factory->createAggregateJail([], []);
    }

    public function testInvalidTypeForSingleInstance()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected type to be "object", got "array"'
        );
        $this->factory->createInstanceJail([], stdClass::class);
    }

    public function testInvalidTypeForSingleInstanceClass()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected type to be "string", got "array"'
        );
        $this->factory->createInstanceJail(new stdClass(), []);
    }

    public function testCreateProxyFromAProxy()
    {
        $proxy = $this->factory->createInstanceJail(new BaseClass(), BaseClass::class);
        $this->assertNotNull($proxy, $this->factory->createInstanceJail($proxy, BaseClass::class));
    }

    private function assertMethodsCalls($proxy, array $allowedMethods, array $jailedMethods)
    {
        foreach ($allowedMethods as $allowedMethod) {
            $this->assertSame($allowedMethod, $proxy->{$allowedMethod}());
        }

        foreach ($jailedMethods as $jailedMethod) {
            $this->assertJailedMethod($proxy, $jailedMethod);
        }
    }

    protected function assertProxyInstanceOf($proxy, $baseClass, $superClass)
    {
        $this->assertInstanceOf($superClass, $proxy);

        if ($baseClass !== $superClass) {
            $this->assertNotInstanceOf($baseClass, $proxy);
        }
    }

    protected function assertJailedMethod($proxy, $method)
    {
        try {
            $proxy->{$method}();
            $this->fail('Expected exception');
        } catch (JailException $e) {
            $this->assertInstanceOf(ExceptionInterface::class, $e);
        }
    }
}
