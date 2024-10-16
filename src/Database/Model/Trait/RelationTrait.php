<?php

namespace VanyaPhp\CustomFramework\Database\Model\Trait;
;
use VanyaPhp\CustomFramework\Database\Model\BaseModel;
use VanyaPhp\CustomFramework\Database\RelationManager\BelongsTo;
use VanyaPhp\CustomFramework\Database\RelationManager\HasMany;
use VanyaPhp\CustomFramework\Database\RelationManager\HasOne;

/**
 * @mixin BaseModel
 */
trait RelationTrait
{
    /**
     * @param class-string $className
     * @param string $foreignKey
     * @param string $ownerKey
     */
    public function belongsTo(string $className, string $foreignKey, string $ownerKey): BelongsTo
    {
        return new BelongsTo($className, $foreignKey, $ownerKey, $this);
    }

    /**
     * @param class-string $className
     * @param string $foreignKey
     * @param string $ownerKey
     */
    public function hasMany(string $className, string $foreignKey, string $ownerKey): HasMany
    {
        return new HasMany($className, $foreignKey, $ownerKey, $this);
    }

    /**
     * @param class-string $className
     * @param string $foreignKey
     * @param string $ownerKey
     */
    public function hasOne(string $className, string $foreignKey, string $ownerKey): HasOne
    {
        return new HasOne($className, $foreignKey, $ownerKey, $this);
    }
}