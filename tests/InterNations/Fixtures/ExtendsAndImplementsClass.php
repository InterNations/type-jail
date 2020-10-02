<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

interface ExtendsAndImplementsInterface2 extends ExtendsAndImplementsInterface1
{
    public function interfaceMethod2(): string;
}

interface ExtendsAndImplementsInterface1
{
    public function interfaceMethod1(): string;
}

abstract class AbstractBaseClass1
{
    abstract public function abstractMethod1(): string;
}

abstract class AbstractBaseClass2 extends AbstractBaseClass1
{
    public function abstractMethod1(): string
    {
        return __FUNCTION__;
    }

    abstract public function abstractMethod2();
}

class ExtendsAndImplementsClass extends AbstractBaseClass2 implements ExtendsAndImplementsInterface2
{
    public function extendsMethod(): string
    {
        return __FUNCTION__;
    }

    public function interfaceMethod1(): string
    {
        return __FUNCTION__;
    }

    public function interfaceMethod2(): string
    {
        return __FUNCTION__;
    }

    public function abstractMethod2(): string
    {
        return __FUNCTION__;
    }
}
