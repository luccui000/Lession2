<?php

namespace LampartTest\Core;

use MongoDB\Driver\Exception\ConnectionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class Container implements ContainerInterface
{
    private static $entries = [];

    /**
     * get entry from container
     * @param string $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function get($id)
    {
        if($this->has($id)) {
            $entry = static::$entries[$id];
            if(is_callable($entry)) {
                return $entry($this);
            }
            $id = $entry;
        }
        return $this->resolve($id);
    }

    /**
     * check entry exists
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        return isset(static::$entries[$id]);
    }

    /**
     * resolve all dependencies
     * @param $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function resolve($id): mixed
    {
        $reflectionClass = new ReflectionClass($id);
        if(!$reflectionClass->isInstantiable()) {
            throw new ConnectionException("Class $id is instantiable");
        }
        $constructor = $reflectionClass->getConstructor();
        if(!$constructor)
            return new $id;
        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    /**
     * get constructor dependencies
     * @param array $parameters
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDependencies(array $parameters): array
    {
        return array_map(function(ReflectionParameter $param) {
            $type = $param->getType();
            if(!$type)
                throw new ConnectionException("");
            if($type instanceof ReflectionUnionType)
                throw new ConnectionException("");
            if($type instanceof ReflectionNamedType && !$type->isBuiltin())
                return $this->get($type->getName());
            return null;
        }, $parameters);
    }
}