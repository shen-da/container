<?php

declare(strict_types=1);

namespace Loner\Container\Exception;

use Loner\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 异常：标识符实体条目未找到
 *
 * @package Loner\Container\Exception
 */
class NotFoundException extends DefinedException implements NotFoundExceptionInterface
{
    /**
     * 创建异常
     *
     * @param ContainerInterface $container
     * @param DefinedException $exception
     * @return static
     */
    public static function create(ContainerInterface $container, DefinedException $exception): self
    {
        return new self($container->getResolving(), 0, $exception);
    }
}
