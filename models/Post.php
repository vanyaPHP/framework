<?php

namespace Models;

class Post extends \VanyaPhp\CustomFramework\Database\Model\BaseModel
{
    protected string $table = 'posts';

    protected string $primaryKey = 'post_id';

    protected bool $timestamps = true;

    protected array $fillable = [
        'title',
        'body'
    ];

    public function author(): \VanyaPhp\CustomFramework\Database\RelationManager\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}