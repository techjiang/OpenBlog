<?php
/**
 * OpenBlog - 模型基类
 *
 * 提供表映射与常用 CRUD，子类只需声明 $table / $fillable。
 */

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    /** @var array<int, string> */
    protected static array $fillable = [];

    /** @var array<string, mixed> */
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public static function db(): Database
    {
        return App::instance()->db;
    }

    public static function query(): Query
    {
        return static::db()->table(static::$table);
    }

    public static function find(int $id): ?static
    {
        $row = static::query()->where(static::$primaryKey, $id)->first();
        return $row ? new static($row) : null;
    }

    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        if ($model === null) {
            throw new \RuntimeException('记录不存在');
        }
        return $model;
    }

    public static function where(string $column, mixed $value): ?static
    {
        $row = static::query()->where($column, $value)->first();
        return $row ? new static($row) : null;
    }

    public static function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        return array_map(
            static fn (array $row) => new static($row),
            static::query()->orderBy($orderBy, $direction)->get()
        );
    }

    public static function count(): int
    {
        return static::query()->count();
    }

    public static function create(array $data): int
    {
        $data = static::filterFillable($data);
        $data['created_at'] ??= date('Y-m-d H:i:s');
        $data['updated_at'] ??= date('Y-m-d H:i:s');
        return static::db()->insert(static::$table, $data);
    }

    public static function updateById(int $id, array $data): int
    {
        $data = static::filterFillable($data);
        unset($data['created_at']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        return static::db()->update(
            static::$table,
            $data,
            '`' . static::$primaryKey . '` = :id',
            ['id' => $id]
        );
    }

    public static function deleteById(int $id): int
    {
        return static::db()->delete(static::$table, '`' . static::$primaryKey . '` = :id', ['id' => $id]);
    }

    public function save(): bool
    {
        $id = $this->attributes[static::$primaryKey] ?? null;
        if ($id === null) {
            return false;
        }
        $data = static::filterFillable($this->attributes);
        unset($data[static::$primaryKey]);
        return static::updateById((int)$id, $data) >= 0;
    }

    public function delete(): int
    {
        return static::deleteById((int)$this->id);
    }

    protected static function filterFillable(array $data): array
    {
        if (static::$fillable === []) {
            return $data;
        }
        return array_intersect_key($data, array_flip(static::$fillable));
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function id(): int
    {
        return (int)($this->attributes[static::$primaryKey] ?? 0);
    }
}
