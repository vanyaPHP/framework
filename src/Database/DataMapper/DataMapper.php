<?php

namespace VanyaPhp\CustomFramework\Database\DataMapper;

use PDO;
use VanyaPhp\CustomFramework\Contracts\DataMapper\DataMapperInterface;
use VanyaPhp\CustomFramework\Database\Connection;
use VanyaPhp\CustomFramework\Database\RelationManager\BelongsTo;
use VanyaPhp\CustomFramework\Database\RelationManager\HasMany;
use VanyaPhp\CustomFramework\Database\RelationManager\HasOne;

class DataMapper implements DataMapperInterface
{
    protected const array METHODS_TO_EXCLUDE = [
        'create',
        'update',
        'delete',
        'all',
        'find',
        'findMany',
        'findOrFail',
        'firstOrCreate',
        'query',
        'belongsTo',
        'hasMany',
        'hasOne',
    ];

    public function getModelInfo(string $modelName): array
    {
        $info = [];

        $reflection = new \ReflectionClass($modelName);
        $info['class_name'] = $modelName;
        $info['primary_key'] = $reflection->getProperty('primaryKey')->getDefaultValue();
        $info['table_name'] = $reflection->getProperty('table')->getDefaultValue();
        $info['timestamps'] = $reflection->getProperty('timestamps')->getDefaultValue();

        $connection = app(Connection::class)->getConnection();
        $info['table_columns'] = $connection
            ->query(sprintf('SHOW COLUMNS FROM %s', $info['table_name']))
            ->fetchAll(PDO::FETCH_COLUMN);

        $info['fillable_properties'] = $reflection->getProperty('fillable')->getDefaultValue();
        $info['cast_properties'] = $reflection->getProperty('casts')->getDefaultValue();

        $info['relations'] = [];
        $relationMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($relationMethods as $method)
        {
            if (!in_array($method->getName(), static::METHODS_TO_EXCLUDE))
            {
                if (count($method->getParameters()) == 0)
                {
                    $result = $method->invoke(new $modelName);
                    switch ($result::class)
                    {
                        case BelongsTo::class:
                            $temp_info = [
                                'relation' => 'belongs_to',
                                'table' => (new \ReflectionClass($result->className))->getProperty('table')->getDefaultValue(),
                                'class_name' => $result->className,
                                'foreign_key' => $result->foreignKey,
                                'owner_key' => $result->localKey,
                                'primary_key' => $result->primaryKey,
                                'is_owner' => false
                            ];
                            break;
                        case HasOne::class:
                            $temp_info = [
                                'relation' => 'has_one',
                                'table' => (new \ReflectionClass($result->className))->getProperty('table')->getDefaultValue(),
                                'class_name' => $result->className,
                                'foreign_key' => $result->foreignKey,
                                'owner_key' => $result->localKey,
                                'primary_key' => $result->primaryKey,
                                'is_owner' => true
                            ];
                            break;
                        case HasMany::class:
                            $temp_info = [
                                'relation' => 'has_many',
                                'table' => (new \ReflectionClass($result->className))->getProperty('table')->getDefaultValue(),
                                'class_name' => $result->className,
                                'foreign_key' => $result->foreignKey,
                                'owner_key' => $result->localKey,
                                'primary_key' => $result->primaryKey,
                                'is_owner' => true
                            ];
                            break;
                        default:
                            $temp_info = null;
                    }

                    if ($temp_info != null) {
                        $info['relations'][$method->getName()] = $temp_info;
                    }
                }
            }
        }

        return $info;
    }
}