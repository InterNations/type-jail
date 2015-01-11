<?php
namespace InterNations\Component\TypePolice\Tests\Fixtures;

interface ExtendsAndImplementsInterface2 extends ExtendsAndImplementsInterface1
{
    public function interfaceMethod2();
}

interface ExtendsAndImplementsInterface1
{
    public function interfaceMethod1();
}

abstract class AbstractBaseClass1
{
    abstract public function abstractMethod1();
}

abstract class AbstractBaseClass2 extends AbstractBaseClass1
{
    public function abstractMethod1()
    {
        return __FUNCTION__;
    }

    abstract public function abstractMethod2();
}

class ExtendsAndImplementsClass extends AbstractBaseClass2 implements ExtendsAndImplementsInterface2
{
    public function extendsMethod()
    {
        return __FUNCTION__;
    }

    public function interfaceMethod1()
    {
        return __FUNCTION__;
    }

    public function interfaceMethod2()
    {
        return __FUNCTION__;
    }

    public function abstractMethod2()
    {
        return __FUNCTION__;
    }
}
