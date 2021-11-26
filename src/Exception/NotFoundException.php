<?php

declare(strict_types=1);

namespace Loner\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 异常：标识符实体条目未找到
 *
 * @package Loner\Container\Exception
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
