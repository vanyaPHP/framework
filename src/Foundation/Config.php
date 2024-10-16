<?php

namespace VanyaPhp\CustomFramework\Foundation;

use VanyaPhp\CustomFramework\Contracts\Config\ConfigInterface;
use VanyaPhp\CustomFramework\Foundation\Exceptions\EnvironmentConfigurationException;

class Config implements ConfigInterface
{

    public static function env(string $key = null, $default = null): mixed
    {
        return !($key) ? $_ENV : ($_ENV[$key] ?? $default);
    }

    public static function getDatabaseConfig(): array
    {
        /**
         * @var array<string, array<string, mixed>> $connections
         */
        $connections = require_once dirname(__DIR__, 2) . '/config/database.php';
        $envConnectionConfig = $connections[self::env("DB_CONNECTION")] ?? null;
        if (!$envConnectionConfig) {
            throw new EnvironmentConfigurationException(
                sprintf("Database connection %s is not configured in database.php file", self::env("DB_CONNECTION")),
                500
            );
        }

        $envConnectionConfig['connection'] = self::env("DB_CONNECTION");

        return $envConnectionConfig;
    }
}