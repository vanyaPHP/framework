<?php

namespace VanyaPhp\CustomFramework\Contracts\DataMapper;

interface DataMapperInterface
{
    public function getModelInfo(string $modelName): array;
}