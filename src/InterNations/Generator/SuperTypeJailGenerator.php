<?php
namespace InterNations\Component\TypeJail\Generator;

use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

class SuperTypeJailGenerator implements ProxyGeneratorInterface
{
    public function generate(
        ReflectionClass $originalClass,
        ClassGenerator $classGenerator,
        ReflectionClass $superClass = null
    )
    {
        $publicProperties = new PublicPropertiesMap($originalClass);
        $interfaces = [
            'ProxyManager\\Proxy\\AccessInterceptorInterface',
            'ProxyManager\\Proxy\\ValueHolderInterface',
        ];
        $superClass = $superClass ?: $originalClass;

        if ($superClass->isInterface()) {
            $interfaces[] = $superClass->getName();
        } else {
            $classGenerator->setExtendedClass($superClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder = new ValueHolderProperty());
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        foreach (ProxiedMethodsFilter::getProxiedMethods($originalClass) as $method) {
            $classGenerator->addMethodFromGenerator(
                InterceptedMethod::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                    $valueHolder,
                    $prefixInterceptors,
                    $suffixInterceptors
                )
            );
        }

        $classGenerator->addMethodFromGenerator(
            new Constructor($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(new GetWrappedValueHolderValue($valueHolder));
        $classGenerator->addMethodFromGenerator(new SetMethodPrefixInterceptor($prefixInterceptors));
        $classGenerator->addMethodFromGenerator(new SetMethodSuffixInterceptor($suffixInterceptors));
    }
}
