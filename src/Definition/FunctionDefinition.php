<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use Closure;
use Loner\Container\Collector\ReflectionCollector;
use Loner\Container\ContainerInterface;
use Loner\Container\Exception\{DefinedException, ReflectedException};
use ReflectionFunction;

/**
 * 基于【函数名/闭包】的依赖定义
 *
 * @package Loner\Container\Definition
 */
class FunctionDefinition implements DefinitionInterface
{
    use Caller;

    /**
     * 主反射
     *
     * @var ReflectionFunction
     */
    private ReflectionFunction $reflection;

    /**
     * 定义基础分析
     *
     * @param Closure|string $function
     * @throws DefinedException
     */
    public function __construct(Closure|string $function)
    {
        try {
            $this->reflection = ReflectionCollector::getFunction($function);
        } catch (ReflectedException $e) {
            throw new DefinedException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 获取主反射
     *
     * @return ReflectionFunction
     */
    public function caller(): ReflectionFunction
    {
        return $this->reflection;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$parameters = []): mixed
    {
        $dependencies = $this->resolveDependencies($container, $parameters);
        return $this->reflection->invokeArgs($dependencies);
    }
}
