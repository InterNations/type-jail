<?php
namespace InterNations\Component\TypePolice\Factory;

use InterNations\Component\TypePolice\Exception\ExceptionInterface;
use InterNations\Component\TypePolice\Exception\HierarchyException;

interface ProxyFactoryInterface
{
    /**
     * Police single instance
     *
     * Returns a proxy with all the methods policed that are not part of the contract of $class. For every method
     * that is invoked on the proxy that does not belong to the contract of $class an exception will be thrown
     *
     * @param object $instance
     * @param string $class
     * @throws ExceptionInterface Any TypePolice exception
     * @throws HierarchyException If a hierarchy error occurs
     * @return object Object of type instance with method calls that vio
     */
    public function policeInstance($instance, $class);

    /**
     * Create policed proxies for an aggregate
     *
     * @param object[] $instanceAggregate
     * @param string $class
     * @return object[]
     */
    public function policeAggregate($instanceAggregate, $class);
}
