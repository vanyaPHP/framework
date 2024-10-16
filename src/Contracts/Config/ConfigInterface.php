<?php

namespace VanyaPhp\CustomFramework\Contracts\Config;

interface ConfigInterface
{
    public static function env(string $key = null, $default = null): mixed;

    public static function getDatabaseConfig(): array;
}