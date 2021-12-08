<?php

declare(strict_types=1);

namespace Loner\Container\Exception;

use Exception;

/**
 * 异常：解析依赖错误
 *
 * @package Loner\Container\Exception
 */
class ResolvedException extends Exception
{
    /**
     * 错误码：参数值未提供
     */
    public const PARAMETER_VALUE_NOT_PROVIDED = 1;

    /**
     * 错误码：方法调用失败
     */
    public const METHOD_INVOCATION_FAILED = 2;

    /**
     * 错误码：必须调用构造函数的内置 final 类（做了构造函数判断，理论上不会有该错误码抛出）
     */
    public const INTERNAL_FINAL_CLASS = 3;

    /**
     * 错误码：构造方法不是公开的
     */
    public const CONSTRUCTOR_NOT_PUBLIC = 4;
}
