<?php
namespace InterNations\Component\TypeJail\Tests\Factory;

use InterNations\Component\TypeJail\Factory\SuperTypeFactory;
use ProxyManager\Proxy\ProxyInterface;

class SuperTypeFactoryTest extends AbstractJailFactoryTest
{
    protected function setUp(): void
    {
        $this->factory = new SuperTypeFactory();
    }

    protected static function assertJailedMethod(ProxyInterface $proxy, string $method): void
    {
        self::assertFalse(method_exists($proxy, $method), 'Method "' . $method . '" should not exist');
    }
}
