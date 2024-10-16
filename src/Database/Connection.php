<?php

namespace VanyaPhp\CustomFramework\Database;

use PDO;

class Connection
{
    private ?PDO $connection;

    public function __construct($params)
    {
        $this->connection = new PDO(
            sprintf("%s:host=%s;dbname=%s",
                $params['connection'],
                $params['host'],
                $params['database']
            ),
            $params['username'],
            $params['password'],
        );
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public function getConnection(): ?PDO
    {
        return $this->connection;
    }
}