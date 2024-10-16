<?php

namespace VanyaPhp\CustomFramework\Collection;

use IteratorAggregate;
use ArrayIterator;
use Traversable;
use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;

class ArrayCollection implements CollectionInterface, IteratorAggregate
{
    private array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function setItems(array $items = []): static
    {
        $this->items = $items;

        return $this;
    }

    public function exists(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public static function fromMap(array $items, callable $fn): static
    {
        return new static(array_map($fn, $items));
    }

    public function reduce(callable $fn, mixed $initial): mixed
    {
        return array_reduce($this->items, $fn, $initial);
    }

    public function map(callable $fn): static
    {
        return new static (array_map($fn, $this->items));
    }

    public function each(callable $fn): void
    {
        array_walk($this->items, $fn);
    }

    public function some(callable $fn): bool
    {
        foreach ($this->items as $index => $element) {
            if ($fn($element, $index, $this->items)) {
                return true;
            }
        }

        return false;
    }

    public function filter(callable $fn): static
    {
        return new static(array_filter($this->items, $fn, ARRAY_FILTER_USE_BOTH));
    }

    public function first(): mixed
    {
        return reset($this->items);
    }

    public function last(): mixed
    {
        return end($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function add(mixed $element, mixed $key = null): void
    {
        if ($key != null)
        {
            $this->items[$key] = $element;
        }

        else $this->items[] = $element;
    }

    public function clearEmptyValues(): void
    {
        $items = [];
        foreach ($this->items as $item) {
            if ($item != [])
            {
                $items []= $item;
            }
        }

        $this->items = $items;
    }

    public function get(mixed $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    public function values(): array
    {
        return array_values($this->items);
    }

    public function items(): array
    {
        return $this->items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}