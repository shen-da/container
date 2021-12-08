<?php

declare(strict_types=1);

namespace Loner\Container\Definition\Callable;

use Loner\Container\Collector\ReflectionCollector;
use Loner\Container\Exception\{
    ContainerException,
    DefinedException,
    NotFoundException,
    ReflectedException,
    ResolvedException
};
use Loner\Container\ContainerInterface;
use ReflectionException;
use ReflectionMethod;

/**
 * 基于【类名+方法名】的依赖定义
 *
 * @package Loner\Container\Definition\Callable
 */
class MethodDefinition implements CallableDefinitionInterface
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
     * @var ReflectionMethod
     */
    private ReflectionMethod $reflection;

    /**
     * 是否静态
     *
     * @var bool
     */
    private bool $isStatic;

    /**
     * 临时对象
     *
     * @var object|null
     */
    private ?object $object = null;

    /**
     * @inheritDoc
     */
    public function declaring(): string
    {
        return $this->declaring ??= $this->reflection->class . '::' . $this->reflection->name;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$parameters = []): mixed
    {
        $object = $this->getObject($container);
        $dependencies = $this->resolveDependencies($container, $parameters);

        try {
            return $this->reflection->invokeArgs($object, $dependencies);
        } catch (ReflectionException) {
            throw new ResolvedException($this->declaring(), ResolvedException::METHOD_INVOCATION_FAILED);
        }
    }

    /**
     * 定义基础分析
     *
     * @param string $class
     * @param string $method
     * @throws DefinedException
     */
    public function __construct(string $class, string $method)
    {
        try {
            $this->reflection = ReflectionCollector::getMethod($class, $method);
        } catch (ReflectedException $e) {
            throw new DefinedException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 设置临时对象（非静态类方法有效）
     *
     * @param object $object
     * @return $this
     */
    public function setObject(object $object): self
    {
        if (!$this->isStatic()) {
            $this->object = $object;
        }
        return $this;
    }

    /**
     * 获取依赖对象
     *
     * @param ContainerInterface $container
     * @return object|null
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function getObject(ContainerInterface $container): ?object
    {
        if ($this->isStatic()) {
            return null;
        }

        if ($this->object) {
            $object = $this->object;
            $this->object = null;
        } else {
            $object = $container->get($this->reflection->class);
        }

        return $object;
    }

    /**
     * 返回是否静态函数
     *
     * @return bool
     */
    private function isStatic(): bool
    {
        return $this->isStatic ??= $this->reflection->isStatic();
    }

    /**
     * 主调用反射
     *
     * @return ReflectionMethod
     */
    private function caller(): ReflectionMethod
    {
        return $this->reflection;
    }
}
