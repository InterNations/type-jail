<?php
namespace InterNations\Component\TypePolice\Tests\Factory;

use InterNations\Component\TypePolice\Factory\SuperProxyFactory;

class SuperProxyFactoryTest extends AbstractProxyFactoryTest
{
    public function setUp()
    {
        $this->factory = new SuperProxyFactory();
    }

    protected function assertPolicedMethod($proxy, $method)
    {
        $this->assertFalse(method_exists($proxy, $method), 'Method "' . $method . '" should not exist');
    }
}
