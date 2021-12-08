<?php

declare(strict_types=1);

namespace Loner\Container\Exception;

use Loner\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * 异常：容器解析错误
 *
 * @package Loner\Container\Exception
 */
class ContainerException extends ResolvedException implements ContainerExceptionInterface
{
    /**
     * 创建异常
     *
     * @param ContainerInterface $container
     * @param ResolvedException $exception
     * @return static
     */
    public static function create(ContainerInterface $container, ResolvedException $exception): self
    {
        return new self($container->getResolving(), 0, $exception);
    }
}
