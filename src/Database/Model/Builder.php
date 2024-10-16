<?php

namespace VanyaPhp\CustomFramework\Database\Model;

use ReflectionClass;
use VanyaPhp\CustomFramework\Contracts\Collection\CollectionInterface;
use VanyaPhp\CustomFramework\Contracts\DataMapper\DataMapperInterface;
use VanyaPhp\CustomFramework\Contracts\Model\BuilderInterface;
use \VanyaPhp\CustomFramework\Contracts\QueryBuilder\BuilderInterface as QueryBuilderInterface;

class Builder implements BuilderInterface
{
    private array $modelMapInfo;

    private array $scalarColumnsToSelect = [];

    private array $relatedColumnsToSelect = [];

    private array $wheres = [];

    public function __construct(string $modelName)
    {
        $this->modelMapInfo = app(DataMapperInterface::class)->getModelInfo($modelName);
    }

    private function buildColumn(string $column): void
    {
        $columnParts = explode('.', $column);
        if (count($columnParts) === 1) { // there is no nested relations
            $relationParts = explode(':', $column);
            if (count($relationParts) === 1) { // relation or field like 'post'
                if (in_array($relationParts[0], $this->modelMapInfo['table_columns'])) { // table column
                    $this->scalarColumnsToSelect []= $relationParts[0];
                } else { // relation column with all fields selected
                    $this->relatedColumnsToSelect [$relationParts[0]] = [
                        'fields' => ['*'],
                        'metadata' => $this->modelMapInfo['relations'][$relationParts[0]],
                        'relations' => []
                    ];
                }
            } else { // relation with certain fields like author:first_name,last_name
                $this->relatedColumnsToSelect[$relationParts[0]] = [
                    'fields' => [],
                    'metadata' => $this->modelMapInfo['relations'][$relationParts[0]],
                    'relations' => []
                ];

                $primaryKey = $this->modelMapInfo['relations'][$relationParts[0]]['primary_key'];
                $relationFields = explode(',', $relationParts[1]);
                foreach($relationFields as $relationField) {
                    $this->relatedColumnsToSelect[$relationParts[0]]['fields'] []= $relationField;
                }

                if (!in_array($primaryKey, $this->relatedColumnsToSelect[$relationParts[0]]['fields'])) {
                    $this->relatedColumnsToSelect[$relationParts[0]]['fields'] []= $primaryKey;
                }
            }
        } else { // nested relation
            $this->relatedColumnsToSelect[$columnParts[0]] = [
                'fields' => ['*'],
                'metadata' => $this->modelMapInfo['relations'][$columnParts[0]],
                'relations' => []
            ];

            for($i = 1; $i < count($columnParts) - 1; $i++) {
                $className = (count($this->relatedColumnsToSelect[$columnParts[0]]['relations']) == 0)
                    ? $this->relatedColumnsToSelect[$columnParts[0]]['metadata']['class_name']
                    : end($this->relatedColumnsToSelect[$columnParts[0]]['relations'])['class_name'];

                $this->relatedColumnsToSelect[$columnParts[0]]['relations'][$columnParts[$i]] = [
                    'fields' => ['*'],
                    'metadata' => app(DataMapperInterface::class)->getModelInfo($className)['relations'][$columnParts[$i]],
                    'relations' => []
                ];
            }

            $lastRelation = $columnParts[count($columnParts) - 1];
            $lastRelationsFields = explode(':', $lastRelation);
            $className = (count($this->relatedColumnsToSelect[$columnParts[0]]['relations']) == 0)
                ? $this->relatedColumnsToSelect[$columnParts[0]]['metadata']['class_name']
                : end($this->relatedColumnsToSelect[$columnParts[0]]['relations'])['metadata']['class_name'];

            $this->relatedColumnsToSelect[$columnParts[0]]['relations'] [$lastRelationsFields[0]] = [
                'fields' => [],
                'metadata' => app(DataMapperInterface::class)->getModelInfo($className)['relations'][$lastRelationsFields[0]],
                'relations' => []
            ];

            $primaryKey = $this->relatedColumnsToSelect[$columnParts[0]]['relations'][$lastRelationsFields[0]]['metadata']['primary_key'];

            if (count($lastRelationsFields) === 1) { // all fields from lastRelation selected
                $this->relatedColumnsToSelect[$columnParts[0]]['relations'][$lastRelationsFields[0]]['fields'] = ['*'];
            } else {
                $lastRelationFieldsList = explode(',', $lastRelationsFields[1]);
                foreach($lastRelationFieldsList as $lastRelationFieldInfo) { // add field to lastRelation 'fields' key
                    $this->relatedColumnsToSelect[$columnParts[0]]['relations'][$lastRelationsFields[0]]['fields'] []= $lastRelationFieldInfo;
                }

                if (!in_array($primaryKey, $this->relatedColumnsToSelect[$columnParts[0]]['relations'][$lastRelationsFields[0]]['fields'])) {
                  $this->relatedColumnsToSelect[$columnParts[0]]['relations'][$lastRelationsFields[0]]['fields'] []= $primaryKey;
                }
            }
        }
    }

