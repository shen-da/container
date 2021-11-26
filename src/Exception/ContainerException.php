<?php

declare(strict_types=1);

namespace Loner\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * 异常：容器解析错误
 *
 * @package Loner\Container\Exception
 */
class ContainerException extends ResolvedException implements ContainerExceptionInterface
{
}
