<?php
namespace InterNations\Component\TypePolice\Tests\Factory;

use ArrayIterator;
use InterNations\Component\TypePolice\Exception\BadMethodCallException;
use InterNations\Component\TypePolice\Exception\ExceptionInterface;
use InterNations\Component\TypePolice\Exception\HierarchyException;
use InterNations\Component\TypePolice\Exception\InvalidArgumentException;
use InterNations\Component\TypePolice\Factory\PolicedProxyFactory;
use InterNations\Component\TypePolice\Factory\PolicedProxyFactoryInterface;
use InterNations\Component\TypePolice\Tests\Fixtures\AbstractBaseClass;
use InterNations\Component\TypePolice\Tests\Fixtures\AbstractBaseClass1;
use InterNations\Component\TypePolice\Tests\Fixtures\AbstractBaseClass2;
use InterNations\Component\TypePolice\Tests\Fixtures\BaseClass;
use InterNations\Component\TypePolice\Tests\Fixtures\ClassImplementsInterface;
use InterNations\Component\TypePolice\Tests\Fixtures\ExtendsAndImplementsClass;
use InterNations\Component\TypePolice\Tests\Fixtures\ExtendsAndImplementsInterface1;
use InterNations\Component\TypePolice\Tests\Fixtures\ExtendsAndImplementsInterface2;
use InterNations\Component\TypePolice\Tests\Fixtures\ExtendsClass;
use InterNations\Component\TypePolice\Tests\Fixtures\InterfaceForClass;
use InterNations\Component\Testing\AbstractTestCase;
use stdClass;

class PolicedProxyFactoryTest extends AbstractTestCase
{
    /** @var PolicedProxyFactoryInterface */
    private $factory;

    public function setUp()
    {
        $this->factory = new PolicedProxyFactory();
    }

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
        ];
    }

    /**
     * @param object $instance
     * @param string $class
     * @param array $allowedMethods
     * @param array $policedMethods
     * @dataProvider getInstanceScenarios
     */
    public function testPolicingScenarios($instance, $class, array $allowedMethods, array $policedMethods)
    {
        $proxy = $this->factory->policeInstance($instance, $class);
        $this->assertInternalType('object', $proxy);
        $this->assertInstanceOf($class, $proxy);

        if (get_class($instance) !== $class) {
            $this->assertNotInstanceOf(get_class($instance), $proxy);
        }

        $this->assertMethodsCalls($proxy, $allowedMethods, $policedMethods);
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
     * @param array $policedMethods
     * @dataProvider getAggregateScenarios
     */
    public function testPoliceAggregate($list, $class, array $allowedMethods, array $policedMethods)
    {
        $proxies = $this->factory->policeAggregate($list, $class);
        foreach ($proxies as $proxy) {
            $this->assertMethodsCalls($proxy, $allowedMethods, $policedMethods);
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
                'Cannot create policed proxy for "%1$s" as "%2$s" is not part of the inheritance hierarchy of "%1$s". ',
                get_class($instance),
                $class
            )
        );

        $this->factory->policeInstance($instance, $class);
    }

    public function testInvalidTypeForAggregate()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected type to be one of "array", "Traversable", got "stdClass"'
        );
        $this->factory->policeAggregate(new stdClass(), stdClass::class);
    }

    public function testInvalidTypeForAggregateClass()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected type to be "string", got "array"'
        );
        $this->factory->policeAggregate([], []);
    }

    public function testInvalidTypeForSingleInstance()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected type to be "object", got "array"'
        );
        $this->factory->policeInstance([], stdClass::class);
    }

    public function testInvalidTypeForSingleInstanceClass()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected type to be "string", got "array"'
        );
        $this->factory->policeInstance(new stdClass(), []);
    }

    private function assertMethodsCalls($proxy, array $allowedMethods, array $policedMethods)
    {
        foreach ($allowedMethods as $allowedMethod) {
            $this->assertSame($allowedMethod, $proxy->{$allowedMethod}());
        }

        foreach ($policedMethods as $policedMethod) {
            try {
                $proxy->{$policedMethod}();
                $this->fail('Expected exception');
            } catch (BadMethodCallException $e) {
                $this->assertInstanceOf(ExceptionInterface::class, $e);
            }
        }
    }
}
