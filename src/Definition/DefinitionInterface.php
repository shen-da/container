<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use Loner\Container\ContainerInterface;
use Loner\Container\Exception\{ContainerException, NotFoundException, ResolvedException};

/**
 * 依赖源定义
 *
 * @package Loner\Container\Definition
 */
interface DefinitionInterface
{
    /**
     * @param ContainerInterface $container
     * @param array $arguments
     * @return mixed
     * @throws ResolvedException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function resolve(ContainerInterface $container, array &$arguments = []): mixed;
}
