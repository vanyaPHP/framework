<?php

namespace Models;

class City extends \VanyaPhp\CustomFramework\Database\Model\BaseModel
{
    protected string $table = 'cities';

    protected string $primaryKey = 'city_id';

    protected array $fillable = [
        'city_name',
    ];

    public function addresses(): \VanyaPhp\CustomFramework\Database\RelationManager\HasMany
    {
        return $this->hasMany(Address::class, 'city_id', 'city_id');
    }
}