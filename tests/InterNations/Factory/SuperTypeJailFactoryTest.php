<?php
namespace InterNations\Component\TypeJail\Tests\Factory;

use InterNations\Component\TypeJail\Factory\SuperTypeJailFactory;

class SuperTypeJailFactoryTest extends AbstractJailFactoryTest
{
    protected function setUp(): void
    {
        $this->factory = new SuperTypeJailFactory();
    }
}
