<?php
namespace InterNations\Component\TypePolice\Exception;

use InterNations\Component\TypePolice\Util\TypeUtil;
use ReflectionClass;

class HierarchyException extends RuntimeException
{
    public static function hierarchyMismatch(ReflectionClass $class, ReflectionClass $superClass)
    {
        return new static(
            sprintf(
                'Cannot create policed proxy for "%1$s" as "%2$s" is not part of the inheritance hierarchy of "%1$s". '
                . 'Valid supertypes are: "%3$s"',
                $class->getName(),
                $superClass->getName(),
                implode('", "', TypeUtil::getSuperTypeNames($class))
            )
        );
    }
}
