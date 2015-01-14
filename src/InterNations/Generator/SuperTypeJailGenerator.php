<?php
namespace InterNations\Component\TypeJail\Generator;

use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicClone;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\MagicSleep;
use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\MethodReflection;

class SuperTypeJailGenerator implements ProxyGeneratorInterface
{
    public function generate(
        ReflectionClass $originalClass,
        ClassGenerator $classGenerator,
        ReflectionClass $superClass = null
    )
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

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

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator) {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    static function (ReflectionMethod $method) use (
                            $prefixInterceptors,
                            $suffixInterceptors,
                            $valueHolder
                        ) {
                        return InterceptedMethod::generateMethod(
                            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                            $valueHolder,
                            $prefixInterceptors,
                            $suffixInterceptors
                        );
                    },
                    ProxiedMethodsFilter::getProxiedMethods($originalClass)
                ),
                [
                    new Constructor($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors),
                    new GetWrappedValueHolderValue($valueHolder),
                    new SetMethodPrefixInterceptor($prefixInterceptors),
                    new SetMethodSuffixInterceptor($suffixInterceptors),
                    new MagicGet(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicSet(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicClone($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors),
                    new MagicSleep($originalClass, $valueHolder),
                    new MagicWakeup($originalClass, $valueHolder),
                ]
            )
        );
    }
}
