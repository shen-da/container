<?php

declare(strict_types=1);

namespace Loner\Container\Collector;

use Closure;
use Loner\Container\Exception\DefinedException;
use Loner\Container\Definition\Callable\{
    CallableDefinitionInterface,
    ClassDefinition,
    FunctionDefinition,
    MethodDefinition
};

/**
 * 依赖定义收集器
 *
 * @package Loner\Container\Collector
 */
class DefinitionCollector
{
    /**
     * 【类名】定义库
     *
     * @var ClassDefinition[] [$className => ClassDefinition]
     */
    private static array $classDefinitions = [];

    /**
     * 【类名::方法名】定义库
     *
     * @var MethodDefinition[][] [$className => [$methodName => MethodDefinition]]
     */
    private static array $methodDefinitions = [];

    /**
     * 【函数名】定义库
     *
     * @var FunctionDefinition[] [$functionName => FunctionDefinition]
     */
    private static array $functionDefinitions = [];

    /**
     * 创建定义
     *
     * @param Closure|string $source
     * @return CallableDefinitionInterface
     * @throws DefinedException
     */
    public static function make(Closure|string $source): CallableDefinitionInterface
    {
        return $source instanceof Closure ? self::getFunction($source) : self::get($source);
    }

    /**
     * 创建并返回定义，或返回异常码
     *
     * @param Closure|string $source
     * @return CallableDefinitionInterface|DefinedException
     */
    public static function makeSafely(Closure|string $source): CallableDefinitionInterface|DefinedException
    {
        try {
            return self::make($source);
        } catch (DefinedException $e) {
            return $e;
        }
    }

    /**
     * 获取标识符定义
     *
     * @param string $source
     * @return CallableDefinitionInterface
     * @throws DefinedException
     */
    public static function get(string $source): CallableDefinitionInterface
    {
        if (str_contains($source, '::')) {
            return self::getMethod(...explode('::', $source, 2));
        }
        return function_exists($source) ? self::getFunction($source) : self::getClass($source);
    }

    /**
     * 获取指定【类名】的定义
     *
     * @param string $class
     * @return ClassDefinition
     * @throws DefinedException
     */
    public static function getClass(string $class): ClassDefinition
    {
        return self::$classDefinitions[$class] ??= new ClassDefinition($class);
    }

    /**
     * 获取指定【类名::方法名】的定义
     *
     * @param string $class
     * @param string $method
     * @return MethodDefinition
     * @throws DefinedException
     */
    public static function getMethod(string $class, string $method): MethodDefinition
    {
        return self::$methodDefinitions[$class][$method] ??= new MethodDefinition($class, $method);
    }

    /**
     * 获取指定【闭包/函数名】的定义
     *
     * @param Closure|string $function
     * @return FunctionDefinition
     * @throws DefinedException
     */
    public static function getFunction(Closure|string $function): FunctionDefinition
    {
        return is_string($function)
            ? self::$functionDefinitions[$function] ??= new FunctionDefinition($function)
            : new FunctionDefinition($function);
    }
}
