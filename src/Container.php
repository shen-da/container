<?php

declare(strict_types=1);

namespace Loner\Container;

use Closure;
use Loner\Container\Collector\DefinitionCollector;
use Loner\Container\Definition\Callable\CallableDefinitionInterface;
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
     * @var <CallableDefinitionInterface|DefinedException>[]
     */
    private array $definitions = [];

    /**
     * 解析实体标识符堆栈
     *
     * @var string[]
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
    public function method(object $object, string $method): Closure
    {
        return fn(array $parameters = []) => $this->resolveMethod($object, $method, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function closure(Closure $closure): Closure
    {
        return fn(array &$parameters = []) => $this->revolveClosure($closure, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function resolveMethod(object $object, string $method, array &$parameters): mixed
    {
        try {
            return DefinitionCollector::getMethod($object::class, $method)->setObject($object)->resolve($this, $parameters);
        } catch (DefinedException $e) {
            throw NotFoundException::create($this, $e);
        } catch (ResolvedException $e) {
            throw ContainerException::create($this, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function revolveClosure(Closure $closure, array &$parameters): mixed
    {
        try {
            return DefinitionCollector::getFunction($closure)->resolve($this, $parameters);
        } catch (DefinedException $e) {
            throw NotFoundException::create($this, $e);
        } catch (ResolvedException $e) {
            throw ContainerException::create($this, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function define(string $id, Closure|string $source): void
    {
        $this->definitions[$id] = DefinitionCollector::make($source);
        unset($this->entries[$id]);
    }

    /**
     * @inheritDoc
     */
    public function defineBatch(array $sources = []): void
    {
        foreach ($sources as $id => $source) {
            try {
                $this->define($id, $source);
            } catch (TypeError) {
                throw new DefinedException(sprintf('Invalid definition source type for identifier[%s]. Definition source must be a string or closure.', $id));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function removeDefinition(string ...$ids): void
    {
        if (empty($ids)) {
            $this->definitions = [];
        } else {
            foreach ($ids as $id) {
                unset($this->definitions[$id]);
            }
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

        $definitionOrException = $this->getDefinition($id);

        if ($definitionOrException instanceof DefinedException) {
            throw NotFoundException::create($this, $definitionOrException);
        }

        try {
            $entry = $definitionOrException->resolve($this, $parameters);
        } catch (ResolvedException $e) {
            throw ContainerException::create($this, $e);
        }

        array_pop($this->resolveStack);

        return $entry;
    }

    /**
     * @inheritDoc
     */
    public function getResolving(string $separator = '->'): string
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
        return $this->hasEntry($id) || $this->getDefinition($id) instanceof CallableDefinitionInterface;
    }

    /**
     * 共享容器自身，初始化定义
     */
    public function __construct()
    {
        $this->set(self::class, $this);
        $this->set(ContainerInterface::class, $this);
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
     * 获取标识符的定义，或者定义异常
     *
     * @param string $id
     * @return CallableDefinitionInterface|DefinedException
     */
    private function getDefinition(string $id): CallableDefinitionInterface|DefinedException
    {
        return $this->definitions[$id] ??= DefinitionCollector::makeSafely($id);
    }
}
