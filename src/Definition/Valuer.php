<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * 数值评估者
 *
 * @package Loner\Container\Definition
 */
trait Valuer
{
    /**
     * 全限定类型名列表
     *
     * @var string[]
     */
    private array $classnames;

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
     * 获取全部全限定类型名
     *
     * @return string[]
     */
    private function getClassnames(): array
    {
        $valuer = $this->valuer();

        if (null === $type = $valuer->getType()) {
            return [];
        }

        $class = $valuer->getDeclaringClass();

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
     * 返回首个全限定类名
     *
     * @return string|null
     */
    private function classname(): ?string
    {
        return $this->classnames()[0] ?? null;
    }

    /**
     * 获取相应全限定类型名列表
     *
     * @return string[]
     */
    private function classnames(): array
    {
        return $this->classnames ??= $this->getClassnames();
    }
}
