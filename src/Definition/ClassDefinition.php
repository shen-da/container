<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use Loner\Container\Collector\ReflectionCollector;
use Loner\Container\ContainerInterface;
use Loner\Container\Exception\{DefinedException, ReflectedException, ResolvedException};
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * 基于【类名】的依赖定义
 *
 * @package Loner\Container\Definition
 */
class ClassDefinition implements DefinitionInterface
{
    use Caller;

    /**
     * 主反射
     *
     * @var ReflectionClass
     */
    private ReflectionClass $reflection;

    /**
     * 构造函数反射
     *
     * @var ReflectionMethod|null
     */
    private ?ReflectionMethod $constructor;

    /**
     * 定义基础分析
     *
     * @param string $class
     * @throws DefinedException
     */
    public function __construct(string $class)
    {
        try {
            $this->reflection = ReflectionCollector::getClass($class);
            $this->constructor = $this->reflection->getConstructor();
        } catch (ReflectedException $e) {
            throw new DefinedException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 获取主调用反射
     *
     * @return ReflectionMethod|null
     */
    public function caller(): ?ReflectionMethod
    {
        return $this->constructor;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$arguments = []): object
    {
        $reflection = $this->reflection;

        if ($this->constructor === null) {
            try {
                return $reflection->newInstanceWithoutConstructor();
            } catch (ReflectionException) {
                throw new ResolvedException(sprintf(
                    'Class[%s] is an internal class that cannot be instantiated without invoking the constructor.',
                    $reflection->name
                ));
            }
        }

        $dependencies = $this->resolveDependencies($container, $arguments);

        try {
            return $reflection->newInstanceArgs($dependencies);
        } catch (ReflectionException) {
            throw new ResolvedException(sprintf('Class[%s] constructor is not public.', $reflection->name));
        }
    }
}
