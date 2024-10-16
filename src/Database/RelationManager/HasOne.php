<?php

namespace VanyaPhp\CustomFramework\Database\RelationManager;

use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;
use VanyaPhp\CustomFramework\Database\Model\BaseModel;

class HasOne extends BaseRelation
{
    public function __construct(
        string $className,
        string $foreignKey,
        string $ownerKey,
        BaseModel $instance,
    ) {
        //$this->builder = $className::query()->where($foreignKey, $ownerKey);
        parent::__construct($className, $foreignKey, $ownerKey, $instance);
    }

    public function get(): ?BaseModel
    {
        return parent::get()->first();
    }
}