<?php

namespace VanyaPhp\CustomFramework\ServiceContainer;

use ReflectionClass;
use ReflectionParameter;
use VanyaPhp\CustomFramework\Contracts\ServiceContainer\ServiceContainerInterface;

class ServiceContainer implements ServiceContainerInterface
{
    /**
     * @var array<string, callable|object>
     */
    private array $singletons = [];

    /**
     * @var array<string, callable|object>
     */
    private array $bindings = [];

    public function get(string $className, array $args = []): object
    {
        if ($initialization = $this->singletons[$className] ?? false) {
            if (is_callable($initialization)) {
                $this->singletons[$className] = call_user_func($initialization, $args);
                return $this->singletons[$className];
            }

            return $this->singletons[$className];
        }

        if ($bindClassName = $this->bindings[$className] ?? false) {
            return $this->get($bindClassName, $args);
        }

        $reflection = new ReflectionClass($className);
        if (count($args) != 0) {
            return $reflection->newInstanceArgs($args);
        }

        $constructorParams = array_map(function (ReflectionParameter $parameter) {
            $type = $parameter->getType();
            if ($type->isBuiltin()) {
                return $parameter->getDefaultValue();
            }

            return $this->get($type);

        }, $reflection->getConstructor()?->getParameters() ?? []);

        if (!$constructorParams || count($constructorParams) === 0) {
            return new $className();
        }

        return $reflection->newInstance($constructorParams);
    }

    public function bind(string $abstract, string|callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string|object $className, callable|object|null $initialization = null): void
    {
        if (is_null($initialization)) {
            $this->singletons[$className::class] = $className;
            return;
        }

        $this->singletons[$className] = $initialization;
    }
}