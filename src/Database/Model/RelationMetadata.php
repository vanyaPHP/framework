<?php

namespace VanyaPhp\CustomFramework\Database\Model;

readonly class RelationMetadata
{
    public function __construct(
        public string $relationType,
        public string $belongTable,
        public string $belongTableKey,
        public string $ownerTable,
        public string $ownerTableKey,
    ) {

    }
}