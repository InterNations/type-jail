<?php
namespace InterNations\Component\TypeJail\Tests\Factory;

use InterNations\Component\TypeJail\Factory\JailFactory;

class JailFactoryTest extends AbstractJailFactoryTest
{
    public function setUp()
    {
        $this->factory = new JailFactory();
    }

    protected function assertProxyInstanceOf($proxy, $baseClass, $superClass)
    {
        $this->assertInstanceOf($superClass, $proxy);
        $this->assertInstanceOf($baseClass, $proxy);
    }
}
