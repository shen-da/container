<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use Loner\Container\ContainerInterface;
use Loner\Container\Exception\ResolvedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * 基于【参数反射】的依赖定义
 *
 * @package Loner\Container\Definition
 */
class ParameterDefinition implements DefinitionInterface
{
    /**
     * 数据集
     *
     * @var array
     */
    private array $dataset = [];

    /**
     * 全限定类型名列表
     *
     * @var string[]
     */
    private array $classnames;

    /**
     * 默认值
     *
     * @var mixed
     */
    private mixed $defaultValue = null;

    /**
     * 获取全部全限定类型名
     *
     * @param ReflectionParameter $parameter
     * @return string[]
     */
    private static function getClassnames(ReflectionParameter $parameter): array
    {
        if (null === $type = $parameter->getType()) {
            return [];
        }

        $class = $parameter->getDeclaringClass();

        if ($type instanceof ReflectionNamedType) {
            return (array)self::getClassname($type, $class);
        }

        return $type instanceof ReflectionUnionType ? array_reduce($type->getTypes(), function ($classnames, $type) use ($class) {
            if (null !== $classname = self::getClassname($type, $class)) {
                $classnames[] = $classname;
            }
            return $classnames;
        }, []) : [];
    }

    /**
     * 从类型名反射中获取类名，无则返回 null
     *
     * @param ReflectionNamedType $type
     * @param ReflectionClass|null $class
     * @return string|null
     */
    private static function getClassname(ReflectionNamedType $type, ?ReflectionClass $class): ?string
    {
        if ($type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if ($class !== null) {
            if ($name === 'self') {
                $name = $class->getName();
            } elseif ($name === 'parent' && null !== $class = $class->getParentClass()) {
                $name = $class->getName();
            }
        }

        return $name;
    }

    /**
     * 异常信息前缀
     *
     * @param ReflectionParameter $parameter
     * @return string
     */
    private static function getDeclaring(ReflectionParameter $parameter): string
    {
        $caller = $parameter->getDeclaringFunction();
        return property_exists($caller, 'class') ? "{$caller->class}::{$caller->name}" : $caller->name;
    }

    /**
     * 初始化参数信息
     *
     * @param ReflectionParameter $parameter
     * @param string|null $declaring
     */
    public function __construct(private ReflectionParameter $parameter, private ?string $declaring = null)
    {
        if ($this->isVariadic() === false) {
            try {
                $this->defaultValue = $parameter->getDefaultValue();
            } catch (ReflectionException) {
            }
        }
    }

    /**
     * 返回参数名称
     *
     * @return string
     */
    public function name(): string
    {
        return $this->dataset[__FUNCTION__] ??= $this->parameter->name;
    }

    /**
     * 返回参数位置
     *
     * @return int
     */
    public function position(): int
    {
        return $this->dataset[__FUNCTION__] ??= $this->parameter->getPosition();
    }

    /**
     * 返回参数是否可以为空
     *
     * @return bool
     */
    public function allowsNull(): bool
    {
        return $this->dataset[__FUNCTION__] ??= $this->parameter->allowsNull();
    }

    /**
     * 返回参数是否可变
     *
     * @return bool
     */
    public function isVariadic(): bool
    {
        return $this->dataset[__FUNCTION__] ??= $this->parameter->isVariadic();
    }

    /**
     * 返回首个全限定类名
     *
     * @return string|null
     */
    public function classname(): ?string
    {
        return $this->classnames()[0] ?? null;
    }

    /**
     * 获取参数全限定类型名列表
     *
     * @return string[]
     */
    public function classnames(): array
    {
        return $this->classnames ??= self::getClassnames($this->parameter);
    }

    /**
     * 返回参数默认值
     *
     * @return mixed
     */
    public function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * 返回调用域名称
     *
     * @return string
     */
    public function declaring(): string
    {
        return $this->declaring ??= self::getDeclaring($this->parameter);
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$arguments = []): mixed
    {
        $name = $this->name();
        if (key_exists($name, $arguments)) {
            return $arguments[$name];
        }

        $position = $this->position();
        if (key_exists($position, $arguments)) {

            // 不是末位可变参数，直接返回
            if ($this->isVariadic() === false) {
                return $arguments[$position];
            }

            // 末位可变参数，且能提供相应位置的值，则依序补值
            $args = [];

            do {
                $args[] = $arguments[$position];
            } while (key_exists(++$position, $arguments));

            return $args;
        }

        // 若为未位可变参数，且未提供值，返回空列表
        if ($this->isVariadic()) {
            return [];
        }

        if (null !== $default = $this->defaultValue()) {
            return $default;
        }

        if ($this->allowsNull()) {
            return null;
        }

        if (null === $classname = $this->classname()) {
            throw new ResolvedException(sprintf(
                'Parameter[%s] of %s has no value provided.',
                $name, $this->declaring()
            ));
        }

        return $container->get($classname);
    }
}
