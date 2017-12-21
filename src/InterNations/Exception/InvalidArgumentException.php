<?php
namespace InterNations\Component\TypeJail\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $given
     * @param string|array $expected
     */
    public static function invalidType($given, $expected): self
    {
        return new static(
            sprintf(
                'Expected type to be %s"%s", got "%s"',
                is_array($expected) ? 'one of ' : '',
                implode('", "', (array) $expected),
                is_object($given) ? get_class($given) : gettype($given)
            )
        );
    }
}
