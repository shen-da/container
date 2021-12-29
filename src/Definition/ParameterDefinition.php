<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use Loner\Container\ContainerInterface;
use Loner\Container\Exception\ResolvedException;
use ReflectionException;
use ReflectionParameter;

/**
 * 基于【参数反射】的依赖定义
 *
 * @package Loner\Container\Definition
 */
class ParameterDefinition implements DefinitionInterface
{
    use Valuer;

    /**
     * 数据集
     *
     * @var array
     */
    private array $dataset = [];

    /**
     * 默认值
     *
     * @var mixed
     */
    private mixed $defaultValue = null;

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
        return $this->declaring ??= $this->parameter->name . ' of ' . $this->callerDeclaring();
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container, array &$parameters = []): mixed
    {
        $name = $this->name();
        if (key_exists($name, $parameters)) {
            return $parameters[$name];
        }

        $position = $this->position();
        if (key_exists($position, $parameters)) {

            // 不是末位可变参数，直接返回
            if ($this->isVariadic() === false) {
                return $parameters[$position];
            }

            // 末位可变参数，且能提供相应位置的值，则依序补值
            $arguments = [];

            do {
                $arguments[] = &$parameters[$position];
            } while (key_exists(++$position, $parameters));

            return $arguments;
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
            throw new ResolvedException($this->declaring(), ResolvedException::PARAMETER_VALUE_NOT_PROVIDED);
        }

        return $container->get($classname);
    }

    /**
     * 获取声明函数/方法名称
     *
     * @param ReflectionParameter $parameter
     * @return string
     */
    private static function getCallerDeclaring(ReflectionParameter $parameter): string
    {
        $caller = $parameter->getDeclaringFunction();
        return property_exists($caller, 'class') ? $caller->class . '::' . $caller->name : $caller->name;
    }

    /**
     * 初始化参数信息
     *
     * @param ReflectionParameter $parameter
     * @param string|null $callerDeclaring
     */
    public function __construct(private ReflectionParameter $parameter, private ?string $callerDeclaring = null)
    {
        if ($this->isVariadic() === false) {
            try {
                $this->defaultValue = $parameter->getDefaultValue();
            } catch (ReflectionException) {
            }
        }
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
     * 返回参数名称
     *
     * @return string
     */
    private function name(): string
    {
        return $this->dataset[__FUNCTION__] ??= $this->parameter->name;
    }

    /**
     * 返回参数位置
     *
     * @return int
     */
    private function position(): int
    {
        return $this->dataset[__FUNCTION__] ??= $this->parameter->getPosition();
    }

    /**
     * 返回参数是否可以为空
     *
     * @return bool
     */
    private function allowsNull(): bool
    {
        return $this->dataset[__FUNCTION__] ??= $this->parameter->allowsNull();
    }

    /**
     * 返回参数默认值
     *
     * @return mixed
     */
    private function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * 获取声明函数/方法名称
     *
     * @return string
     */
    private function callerDeclaring(): string
    {
        return $this->callerDeclaring ??= self::getCallerDeclaring($this->parameter);
    }

    /**
     * 获取主数据反射
     *
     * @return ReflectionParameter
     */
    private function valuer(): ReflectionParameter
    {
        return $this->parameter;
    }
}
