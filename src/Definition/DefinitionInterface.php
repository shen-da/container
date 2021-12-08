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
     * 定位名称公布
     *
     * @return string
     */
    public function declaring(): string;

    /**
     * 根据给定参数列表，从指定容器中解析定义实体
     *
     * @param ContainerInterface $container
     * @param array $parameters
     * @return mixed
     * @throws ResolvedException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function resolve(ContainerInterface $container, array &$parameters = []): mixed;
}
