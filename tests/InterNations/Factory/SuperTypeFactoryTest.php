<?php
namespace InterNations\Component\TypeJail\Tests\Factory;

use InterNations\Component\TypeJail\Factory\SuperTypeFactory;

class SuperTypeFactoryTest extends AbstractJailFactoryTest
{
    public function setUp()
    {
        $this->factory = new SuperTypeFactory($this->getConfiguration());
    }

    protected function assertJailedMethod($proxy, $method)
    {
        $this->assertFalse(method_exists($proxy, $method), 'Method "' . $method . '" should not exist');
    }
}
