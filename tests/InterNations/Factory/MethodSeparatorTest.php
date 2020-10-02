<?php
namespace InterNations\Component\TypeJail\Tests\Factory;

use InterNations\Component\TypeJail\Factory\MethodSeparator;
use InterNations\Component\TypeJail\Tests\Fixtures\BaseClass;
use InterNations\Component\TypeJail\Tests\Fixtures\ClassImplementsInterface;
use InterNations\Component\TypeJail\Tests\Fixtures\AbstractBaseClass;
use InterNations\Component\TypeJail\Tests\Fixtures\ClassWithPublicDestructor;
use InterNations\Component\TypeJail\Tests\Fixtures\ExtendsClass;
use InterNations\Component\TypeJail\Tests\Fixtures\InterfaceForClass;
use InterNations\Component\Testing\AbstractTestCase;
use InterNations\Component\TypeJail\Tests\Fixtures\InterfaceForPublicDestructorClass;
use ReflectionClass;

class MethodSeparatorTest extends AbstractTestCase
{
    private MethodSeparator $separator;

    protected function setUp(): void
    {
        $this->separator = new MethodSeparator();
    }

    public static function getImplementations(): iterable
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
            ],
            [
                ClassWithPublicDestructor::class,
                InterfaceForPublicDestructorClass::class,
                [['additionalMethod'], ['__destruct', 'interfaceMethod']],
            ],
        ];
    }

    /**
     * @param array $expectatios
     * @dataProvider getImplementations
     */
    public function testMethodSeparation(string $childClass, string $parentClass, array $expectations): void
    {
        self::assertEquals($expectations, $this->separator->separateMethods(new ReflectionClass($childClass), new ReflectionClass($parentClass)));
    }
}
