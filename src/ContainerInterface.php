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
     * 给指定标识符定义依赖源（会删除标识符对应的共享实体）
     *
     * @param string $id
     * @param callable|string $source
     * @throws DefinedException
     */
    public function define(string $id, callable|string $source): void;

    /**
     * 移除依赖定义：全部或指定标识符
     *
     * @param string|null $id
     */
    public function removeDefinition(string $id = null): void;

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
     * 从容器解析指定标识符实体并返回；若未提供参数，优先取标识符缓存实体
     *
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function make(string $id, array $parameters = []): mixed;

    /**
     * 返回对象方法依赖解析包
     *
     * @param object $object
     * @param string $method
     * @return Closure
     * @throws DefinedException
     */
    public function method(object $object, string $method): Closure;

    /**
     * 返回闭包依赖解析包
     *
     * @param Closure $closure
     * @return Closure
     * @throws DefinedException
     */
    public function closure(Closure $closure): Closure;
}
