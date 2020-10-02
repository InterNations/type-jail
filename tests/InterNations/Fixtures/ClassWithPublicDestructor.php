<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

interface InterfaceForPublicDestructorClass
{
    public function interfaceMethod();
}

class ClassWithPublicDestructor implements InterfaceForPublicDestructorClass
{
    public function interfaceMethod(): string
    {
        return __FUNCTION__;
    }

    public function additionalMethod(): string
    {
        return __FUNCTION__;
    }

    public function __destruct()
    {
    }
}
