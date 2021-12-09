<?php

declare(strict_types=1);

namespace Loner\Container;

use Closure;
use Loner\Container\Exception\{ContainerException, DefinedException, NotFoundException};
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * IoC 容器
 *
 * @package Loner\Container
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * 从容器解析指定标识符实体并返回（共享实体）
     *
     * @param string $id
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get(string $id): mixed;

    /**
     * 判断容器是否可以返回指定标识符的实体
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * 给指定标识符定义依赖源（会删除标识符对应的共享实体）
     *
     * @param string $id
     * @param Closure|string $source
     * @throws DefinedException
     */
    public function define(string $id, Closure|string $source): void;

    /**
     * 批量定义标识符的依赖源
     *
     * @param array $sources
     * @throws DefinedException
     */
    public function defineBatch(array $sources = []): void;

    /**
     * 移除指定标识符对应的定义，无参则移除全部
     *
     * @param string ...$ids
     */
    public function removeDefinition(string ...$ids): void;

    /**
     * 更新标识符共享实体
     *
     * @param string $id
     * @param mixed $entry
     */
    public function set(string $id, mixed $entry): void;

    /**
     * 移除共享实体：全部或指定标识符
     *
     * @param string|null $id
     */
    public function unset(string $id = null): void;

    /**
     * 从容器解析指定标识符实体并返回（不影响表层标识符缓存实体）
     *
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function make(string $id, array $parameters = []): mixed;

    /**
     * 获取解析堆栈连缀字符串
     *
     * @param string $separator
     * @return string
     */
    public function getResolving(string $separator = '->'): string;

    /**
     * 返回对象代理
     *
     * @param object $object
     * @return Foundry
     */
    public function foundry(object $object): Foundry;

    /**
     * 返回对象方法依赖解析包
     *
     * @param object $object
     * @param string $method
     * @return Closure
     */
    public function method(object $object, string $method): Closure;

    /**
     * 返回闭包依赖解析包
     *
     * @param Closure $closure
     * @return Closure
     */
    public function closure(Closure $closure): Closure;

    /**
     * 从容器中解析对象方法
     *
     * @param object $object
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function resolveMethod(object $object, string $method, array &$parameters = []): mixed;

    /**
     * 从容器中解析闭包
     *
     * @param Closure $closure
     * @param array $parameters
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function revolveClosure(Closure $closure, array &$parameters = []): mixed;
}
