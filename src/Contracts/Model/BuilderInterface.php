<?php

namespace VanyaPhp\CustomFramework\Contracts\Model;

use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;
use VanyaPhp\CustomFramework\Database\Model\BaseModel;

interface BuilderInterface
{
    public function all(array $columns = ['*']): CollectionInterface;

    public function find(mixed $id, array $columns = ['*']): BaseModel;

    public function findMany(array $ids, array $columns = ['*']): BaseModel;

    public function findOrFail(mixed $id, array $columns = ['*']): BaseModel;

    public function firstOrCreate(mixed $id, array $attributes = []): BaseModel;

    public function get(): CollectionInterface;

    public function create(array $attributes = []): BaseModel;

    public function update(array $values): BaseModel;

    public function delete(): void;

    public function query(string $modelName): self;

    public function where(string $field, string $operator = '=', mixed $value = null): self;

    public function orWhere(string $field, string $operator = '=', mixed $value = null): self;

    public function whereIn(string $field, array $values): self;

    public function orWhereIn(string $field, array $values): self;

    public function whereNotIn(string $field, array $values): self;

    public function orWhereNotIn(string $field, array $values): self;

    public function whereLike(string $field, mixed $value = null): self;

    public function orWhereLike(string $field, mixed $value = null): self;

    public function whereNotLike(string $field, mixed $value = null): self;

    public function orWhereNotLike(string $field, mixed $value = null): self;
}