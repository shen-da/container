<?php

declare(strict_types=1);

namespace Loner\Container;

use Loner\Container\Collector\DefinitionCollector;
use Loner\Container\Exception\{ContainerException, DefinedException, NotFoundException, ResolvedException};

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
     * 代理对象方法
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __call(string $name, array $arguments): mixed
    {
        $container = $this->container;
        $object = $this->object;

        $container->resolvesPush($object::class . '::' . $name);

        try {
            $definition = DefinitionCollector::getMethod($object::class, $name);
        } catch (DefinedException) {
            throw new NotFoundException($container);
        }

        try {
            $entry = $definition->setObject($object)->resolve($container, $arguments);
        } catch (ResolvedException $e) {
            throw new ContainerException($container, $e);
        }

        $container->resolvesPop($object::class . '::' . $name);

        return $entry;
    }
}
