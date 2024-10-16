<?php

namespace VanyaPhp\CustomFramework\Database\Model;

use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;
use VanyaPhp\CustomFramework\Contracts\Model\BuilderInterface;
use VanyaPhp\CustomFramework\Database\Model\Trait\RelationTrait;

class BaseModel
{
    use RelationTrait;

    protected string $primaryKey;

    protected string $table;

    protected bool $timestamps = false;

    protected array $fillable = [];

    protected array $casts = [];

    public static function create(array $attributes = []): self
    {
        return app(BuilderInterface::class)->create($attributes);
    }

    public function update(array $attributes): self
    {
        return app(BuilderInterface::class)
            ->where($this->primaryKey, $this->{$this->primaryKey})
            ->update($attributes);
    }

    public function delete(mixed $id): void
    {
        app(BuilderInterface::class)
            ->where($this->primaryKey, $this->{$this->primaryKey})
            ->delete();
    }

    public static function all(array $columns = ['*']): CollectionInterface
    {
        return app(BuilderInterface::class, [static::class])->all($columns);
    }

    public static function find(mixed $id, array $columns = ['*']): BaseModel
    {
        return app(BuilderInterface::class)->find($id, $columns);
    }

    public static function findMany(array $ids, array $columns = ['*']): BaseModel
    {
        return app(BuilderInterface::class)->findMany($ids, $columns);
    }

    public static function findOrFail(mixed $id, array $columns = ['*']): BaseModel
    {
        return app(BuilderInterface::class)->findOrFail($id, $columns);
    }

    public static function firstOrCreate(mixed $id, array $attributes): BaseModel
    {
        return app(BuilderInterface::class)->firstOrCreate($id, $attributes);
    }

    public static function query()
    {
        return app(BuilderInterface::class)->query(self::class);
    }
}