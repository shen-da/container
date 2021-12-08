<?php

declare(strict_types=1);

namespace Loner\Container\Collector;

use Closure;
use Loner\Container\Exception\ReflectedException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

/**
 * 反射收集器
 *
 * @package Loner\Container\Collector
 */
class ReflectionCollector
{
    /**
     * 类型反射库
     *
     * @var ReflectionClass[] [$className => ReflectionClass]
     */
    protected static array $classReflections = [];

    /**
     * 类方法反射库
     *
     * @var ReflectionMethod[][] [$className => [$methodName => ReflectionMethod]]
     */
    protected static array $methodReflections = [];

    /**
     * 类属性反射库
     *
     * @var ReflectionProperty[][] [$className => [$propertyName => ReflectionProperty]]
     */
    protected static array $propertyReflections = [];

    /**
     * 函数反射库
     *
     * @var ReflectionFunction[] [$functionName => ReflectionFunction]
     */
    protected static array $functionReflections = [];

    /**
     * 类反射
     *
     * @param string $class
     * @return ReflectionClass
     * @throws ReflectedException
     */
    public static function getClass(string $class): ReflectionClass
    {
        try {
            return self::$classReflections[$class] ??= new ReflectionClass($class);
        } catch (ReflectionException) {
            throw new ReflectedException($class, ReflectedException::NOT_REFLECTIVE_CLASS);
        }
    }

    /**
     * 类方法反射
     *
     * @param string $class
     * @param string $method
     * @return ReflectionMethod
     * @throws ReflectedException
     */
    public static function getMethod(string $class, string $method): ReflectionMethod
    {
        try {
            return self::$methodReflections[$class][$method] ??= self::getClass($class)->getMethod($method);
        } catch (ReflectionException) {
            throw new ReflectedException($class . '::' . $method, ReflectedException::METHOD_NOT_EXIST);
        }
    }

    /**
     * 类属性反射
     *
     * @param string $class
     * @param string $property
     * @return ReflectionProperty
     * @throws ReflectedException
     */
    public static function getProperty(string $class, string $property): ReflectionProperty
    {
        try {
            return self::$propertyReflections[$class][$property] ??= self::getClass($class)->getProperty($property);
        } catch (ReflectionException) {
            throw new ReflectedException($class . '::$' . $property, ReflectedException::PROPERTY_NOT_EXIST);
        }
    }

    /**
     * 类方法反射
     *
     * @param Closure|string $function
     * @return ReflectionFunction
     * @throws ReflectedException
     */
    public static function getFunction(Closure|string $function): ReflectionFunction
    {
        try {
            return is_string($function)
                ? self::$functionReflections[$function] ??= new ReflectionFunction($function)
                : new ReflectionFunction($function);
        } catch (ReflectionException) {
            throw new ReflectedException($function, ReflectedException::FUNCTION_NOT_EXIST);
        }
    }
}
