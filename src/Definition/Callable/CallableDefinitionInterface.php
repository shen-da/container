<?php

declare(strict_types=1);

namespace Loner\Container\Definition\Callable;

use Loner\Container\ContainerInterface;
use Loner\Container\Definition\DefinitionInterface;
use Loner\Container\Exception\{ContainerException, NotFoundException, ResolvedException};

/**
 * 可调用依赖源定义
 *
 * @package Loner\Container\Definition\Callable
 */
interface CallableDefinitionInterface extends DefinitionInterface
{
    /**
     * 解析依赖
     *
     * @param ContainerInterface $container
     * @param array $parameters
     * @return mixed[]
     * @throws ResolvedException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function resolveDependencies(ContainerInterface $container, array &$parameters): array;
}
