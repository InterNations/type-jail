<?php
namespace InterNations\Component\TypeJail\Tests\Factory;

use InterNations\Component\TypeJail\Factory\JailFactory;
use ProxyManager\Proxy\ProxyInterface;

class JailFactoryTest extends AbstractJailFactoryTest
{
    protected function setUp(): void
    {
        $this->factory = new JailFactory();
    }

    protected static function assertProxyInstanceOf(ProxyInterface $proxy, string $baseClass, string $superClass): void
    {
        self::assertInstanceOf($superClass, $proxy);
        self::assertInstanceOf($baseClass, $proxy);
    }
}
