<?php

declare(strict_types=1);

namespace Loner\Container\Exception;

/**
 * 异常：定义依赖源错误
 *
 * @package Loner\Container\Exception
 */
class DefinedException extends ReflectedException
{
    /**
     * 错误码：类不存在
     */
    public const CLASS_NOT_EXIST = 0x11;

    /**
     * 错误码：类是抽象的
     */
    public const CLASS_IS_ABSTRACT = 0x12;
}
