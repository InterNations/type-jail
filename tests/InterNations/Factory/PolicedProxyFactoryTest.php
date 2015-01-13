<?php
namespace InterNations\Component\TypePolice\Tests\Factory;

use InterNations\Component\TypePolice\Factory\PolicedProxyFactory;

class ProxyFactoryTest extends AbstractProxyFactoryTest
{
    public function setUp()
    {
        $this->factory = new PolicedProxyFactory();
    }

    protected function assertProxyInstanceOf($proxy, $baseClass, $superClass)
    {
        $this->assertInstanceOf($superClass, $proxy);
        $this->assertInstanceOf($baseClass, $proxy);
    }
}
