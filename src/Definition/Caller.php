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
     * @param array $parameters
     * @return array
     * @throws ResolvedException
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function resolveDependencies(ContainerInterface $container, array &$parameters): array
    {
        $dependencies = [];

        foreach ($this->getParameterDefinitions() as $parameterDefinition) {
            $dependencies[] = $parameterDefinition->resolve($container, $parameters);
        }

        // 若存在最末可变参数，将其值列出
        if (isset($parameterDefinition) && $parameterDefinition->isVariadic()) {
            array_push($dependencies, ...array_pop($dependencies));
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
