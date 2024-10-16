<?php

namespace VanyaPhp\CustomFramework\Foundation;

use Dotenv\Dotenv;
use VanyaPhp\CustomFramework\Collection\ArrayCollection;
use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;
use VanyaPhp\CustomFramework\Contracts\Config\ConfigInterface;
use VanyaPhp\CustomFramework\Contracts\DataMapper\DataMapperInterface;
use VanyaPhp\CustomFramework\Contracts\Model\BuilderInterface as ModelBuilderInterface;
use VanyaPhp\CustomFramework\Database\DataMapper\DataMapper;
use VanyaPhp\CustomFramework\Database\Model\Builder as ModelBuilder;
use VanyaPhp\CustomFramework\Contracts\QueryBuilder\BuilderInterface as QueryBuilderInterface;
use VanyaPhp\CustomFramework\Database\QueryBuilder\Builder as QueryBuilder;
use VanyaPhp\CustomFramework\Contracts\ServiceContainer\ServiceContainerInterface;
use VanyaPhp\CustomFramework\Database\Connection;
use VanyaPhp\CustomFramework\ServiceContainer\ServiceContainer;

class Application
{
    private ServiceContainerInterface $serviceContainer;

    private static ?self $instance;

    public function __construct()
    {
        Dotenv::createImmutable(dirname(__DIR__, 2))->load();
        $this->serviceContainer = new ServiceContainer();
        $this->init();
    }

    private function init(): void
    {
        $this->serviceContainer->singleton($this);
        self::$instance = $this;
        $this->serviceContainer->singleton(ServiceContainerInterface::class, $this->serviceContainer);
        $this->serviceContainer->singleton(ConfigInterface::class, fn () => new Config());
        $this->serviceContainer->bind(CollectionInterface::class, ArrayCollection::class);
        $this->serviceContainer->singleton(DataMapperInterface::class, fn () => new DataMapper());
        $this->serviceContainer->bind(ModelBuilderInterface::class, ModelBuilder::class);
        $this->serviceContainer->singleton(QueryBuilderInterface::class, fn () => new QueryBuilder());
        $this->serviceContainer->singleton(
            Connection::class,
            fn () => new Connection($this->serviceContainer->get(ConfigInterface::class)::getDatabaseConfig()));
    }

    public static function getInstance(?string $className = null, array $args = []): object
    {
        return ($className == null)
            ? self::$instance
            : self::$instance->serviceContainer->get($className, $args);
    }
}