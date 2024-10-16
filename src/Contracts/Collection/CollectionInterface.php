<?php

namespace VanyaPhp\CustomFramework\Contracts\Collection;

interface CollectionInterface
{
    public static function fromMap(array $items, callable $fn): static;

    public function setItems(array $items = []): static;

    public function reduce(callable $fn, mixed $initial): mixed;

    public function map(callable $fn): static;

    public function each(callable $fn): void;

    public function some(callable $fn): bool;

    public function filter(callable $fn): static;

    public function first(): mixed;

    public function last(): mixed;

    public function count(): int;

    public function isEmpty(): bool;

    public function add(mixed $element): void;

    public function values(): array;

    public function items(): array;
}