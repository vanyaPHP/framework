<?php

namespace VanyaPhp\CustomFramework\Contracts\ServiceContainer;

interface ServiceContainerInterface
{
    public function get(string $className): object;

    public function bind(string $abstract, string $concrete): void;

    public function singleton(string $className, callable|object $initialization = null): void;
}