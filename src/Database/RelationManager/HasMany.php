<?php

namespace VanyaPhp\CustomFramework\Database\RelationManager;

use VanyaPhp\CustomFramework\Database\Model\BaseModel;

class HasMany extends BaseRelation
{
    public function __construct(
        string $className,
        string $foreignKey,
        string $ownerKey,
        BaseModel $instance
    ) {
        //$this->builder = $className::query()->where($foreignKey, $ownerKey);
        parent::__construct($className, $foreignKey, $ownerKey, $instance);
    }
}