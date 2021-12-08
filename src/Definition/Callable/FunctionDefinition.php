<?php

declare(strict_types=1);

namespace Loner\Container\Definition\Callable;

use Closure;
use Loner\Container\Collector\ReflectionCollector;
use Loner\Container\ContainerInterface;
use Loner\Container\Exception\{DefinedException, ReflectedException};
use ReflectionFunction;

/**
 * 基于【函数名/闭包】的依赖定义
 *
 * @package Loner\Container\Definition\Callable
 */
class FunctionDefinition implements CallableDefinitionInterface
{
    use Caller;

    /**
     * 完全定位名称
     *
     * @var string
     */
    private string $declaring;

    /**
     * 主反射
     *
     * @var ReflectionFunction
     */
    private ReflectionFunction $reflection;

    /**
     * @inheritDoc
     */
    public function declaring(): string
    {
        return $this->declaring ??= $this->reflection->name;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$parameters = []): mixed
    {
        $dependencies = $this->resolveDependencies($container, $parameters);
        return $this->reflection->invokeArgs($dependencies);
    }

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
     * 主调用反射
     *
     * @return ReflectionFunction
     */
    private function caller(): ReflectionFunction
    {
        return $this->reflection;
    }
}
