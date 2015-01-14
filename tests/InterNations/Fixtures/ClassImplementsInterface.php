<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

interface InterfaceForClass
{
    public function interfaceMethod();
}

class ClassImplementsInterface implements InterfaceForClass
{
    public function interfaceMethod()
    {
        return __FUNCTION__;
    }

    public function additionalMethod()
    {
        return __FUNCTION__;
    }
}
