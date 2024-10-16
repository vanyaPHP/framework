<?php

namespace VanyaPhp\CustomFramework\Database\RelationManager;

use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;
use VanyaPhp\CustomFramework\Database\Model\BaseModel;

class BelongsTo extends BaseRelation
{
    public function __construct(
        string $className,
        string $foreignKey,
        string $ownerKey,
        BaseModel $instance,
    ) {
        //$this->builder = $className::query()->where($ownerKey, $foreignKey);
        parent::__construct($className, $foreignKey, $ownerKey, $instance);
    }

    public function get(): BaseModel|CollectionInterface|null
    {
        return parent::get()->first();
    }
}