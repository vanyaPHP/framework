<?php

namespace VanyaPhp\CustomFramework\Database\QueryBuilder;

use PDO;
use VanyaPhp\CustomFramework\Contracts\QueryBuilder\BuilderInterface;
use VanyaPhp\CustomFramework\Database\Connection;

class Builder implements BuilderInterface
{

    public function select(
        string $table, string $primaryKey, array $columns = ['*'],
        array $conditions = [], array $joins = [], array $orderBy = []
    ): array {
        $connection = app(Connection::class)->getConnection();
        $mainTableColumns = implode(',', array_map(fn ($column) => "$table.$column", $columns));

        $joinedTablesColumns = [];
        $joinedTablesConditions = [];
        $joinedTableRelationLink = [];

        foreach($joins as $joinRelationName => $join) {
            $joinedTablesConditions [$join['metadata']['table']]= [
                'foreign_key' => $join['metadata']['foreign_key'],
                'owner_key' => $join['metadata']['owner_key'],
                'is_owner' => $join['metadata']['is_owner'],
                'joined_table' => $table
            ];
            $joinedTablesColumns [$join['metadata']['table']] = $join['fields'];
            $joinedTableRelationLink [$join['metadata']['table']] = $joinRelationName;

            if (count($join['relations']) != 0) {
                $counter = 0;
                foreach ($join['relations'] as $relationName => $subRelation) {
                    $joinedTablesConditions [$subRelation['metadata']['table']]= [
                        'foreign_key' => $subRelation['metadata']['foreign_key'],
                        'owner_key' => $subRelation['metadata']['owner_key'],
                        'is_owner' => $subRelation['metadata']['is_owner'],
                        'joined_table' => ($counter == 0)
                            ? $join['metadata']['table']
                            : array_search(end($joinedTablesConditions), $joinedTablesConditions),
                    ];

                    $joinedTablesColumns [$subRelation['metadata']['table']] = $subRelation['fields'];
                    $joinedTableRelationLink [$subRelation['metadata']['table']] = $joinRelationName . "_" . $relationName;
                    $counter ++;
                }
            }
        }

        $joinSelectors = implode(
            ', ',
            array_map(function ($table, array $fields) use ($joinedTableRelationLink) {
                if ($fields == ['*']) {
                    return sprintf("'' as %s_framework_orm_delimiter, %s.*", $joinedTableRelationLink[$table], $table);
                } else {
                    $tableSelector = sprintf("'' as %s_framework_orm_delimiter, ", $joinedTableRelationLink[$table]);
                    $tableSelector .= implode(
                        ', ',
                        array_map(
                            function ($field) use ($table) {
                                return "$table.$field";
                            },
                            $fields
                        )
                    );

                    return $tableSelector;
                }
            }, array_keys($joinedTablesColumns), $joinedTablesColumns)
        );

        $joinCondition = implode(' ', array_map(function ($joinData) use ($joinedTablesConditions, $table) {
            $joinTable = array_search($joinData, $joinedTablesConditions);
            $joinedTable = $joinData['joined_table'];
            $foreignKey = $joinData['foreign_key'];
            $ownerKey = $joinData['owner_key'];
            return ($joinData['is_owner'])
                ? "LEFT JOIN $joinTable ON $joinedTable.$foreignKey = $joinTable.$ownerKey"
                : "LEFT JOIN $joinTable ON $joinedTable.$ownerKey = $joinTable.$foreignKey";
        }, $joinedTablesConditions));

        $sql = "SELECT '' as framework_orm_main_table, $mainTableColumns, $joinSelectors FROM $table $joinCondition";

        return $connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $table, array $attributes): array
    {
        // TODO: Implement insert() method.
    }

    public function update(string $table, array $attributes, array $conditions): array
    {
        // TODO: Implement update() method.
    }

    public function delete(string $table, array $conditions): void
    {
        // TODO: Implement delete() method.
    }
}