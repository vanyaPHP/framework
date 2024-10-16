<?php

namespace Models;

class Address extends \VanyaPhp\CustomFramework\Database\Model\BaseModel
{
    protected string $table = 'addresses';

    protected string $primaryKey = 'address_id';

    protected array $fillable = [
        'street',
        'building',
    ];

    public function user(): \VanyaPhp\CustomFramework\Database\RelationManager\HasOne
    {
        return $this->hasOne(User::class, 'address_id', 'address_id');
    }

    public function city(): \VanyaPhp\CustomFramework\Database\RelationManager\BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }
}