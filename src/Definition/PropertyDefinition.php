<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use Loner\Container\ContainerInterface;
use Loner\Container\Exception\ResolvedException;
use ReflectionProperty;

/**
 * 基于【属性反射】的依赖定义
 *
 * @package Loner\Container\Definition
 */
class PropertyDefinition implements DefinitionInterface
{
    use Valuer;

    /**
     * 完全定位名称
     *
     * @var string
     */
    private string $declaring;

    /**
     * @inheritDoc
     */
    public function declaring(): string
    {
        return $this->declaring ??= $this->property->name . ' of ' . $this->property->class . '::$' . $this->property->name;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$parameters = []): mixed
    {
        $name = '$' . $this->property->name;
        if (key_exists($name, $parameters)) {
            return $parameters[$name];
        }

        if (null === $classname = $this->classname()) {
            throw new ResolvedException($this->declaring(), ResolvedException::PARAMETER_VALUE_NOT_PROVIDED);
        }

        return $container->get($classname);
    }

    /**
     * 初始化参数信息
     *
     * @param ReflectionProperty $property
     */
    public function __construct(private ReflectionProperty $property)
    {
    }

    /**
     * 返回名称
     *
     * @return string
     */
    private function name(): string
    {
        return $this->name ??= '$' . $this->property->name;
    }

    /**
     * 获取主数据反射
     *
     * @return ReflectionProperty
     */
    public function valuer(): ReflectionProperty
    {
        return $this->property;
    }
}
