<?php

declare(strict_types=1);

namespace Loner\Container;

use Closure;
use Loner\Container\Collector\DefinitionCollector;
use Loner\Container\Definition\{ClassDefinition, FunctionDefinition, MethodDefinition};
use Loner\Container\Exception\{ContainerException, DefinedException, NotFoundException, ResolvedException};
use TypeError;

/**
 * IoC 容器
 *
 * @package Loner\Container
 */
class Container implements ContainerInterface
{
    /**
     * 共享实体库
     *
     * @var array
     */
    private array $entries = [];

    /**
     * 定义库
     *
     * @var array
     */
    private array $definitions = [];

    /**
     * 解析实体标识符堆栈
     *
     * @var array
     */
    private array $resolveStack = [];

    /**
     * @inheritDoc
     */
    public function foundry(object $object): Foundry
    {
        return new Foundry($this, $object);
    }

    /**
     * @inheritDoc
     */
    public function closure(Closure $closure): Closure
    {
        $definition = DefinitionCollector::getFunction($closure);
        return fn(array &$arguments = []) => $definition->resolve($this, $arguments);
    }

    /**
     * @inheritDoc
     */
    public function define(string $id, callable|string $source): void
    {
        $this->definitions[$id] = DefinitionCollector::make($source);
        unset($this->entries[$id]);
    }

    /**
     * @inheritDoc
     */
    public function removeDefinition(string $id = null): void
    {
        if ($id === null) {
            $this->definitions = [];
        } else {
            unset($this->definitions[$id]);
        }
    }

    /**
     * @inheritDoc
     */
    public function set(string $id, mixed $entry): void
    {
        $this->entries[$id] = $entry;
    }

    /**
     * @inheritDoc
     */
    public function unset(string $id = null): void
    {
        if ($id === null) {
            $this->entries = [];
        } else {
            unset($this->entries[$id]);
        }
    }

    /**
     * @inheritDoc
     */
    public function make(string $id, array $parameters = []): mixed
    {
        $this->resolveStack[] = $id;

        if (false === $definition = $this->getDefinition($id)) {
            throw  NotFoundException::create($this);
        }

        try {
            $entry = $definition->resolve($this, $parameters);
        } catch (ResolvedException $e) {
            throw ContainerException::create($this, $e);
        }

        array_pop($this->resolveStack);

        return $entry;
    }

    /**
     * @inheritDoc
     */
    public function resolvesPush(string $id): void
    {
        $this->resolveStack[] = $id;
    }

    /**
     * @inheritDoc
     */
    public function resolvesPop(string $id): void
    {
        if ($id !== $resolve = array_pop($this->resolveStack)) {
            array_push($this->resolveStack, $resolve);
        }
    }

    /**
     * @inheritDoc
     */
    public function getResolving(string $separator = '.'): string
    {
        return join($separator, $this->resolveStack);
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): mixed
    {
        return $this->hasEntry($id) ? $this->entries[$id] : $this->entries[$id] = $this->make($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return $this->hasEntry($id) || !empty($this->definitions[$id]) || $this->getDefinition($id);
    }

    /**
     * 共享容器自身，初始化定义
     *
     * @param array $sources
     */
    public function __construct(array $sources = [])
    {
        $this->set(ContainerInterface::class, $this);
        $this->set(self::class, $this);

        $this->defineBatchSafely($sources);
    }

    /**
     * 初始化定义库
     *
     * @param array $sources
     */
    private function defineBatchSafely(array $sources = []): void
    {
        foreach ($sources as $id => $source) {
            try {
                $this->define($id, $source);
            } catch (DefinedException | TypeError) {
            }
        }
    }

    /**
     * 返回是否存在标识符的实体
     *
     * @param string $id
     * @return bool
     */
    private function hasEntry(string $id): bool
    {
        return isset($this->entries[$id]) || key_exists($id, $this->entries);
    }

    /**
     * 获取标识符的定义
     *
     * @param string $id
     * @return ClassDefinition|FunctionDefinition|MethodDefinition|false
     */
    private function getDefinition(string $id): ClassDefinition|FunctionDefinition|MethodDefinition|false
    {
        return $this->definitions[$id] ??= DefinitionCollector::makeSafely($id);
    }
}
