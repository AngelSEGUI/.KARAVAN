<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Modèle de base : accès PDO + opérations CRUD génériques sur la table $table. */
abstract class Model
{
    protected static string $table;

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function find(int $id): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(string $orderBy = 'id'): array
    {
        return static::db()->query('SELECT * FROM ' . static::$table . ' ORDER BY ' . $orderBy)->fetchAll();
    }

    public static function create(array $data): int
    {
        $cols = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $cols),
            implode(', ', array_fill(0, count($cols), '?'))
        );
        static::db()->prepare($sql)->execute(array_values($data));
        return (int) static::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($data)));
        $stmt = static::db()->prepare('UPDATE ' . static::$table . " SET $set WHERE id = ?");
        $stmt->execute([...array_values($data), $id]);
    }

    public static function delete(int $id): void
    {
        static::db()->prepare('DELETE FROM ' . static::$table . ' WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        return (int) static::db()->query('SELECT COUNT(*) FROM ' . static::$table)->fetchColumn();
    }

    protected static function select(string $sql, array $params = []): array
    {
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected static function first(string $sql, array $params = []): ?array
    {
        return static::select($sql, $params)[0] ?? null;
    }
}
