<?php

namespace App\Core;

use PDO;
use PDOException;

class Model
{
    protected static ?PDO $db = null;

    public static function getDB(): PDO
    {
        if (self::$db === null) {
            $config = require dirname(__DIR__) . '/config/database.php';

            try {
                $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
                self::$db = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$db;
    }

    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    protected function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    protected function execute(string $sql, array $params = []): bool
    {
        try {
            return $this->query($sql, $params)->rowCount() > 0;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    protected function lastInsertId(): string
    {
        return self::getDB()->lastInsertId();
    }
}
