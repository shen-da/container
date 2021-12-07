<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

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
 * @package Loner\Container\Definition
 */
class MethodDefinition implements DefinitionInterface
{
    use Caller;

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
    private ?object $object;

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
            $this->isStatic = $this->reflection->isStatic();
        } catch (ReflectedException $e) {
            throw new DefinedException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 获取主调用反射
     *
     * @return ReflectionMethod
     */
    public function caller(): ReflectionMethod
    {
        return $this->reflection;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$parameters = []): mixed
    {
        $dependencies = $this->resolveDependencies($container, $parameters);
        $object = $this->getObject($container);

        try {
            return $this->reflection->invokeArgs($object, $dependencies);
        } catch (ReflectionException) {
            throw new ResolvedException(sprintf(
                'Method[%s::%s] invocation failed.',
                $this->reflection->class, $this->reflection->name
            ));
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
        if (!$this->isStatic) {
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
        if ($this->isStatic) {
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
}
