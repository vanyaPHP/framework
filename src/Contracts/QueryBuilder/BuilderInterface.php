<?php

namespace VanyaPhp\CustomFramework\Contracts\QueryBuilder;

interface BuilderInterface
{
    public function select(string $table, string $primaryKey, array $columns = ['*'], array $conditions = [], array $joins = [], array $orderBy = []): array;

    public function insert(string $table, array $attributes): array;

    public function update(string $table, array $attributes, array $conditions): array;

    public function delete(string $table, array $conditions): void;
}