<?php

declare(strict_types=1);

namespace Loner\Container\Definition;

use Loner\Container\ContainerInterface;
use Loner\Container\Exception\{ContainerException, NotFoundException, ResolvedException};

/**
 * 调用特征
 *
 * @package Loner\Container\Definition
 */
trait Caller
{
    /**
     * 参数定义列表
     *
     * @var ParameterDefinition[]
     */
    private array $parameterDefinitions;

    /**
     * 解析依赖
     *
     * @param ContainerInterface $container
     * @param array $arguments
     * @return array
     * @throws ResolvedException
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function resolveDependencies(ContainerInterface $container, array &$arguments): array
    {
        $dependencies = [];

        foreach ($this->getParameterDefinitions() as $parameterDefinition) {
            $dependencies[] = $parameterDefinition->resolve($container, $arguments);
        }

        // 存在最末可变参数，且能提供相应位置的值，则依序补值
        if (isset($parameterDefinition) && $parameterDefinition->isVariadic()) {
            $position = $parameterDefinition->position();
            while (key_exists(++$position, $arguments)) {
                $dependencies[] = $arguments[$position];
            }
        }

        return $dependencies;
    }

    /**
     * 获取参数定义列表
     *
     * @return ParameterDefinition[]
     */
    private function getParameterDefinitions(): array
    {
        return $this->parameterDefinitions ??= array_map(fn($reflectionParameter) => new ParameterDefinition($reflectionParameter), $this->caller()->getParameters());
    }
}
