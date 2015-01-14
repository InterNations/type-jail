<?php
namespace InterNations\Component\TypeJail\Tests\Fixtures;

class BaseClass
{
    public function baseMethod()
    {
        return __FUNCTION__;
    }

    public static function staticBaseMethod()
    {
        return __FUNCTION__;
    }

    protected function protectedBaseMethod()
    {
    }

    private function privateBaseMethod()
    {
    }
}
