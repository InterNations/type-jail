<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

abstract class AbstractBaseClass
{
    public function baseMethod()
    {
        return __FUNCTION__;
    }

    abstract public function abstractBaseMethod();

    public static function staticBaseMethod()
    {
        return __FUNCTION__;
    }

    private static function privateStaticBaseMethod()
    {
        return __FUNCTION__;
    }
}

class ExtendsClass extends AbstractBaseClass
{
    public function extendedMethod()
    {
        return __FUNCTION__;
    }

    public function abstractBaseMethod()
    {
        return __FUNCTION__;
    }
}
