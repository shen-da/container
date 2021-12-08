<?php

declare(strict_types=1);

namespace Loner\Container;

use Loner\Container\Exception\{ContainerException, NotFoundException};

/**
 * 代工厂
 *
 * @package Loner\Container
 */
class Foundry
{
    /**
     * 初始化信息
     *
     * @param ContainerInterface $container
     * @param object $object
     */
    public function __construct(private ContainerInterface $container, private object $object)
    {
    }

    /**
     * 代理对象调用方法，解决依赖
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __call(string $name, array $parameters): mixed
    {
        return $this->container->resolveMethod($this->object, $name, $parameters);
    }
}
