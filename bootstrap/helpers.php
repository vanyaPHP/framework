<?php

use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;
use VanyaPhp\CustomFramework\Contracts\Config\ConfigInterface;
use VanyaPhp\CustomFramework\Foundation\Application;

if (!function_exists('app')) {
    function app(?string $className = null, array $args = []): object
    {
        return Application::getInstance($className, $args);
    }
}

if (!function_exists('is_collection')) {
    function is_collection($value): bool
    {
        return is_a($value, CollectionInterface::class);
    }
}

if (!function_exists('env')) {
    function env(string $key = null, mixed $default = null): mixed {
        return app(ConfigInterface::class)::env($key, $default);
    }
}