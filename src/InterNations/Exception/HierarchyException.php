<?php
namespace InterNations\Component\TypeJail\Exception;

use InterNations\Component\TypeJail\Util\TypeUtil;
use ReflectionClass;

class HierarchyException extends RuntimeException
{
    public static function hierarchyMismatch(ReflectionClass $class, ReflectionClass $superClass): self
    {
        return new self(
            sprintf(
                'Cannot create proxy for "%1$s" as "%2$s" is not part of the inheritance hierarchy of "%1$s". '
                . 'Valid supertypes are: "%3$s"',
                $class->getName(),
                $superClass->getName(),
                implode('", "', TypeUtil::getSuperTypeNames($class))
            )
        );
    }
}
