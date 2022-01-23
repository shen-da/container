<?php

declare(strict_types=1);

namespace Loner\Container\Definition\Callable;

use Loner\Container\ContainerInterface;
use Loner\Container\Definition\ParameterDefinition;

/**
 * 调用者
 *
 * @package Loner\Container\Definition\Callable
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
     * @inheritDoc
     */
    public function resolveDependencies(ContainerInterface $container, array &$parameters = []): array
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
        return $this->parameterDefinitions ??= array_map(
            fn($reflectionParameter) => new ParameterDefinition($reflectionParameter, $this->declaring()),
            $this->caller()?->getParameters() ?? []
        );
    }
}
