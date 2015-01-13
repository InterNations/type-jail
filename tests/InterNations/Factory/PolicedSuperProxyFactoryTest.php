<?php
namespace InterNations\Component\TypePolice\Tests\Factory;

use InterNations\Component\TypePolice\Factory\PolicedSuperProxyFactory;

class PolicedSuperProxyFactoryTest extends AbstractProxyFactoryTest
{
    public function setUp()
    {
        $this->factory = new PolicedSuperProxyFactory();
    }
}