    private function buildColumns(array $columns = ['*']): void
    {
        if (count($columns) == 1 && $columns[0] == '*') {
            $this->scalarColumnsToSelect = $columns;
        } else {
            foreach ($columns as $column) {
                if ($column == '*') {
                    $this->scalarColumnsToSelect []= '*';
                } else {
                    $this->buildColumn($column);
                }
            }
        }

        if (count($this->scalarColumnsToSelect) == 0) {
            $this->scalarColumnsToSelect = ['*'];
        }
    }

    public function all(array $columns = ['*']): CollectionInterface
    {
        $this->buildColumns($columns);

        if ($this->scalarColumnsToSelect != ['*']) {
            if (!in_array($this->modelMapInfo['primary_key'], $this->scalarColumnsToSelect)) {
                $this->scalarColumnsToSelect = [$this->modelMapInfo['primary_key'], ...$this->scalarColumnsToSelect];
            }
        }

        $result = app(QueryBuilderInterface::class)
            ->select(
                $this->modelMapInfo['table_name'],
                $this->modelMapInfo['primary_key'],
                $this->scalarColumnsToSelect,
                [],
                $this->relatedColumnsToSelect
            );

        if (count($result) == 0) {
            return app(CollectionInterface::class);
        } else {
            return $this->mapRowsToObject($result);
        }
    }

    private function mapRowsToObject($result): CollectionInterface
    {
        $objects = app(CollectionInterface::class);

        $mainObjectClassName = $this->modelMapInfo['class_name'];
        $fieldNames = array_keys($result[0]);
        $fieldNamesLength = count($fieldNames);

        foreach ($result as $row)
        {
            $i = 0;
            $index = $row[$this->modelMapInfo['primary_key']];
            while ($i < $fieldNamesLength)
            {
                if ($fieldNames[$i] == 'framework_orm_main_table')
                {
                    $i++;

                    if (!$objects->exists($row[$this->modelMapInfo['primary_key']]))
                    {
                        $objects->add(new $mainObjectClassName, $index);
                    }

                    while ($i < $fieldNamesLength && !str_contains($fieldNames[$i], '_framework_orm_delimiter'))
                    {
                        if (!isset($objects->get($index)->{$fieldNames[$i]}))
                        {
                            $objects->get($index)->{$fieldNames[$i]} = $row[$fieldNames[$i]];
                        }
                        $i++;
                    }
                }
                else if (str_contains($fieldNames[$i], '_framework_orm_delimiter'))
                {
                    $relationFullName = substr(
                        $fieldNames[$i],
                        0,
                        strpos($fieldNames[$i], '_framework_orm_delimiter'));

                    $i++;

                    $relationParts = explode('_', $relationFullName);
                    if (count($relationParts) == 1)
                    {
                        $relationClassName = $this->relatedColumnsToSelect[$relationParts[0]]['metadata']['class_name'];
                        $relationType = $this->relatedColumnsToSelect[$relationParts[0]]['metadata']['relation'];
                        if ($relationType == 'belongs_to' || $relationType == 'has_one')
                        {
                            if (!isset($objects->get($index)->{$relationParts[0]}))
                            {
                                $objects->get($index)->{$relationParts[0]} = new $relationClassName;
                            }

                            while ($i < $fieldNamesLength && !str_contains($fieldNames[$i], '_framework_orm_delimiter'))
                            {
                                if (!isset($objects->get($index)->{$relationParts[0]}))
                                {
                                    $objects->get($index)->{$relationParts[0]}->{$fieldNames[$i]} = $row[$fieldNames[$i]];
                                }
                                $i++;
                            }
                        }
                        else
                        {
                            if (!isset($objects->get($index)->{$relationParts[0]}))
                            {
                                $objects->get($index)->{$relationParts[0]} = app(CollectionInterface::class);
                            }

                            $key = $row[$this->relatedColumnsToSelect[$relationParts[0]]['metadata']['primary_key']];
                            if (!$objects->get($index)->{$relationParts[0]}->exists($key))
                            {
                                $objects->get($index)->{$relationParts[0]}->add(new $relationClassName, $key);
                            }

                            while ($i < $fieldNamesLength && !str_contains($fieldNames[$i], '_framework_orm_delimiter'))
                            {
                                if (!isset($objects->get($index)->{$relationParts[0]}->get($key)->{$fieldNames[$i]}))
                                {
                                    $objects->get($index)->{$relationParts[0]}->get($key)->{$fieldNames[$i]} = $row[$fieldNames[$i]];
                                }
                                $i++;
                            }
                        }
                    }
                    else
                    {
                        $relationsArray = $this->relatedColumnsToSelect[$relationParts[0]]['relations'];
                        $relationType = $this->relatedColumnsToSelect[$relationParts[0]]['relations'][$relationParts[1]]['metadata']['relation'];
                        $relationClassName = $this->relatedColumnsToSelect[$relationParts[0]]['relations'][$relationParts[1]]['metadata']['class_name'];
                        $tempObject = $objects->get($index)->{$relationParts[0]};
                        if (is_collection($tempObject))
                        {
                            $tempObject = $tempObject->last();
                        }

                        foreach ($relationsArray as $relationName => $relationData)
                        {
                            if ($relationName == $relationParts[1])
                            {
                                if ($relationType == 'belongs_to' || $relationType == 'has_one')
                                {
                                    if (!isset($tempObject->{$relationName}))
                                    {
                                        $tempObject->{$relationName} = new $relationClassName;
                                    }

                                    while ($i < $fieldNamesLength && !str_contains($fieldNames[$i], '_framework_orm_delimiter'))
                                    {
                                        if (!isset($tempObject->{$relationName}->{$fieldNames[$i]}))
                                        {
                                            $tempObject->{$relationName}->{$fieldNames[$i]} = $row[$fieldNames[$i]];
                                        }
                                        $i++;
                                    }
                                }
                                else
                                {
                                    if (!isset($tempObject->{$relationName}))
                                    {
                                        $tempObject->{$relationName} = app(CollectionInterface::class);
                                    }

                                    $key = $row[$this->relatedColumnsToSelect[$relationParts[0]]['relations'][$relationParts[1]]['metadata']['primary_key']];
                                    if (!$tempObject->{$relationName}->exists($key))
                                    {
                                        $tempObject->{$relationName}->add(new $relationClassName, $key);
                                    }

                                    while ($i < $fieldNamesLength && !str_contains($fieldNames[$i], '_framework_orm_delimiter'))
                                    {
                                        if (!isset($tempObject->{$relationName}->get($key)->{$fieldNames[$i]}))
                                        {
                                            $tempObject->{$relationName}->get($key)->{$fieldNames[$i]} = $row[$fieldNames[$i]];
                                        }
                                        $i++;
                                    }
                                }
                                break;
                            }
                            else
                            {
                                $tempObject = $tempObject->{$relationName};
                                if (is_collection($tempObject))
                                {
                                    $tempObject = $tempObject->last($tempObject);
                                }
                            }
                        }
                    }
                }
            }
        }

        $test = null;

        $this->normalizeCollectionKeys($objects);
        exit(print_r($objects, true));

        return $objects;
    }

