<?php

declare(strict_types=1);

namespace Loner\Container\Exception;

use Exception;

/**
 * 异常：反射出错
 *
 * @package Loner\Container\Exception
 */
class ReflectedException extends Exception
{
    /**
     * 错误码：不是可反射类
     */
    public const NOT_REFLECTIVE_CLASS = 1;

    /**
     * 错误码：类方法不存在
     */
    public const METHOD_NOT_EXIST = 2;

    /**
     * 错误码：类属性不存在
     */
    public const PROPERTY_NOT_EXIST = 3;

    /**
     * 错误码：函数不存在
     */
    public const FUNCTION_NOT_EXIST = 4;
}
