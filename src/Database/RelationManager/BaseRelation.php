<?php

namespace VanyaPhp\CustomFramework\Database\RelationManager;

use ReflectionClass;
use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;
use VanyaPhp\CustomFramework\Database\Model\BaseModel;
use VanyaPhp\CustomFramework\Contracts\Model\BuilderInterface;

abstract class BaseRelation
{
    public string $primaryKey;

    protected ?BuilderInterface $builder = null;

    /**
     * @param class-string $className
     * @param string $foreignKey
     * @param string $localKey
     * @param BaseModel $baseModelObject
     */
    public function __construct(
        public readonly string $className,
        public readonly string $foreignKey,
        public readonly string $localKey,
        public readonly BaseModel $baseModelObject,
    )
    {
        $reflectionClass = new ReflectionClass($this->className);
        $property = $reflectionClass->getProperty('primaryKey');
        $this->primaryKey = $property->getDefaultValue();
    }

    public function get(): BaseModel|CollectionInterface|null
    {
        return $this->builder->get();
    }
}