    private function normalizeCollectionKeys(CollectionInterface $objects): CollectionInterface
    {
       return $objects
           ->filter(function ($item) {
                return ($item != []);
           })
           ->map(function ($item) {
                $properties = get_class_vars($item::class);
                foreach ($properties as $property)
                {
                    if (is_collection($item->{$property}))
                    {
                        $item->{$property} = $this->normalizeCollectionKeys($item->{$property});
                    }
                }

                return $item;
       });
    }

    public function find(mixed $id, array $columns = ['*']): BaseModel
    {
        // select - _columns_, condition for primary, no order by, no limit
    }

    public function findMany(array $ids, array $columns = ['*']): BaseModel
    {
        // select - _columns_, condition for primaries, no order by, no limit
    }

    public function findOrFail(mixed $id, array $columns = ['*']): BaseModel
    {
        // select - _columns_, condition for primary, no order by, no limit => exception if not found
    }

    public function firstOrCreate(mixed $id, array $attributes = []): BaseModel
    {
        // select - _columns_, condition for primary, no order by, no limit => insert if not found
    }

    public function get(): CollectionInterface
    {
        // execute built query
    }

    public function create(array $attributes = []): BaseModel
    {
        // insert - _columns_
    }

    public function update(array $values): BaseModel
    {
        // update - primary to update, columns to be updated
    }

    public function delete(): void
    {
        // delete certain ids
    }

    public function query(string $modelName): BuilderInterface
    {
        return new self($modelName);
    }

    public function where(string $field, string $operator = '=', mixed $value = null): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'AND',
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function orWhere(string $field, string $operator = '=', mixed $value = null): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'OR',
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function whereIn(string $field, array $values): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'AND',
            'field' => $field,
            'operator' => 'IN',
            'value' => $values
        ];

        return $this;
    }

    public function orWhereIn(string $field, array $values): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'OR',
            'field' => $field,
            'operator' => 'IN',
            'value' => $values
        ];

        return $this;
    }

    public function whereNotIn(string $field, array $values): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'AND',
            'field' => $field,
            'operator' => 'NOT IN',
            'value' => $values
        ];

        return $this;
    }

    public function orWhereNotIn(string $field, array $values): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'OR',
            'field' => $field,
            'operator' => 'NOT IN',
            'value' => $values
        ];

        return $this;
    }

    public function whereLike(string $field, mixed $value = null): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'AND',
            'field' => $field,
            'operator' => 'LIKE',
            'value' => $value
        ];

        return $this;
    }

    public function orWhereLike(string $field, mixed $value = null): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'OR',
            'field' => $field,
            'operator' => 'LIKE',
            'value' => $value
        ];

        return $this;
    }

    public function whereNotLike(string $field, mixed $value = null): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'AND',
            'field' => $field,
            'operator' => 'NOT LIKE',
            'value' => $value
        ];

        return $this;
    }

    public function orWhereNotLike(string $field, mixed $value = null): BuilderInterface
    {
        $this->wheres []= [
            'linker' => 'OR',
            'field' => $field,
            'operator' => 'NOT LIKE',
            'value' => $value
        ];

        return $this;
    }
}