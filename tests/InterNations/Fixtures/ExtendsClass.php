<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

abstract class AbstractBaseClass
{
    public function baseMethod(): string
    {
        return __FUNCTION__;
    }

    abstract public function abstractBaseMethod(): string;

    public static function staticBaseMethod(): string
    {
        return __FUNCTION__;
    }

    private static function privateStaticBaseMethod(): string
    {
        return __FUNCTION__;
    }
}

class ExtendsClass extends AbstractBaseClass
{
    public function extendedMethod(): string
    {
        return __FUNCTION__;
    }

    public function abstractBaseMethod(): string
    {
        return __FUNCTION__;
    }
}
