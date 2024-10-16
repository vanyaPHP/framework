<?php

namespace Models;

class User extends \VanyaPhp\CustomFramework\Database\Model\BaseModel
{
    protected string $table = 'users';

    protected string $primaryKey = 'user_id';

    protected array $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    public function posts(): \VanyaPhp\CustomFramework\Database\RelationManager\HasMany
    {
        return $this->hasMany(Post::class, 'user_id', 'user_id');
    }

    public function address(): \VanyaPhp\CustomFramework\Database\RelationManager\BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }
}