<?php
namespace InterNations\Component\TypeJail\Tests\Factory;

use InterNations\Component\TypeJail\Factory\SuperTypeFactory;

class SuperTypeFactoryTest extends AbstractJailFactoryTest
{
    protected function setUp(): void
    {
        $this->factory = new SuperTypeFactory();
    }

    protected function assertJailedMethod($proxy, $method)
    {
        $this->assertFalse(method_exists($proxy, $method), 'Method "' . $method . '" should not exist');
    }
}
