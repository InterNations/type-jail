<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

interface InterfaceForClass
{
    public function interfaceMethod();
}

class ClassImplementsInterface implements InterfaceForClass
{
    public function interfaceMethod(): string
    {
        return __FUNCTION__;
    }

    public function additionalMethod(): string
    {
        return __FUNCTION__;
    }
}
