<?php
namespace InterNations\Component\TypeJail\Factory;

use InterNations\Component\TypeJail\Exception\ExceptionInterface;
use InterNations\Component\TypeJail\Exception\HierarchyException;

interface JailFactoryInterface
{
    /**
     * Create single instance jail
     *
     * Returns a jailed proxy of the given type
     *
     * @param object $instance
     * @throws ExceptionInterface Any TypeJail exception
     * @throws HierarchyException If a hierarchy error occurs
     * @return object Object of type instance with method calls that vio
     */
    public function createInstanceJail($instance, string $class);

    /**
     * Create jails for an aggregate
     *
     * @param object[] $instanceAggregate
     * @return object[]
     */
    public function createAggregateJail(iterable $instanceAggregate, string $class): array;
}
