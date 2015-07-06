<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

interface InterfaceForPublicDestructorClass
{
    public function interfaceMethod();
}

class ClassWithPublicDestructor implements InterfaceForPublicDestructorClass
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
