<?php
namespace InterNations\Component\TypeJail\Inflector;

use ProxyManager\Inflector\Util\ParameterEncoder;

class HashedParameterEncoder extends ParameterEncoder
{
    public function encodeParameters(array $parameters)
    {
        return 'P' . hash('sha256', serialize($parameters));
    }
}
