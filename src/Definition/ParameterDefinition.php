<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use Loner\Container\ContainerInterface;
use Loner\Container\Exception\ResolvedException;
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
    private array $classes;

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
        try {
            $this->defaultValue = $parameter->getDefaultValue();
        } catch (ReflectionException) {
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
     * 获取参数全限定类型名列表
     *
     * @return string[]
     */
    public function classes(): array
    {
        return $this->classes ??= self::getClassnames($this->parameter);
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
            return $arguments[$position];
        }

        if (null !== $default = $this->defaultValue()) {
            return $default;
        }

        if ($this->allowsNull()) {
            return null;
        }

        $classnames = $this->classes();

        if (empty($classnames)) {
            throw new ResolvedException(sprintf(
                'Parameter[%s] of %s has no value provided.',
                $name, $this->declaring()
            ));
        }

        $key = '$' . $name;
        if (key_exists($key, $arguments)) {
            $args = &$arguments[$key];
        }

        if (empty($args) || !is_array($args)) {
            return $container->get($classnames[0]);
        }

        foreach ($classnames as $classname) {
            if (isset($args[$classname])) {
                return $container->make($classname, $args[$classname]);
            }
        }

        return $container->make($classnames[0], $args);
    }
}
