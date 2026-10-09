<?php
/**
 * OpenBlog - 数据库层（PDO / MySQL）
 */

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use RuntimeException;

class Database
{
    private PDO $pdo;

    /** @var array<string, mixed> */
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pdo = $this->connect();
    }

    private function connect(): PDO
    {
        $driver = $this->config['driver'] ?? 'mysql';

        if ($driver === 'sqlite') {
            $path = $this->config['path'] ?? ($this->config['database'] ?? ':memory:');
            $dsn  = 'sqlite:' . $path;
            $pdo  = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
            // 兼容 MySQL 的 DATE_FORMAT，便于在不安装数据库的情况下运行
            $pdo->sqliteCreateFunction('DATE_FORMAT', static function ($date, $format) {
                if ($date === null) {
                    return null;
                }
                try {
                    $dt = new DateTime((string)$date);
                } catch (\Throwable $e) {
                    return null;
                }
                $php = strtr((string)$format, [
                    '%Y' => 'Y', '%y' => 'y', '%m' => 'm', '%d' => 'd', '%H' => 'H',
                    '%i' => 'i', '%s' => 's', '%M' => 'F', '%W' => 'l', '%T' => 'H:i:s',
                ]);
                return $dt->format($php);
            }, 2);
            return $pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['charset'] ?? 'utf8mb4'
        );

        try {
            $pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (\PDOException $e) {
            throw new RuntimeException('数据库连接失败：' . $e->getMessage(), 0, $e);
        }

        return $pdo;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->normalizeParams($params));
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        $value = $this->query($sql, $params)->fetchColumn($column);
        return $value === false ? null : $value;
    }

    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $columns),
            implode(', ', array_map(static fn ($c) => ':' . $c, $columns))
        );
        $this->query($sql, $data);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = sprintf('`%s` = :_set_%s', $col, $col);
        }
        $params = [];
        foreach ($data as $col => $val) {
            $params['_set_' . $col] = $val;
        }
        foreach ($whereParams as $k => $v) {
            $params['_where_' . $k] = $v;
        }
        $where = preg_replace_callback('/:(\w+)/', static fn ($m) => ':_where_' . $m[1], $where);

        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, implode(', ', $sets), $where);
        return $this->query($sql, $params)->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->query(sprintf('DELETE FROM `%s` WHERE %s', $table, $where), $params)->rowCount();
    }

    public function lastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function table(string $table): Query
    {
        return (new Query($this))->from($table);
    }

    /**
     * PDO 无法直接绑定 null/bool，这里做一次归一化。
     */
    private function normalizeParams(array $params): array
    {
        foreach ($params as $k => $v) {
            if (is_bool($v)) {
                $params[$k] = (int)$v;
            }
        }
        return $params;
    }
}
