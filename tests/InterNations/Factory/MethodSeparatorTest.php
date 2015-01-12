<?php
namespace InterNations\Component\TypePolice\Tests\Factory;

use InterNations\Component\TypePolice\Factory\MethodSeparator;
use InterNations\Component\TypePolice\Tests\Fixtures\BaseClass;
use InterNations\Component\TypePolice\Tests\Fixtures\ClassImplementsInterface;
use InterNations\Component\TypePolice\Tests\Fixtures\AbstractBaseClass;
use InterNations\Component\TypePolice\Tests\Fixtures\ExtendsClass;
use InterNations\Component\TypePolice\Tests\Fixtures\InterfaceForClass;
use InterNations\Component\Testing\AbstractTestCase;
use ReflectionClass;

class MethodSeparatorTest extends AbstractTestCase
{
    /** @var MethodSeparator */
    private $separator;

    public function setUp()
    {
        $this->separator = new MethodSeparator();
    }

    public static function getImplementations()
    {
        return [
            [
                BaseClass::class,
                BaseClass::class,
                [[], ['baseMethod', 'staticBaseMethod']]
            ],
            [
                ExtendsClass::class,
                AbstractBaseClass::class,
                [
                    ['extendedMethod'],
                    ['abstractBaseMethod', 'baseMethod', 'staticBaseMethod'],
                ]
            ],
            [
                ClassImplementsInterface::class,
                InterfaceForClass::class,
                [['additionalMethod'], ['interfaceMethod']]
            ]
        ];
    }

    /**
     * @param string $childClass
     * @param string $parentClass
     * @param array $expectation
     * @dataProvider getImplementations
     */
    public function testMethodSeparation($childClass, $parentClass, array $expectation)
    {
        $this->assertEquals($expectation, $this->separator->separateMethods(new ReflectionClass($childClass), new ReflectionClass($parentClass)));
    }
}
