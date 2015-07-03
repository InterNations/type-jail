<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

class ClassWithPublicDestructor implements InterfaceForClass
{
    public function interfaceMethod()
    {
        return __FUNCTION__;
    }

    public function additionalMethod()
    {
        return __FUNCTION__;
    }

    public function __destruct()
    {
    }
}
