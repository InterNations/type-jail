<?php
namespace InterNations\Component\TypeJail\Inflector;

use ProxyManager\Inflector\ClassNameInflector as BaseClassNameInflector;

class ClassNameInflector extends BaseClassNameInflector
{
    private $parameterEncoder;

    private $proxyMarker;

    public function __construct($proxyNamespace)
    {
        parent::__construct($proxyNamespace);
        $this->proxyMarker = '\\' . static::PROXY_MARKER . '\\';
        $this->parameterEncoder = new HashedParameterEncoder();
    }

    public function getProxyClassName($className, array $options = [])
    {
        return $this->proxyNamespace
            . $this->proxyMarker
            . $this->getUserClassName($className)
            . '\\' . $this->parameterEncoder->encodeParameters($options);
    }
}
