<?php

declare(strict_types=1);

namespace Loner\Container\Definition\Callable;

use Loner\Container\Attribute\Inject;
use Loner\Container\Collector\ReflectionCollector;
use Loner\Container\ContainerInterface;
use Loner\Container\Exception\{DefinedException, ReflectedException, ResolvedException};
use Loner\Container\Definition\PropertyDefinition;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * 基于【类名】的依赖定义
 *
 * @package Loner\Container\Definition\Callable
 */
class ClassDefinition implements CallableDefinitionInterface
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
     * @inheritDoc
     */
    public function declaring(): string
    {
        return $this->declaring ??= $this->reflection->name;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$parameters = []): object
    {
        if ($this->constructor === null) {
            try {
                return $this->reflection->newInstanceWithoutConstructor();
            } catch (ReflectionException) {
                throw new ResolvedException($this->declaring(), ResolvedException::INTERNAL_FINAL_CLASS);
            }
        }

        $dependencies = $this->resolveDependencies($container, $parameters);

        try {
            $object = $this->reflection->newInstanceArgs($dependencies);
        } catch (ReflectionException) {
            throw new ResolvedException($this->declaring(), ResolvedException::CONSTRUCTOR_NOT_PUBLIC);
        }

        // 属性注入实体
        foreach ($this->getPropertyDefinitions() as $propertyDefinition) {
            $value = $propertyDefinition->resolve($container, $parameters);
            $property = $propertyDefinition->valuer();
            if ($property->isPublic()) {
                $property->setValue($object, $value);
            } else {
                $property->setAccessible(true);
                $property->setValue($object, $value);
                $property->setAccessible(false);
            }
        }

        return $object;
    }

    /**
     * 定义基础分析
     *
     * @param string $class
     * @throws DefinedException
     */
    public function __construct(string $class)
    {
        if (!class_exists($class)) {
            throw new DefinedException($class, DefinedException::CLASS_NOT_EXIST);
        }

        try {
            $this->reflection = ReflectionCollector::getClass($class);
        } catch (ReflectedException $e) {
            throw new DefinedException($e->getMessage(), $e->getCode());
        }

        if ($this->reflection->isAbstract()) {
            throw new DefinedException($class, DefinedException::CLASS_IS_ABSTRACT);
        }

        $this->constructor = $this->reflection->getConstructor();
    }

    /**
     * 主调用反射
     *
     * @return ReflectionMethod|null
     */
    private function caller(): ?ReflectionMethod
    {
        return $this->constructor;
    }

    /**
     * 获取属性定义列表
     *
     * @return PropertyDefinition[]
     */
    private function getPropertyDefinitions(): array
    {
        return $this->propertyDefinitions ??= array_map(
            fn($reflectionProperty) => new PropertyDefinition($reflectionProperty),
            array_filter(
                $this->reflection->getProperties(),
                fn($reflectionProperty) => (bool)$reflectionProperty->getAttributes(Inject::class)
            )
        );
    }
}
