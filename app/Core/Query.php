<?php
/**
 * OpenBlog - 轻量查询构造器
 *
 * 提供链式调用： ->select()->where()->orderBy()->limit()->get()
 */

declare(strict_types=1);

namespace App\Core;

class Query
{
    private Database $db;

    private string $table = '';
    private string $select = '*';
    /** @var array<int, string> */
    private array $joins = [];
    /** @var array<int, array{0:string,1:string}> */
    private array $wheres = [];
    /** @var array<int, string> */
    private array $orders = [];
    private ?int $limitValue = null;
    private ?int $offsetValue = null;
    private string $groupBy = '';
    private string $having = '';

    /** @var array<string, mixed> */
    private array $bindings = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function from(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(string ...$columns): self
    {
        $this->select = implode(', ', $columns);
        return $this;
    }

    public function join(string $table, string $on, string $type = 'INNER'): self
    {
        $this->joins[] = sprintf('%s JOIN %s ON %s', strtoupper($type), $table, $on);
        return $this;
    }

    /**
     * 列名转义：支持 `alias.column` 形式，并兼容调用方已加反引号的情况。
     */
    private function quoteColumn(string $column): string
    {
        $column = trim($column);
        if ($column === '') {
            return $column;
        }
        if (str_contains($column, '.')) {
            return implode('.', array_map(
                static fn (string $part): string => '`' . trim(trim($part), '`') . '`',
                explode('.', $column)
            ));
        }
        return '`' . trim($column, '`') . '`';
    }

    public function leftJoin(string $table, string $on): self
    {
        return $this->join($table, $on, 'LEFT');
    }

    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $placeholder = $this->bind($value);
        $this->wheres[] = [sprintf('%s %s %s', $this->quoteColumn($column), $operator, $placeholder), 'AND'];
        return $this;
    }

    public function orWhere(string $column, mixed $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $placeholder = $this->bind($value);
        $this->wheres[] = [sprintf('%s %s %s', $this->quoteColumn($column), $operator, $placeholder), 'OR'];
        return $this;
    }

    public function whereRaw(string $raw, array $bindings = []): self
    {
        foreach ($bindings as $value) {
            $placeholder = $this->bind($value);
            $raw = preg_replace('/\?/', $placeholder, $raw, 1) ?? $raw;
        }
        $this->wheres[] = [$raw, 'AND'];
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = [sprintf('%s IS NULL', $this->quoteColumn($column)), 'AND'];
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = [sprintf('%s IS NOT NULL', $this->quoteColumn($column)), 'AND'];
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        if ($values === []) {
            $this->wheres[] = ['1 = 0', 'AND'];
            return $this;
        }
        $ph = [];
        foreach ($values as $v) {
            $ph[] = $this->bind($v);
        }
        $this->wheres[] = [sprintf('%s IN (%s)', $this->quoteColumn($column), implode(', ', $ph)), 'AND'];
        return $this;
    }

    public function whereLike(string $column, string $keyword, string ...$extra): self
    {
        $columns = array_merge([$column], $extra);
        $parts = [];
        foreach ($columns as $col) {
            $parts[] = sprintf('%s LIKE %s', $this->quoteColumn($col), $this->bind('%' . $keyword . '%'));
        }
        $this->wheres[] = ['(' . implode(' OR ', $parts) . ')', 'AND'];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = sprintf('%s %s', $this->quoteColumn($column), $dir);
        return $this;
    }

    public function orderByRaw(string $raw): self
    {
        $this->orders[] = $raw;
        return $this;
    }

    public function groupBy(string $column): self
    {
        $this->groupBy = 'GROUP BY ' . $this->quoteColumn($column);
        return $this;
    }

    public function having(string $raw): self
    {
        $this->having = 'HAVING ' . $raw;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offsetValue = $offset;
        return $this;
    }

    public function page(int $page, int $perPage): self
    {
        $page = max(1, $page);
        return $this->limit($perPage)->offset(($page - 1) * $perPage);
    }

    private function bind(mixed $value): string
    {
        $key = ':b' . count($this->bindings);
        $this->bindings[$key] = $value;
        return $key;
    }

    private function buildSql(bool $count = false): string
    {
        $sql = $count
            ? sprintf('SELECT COUNT(*) AS `aggregate` FROM %s', $this->table)
            : sprintf('SELECT %s FROM %s', $this->select, $this->table);

        if ($this->joins !== []) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if ($this->wheres !== []) {
            $parts = [];
            foreach ($this->wheres as $i => [$cond, $logic]) {
                $parts[] = ($i === 0 ? '' : $logic . ' ') . $cond;
            }
            $sql .= ' WHERE ' . implode(' ', $parts);
        }

        if ($this->groupBy !== '') {
            $sql .= ' ' . $this->groupBy;
        }

        if ($this->having !== '') {
            $sql .= ' ' . $this->having;
        }

        if (!$count) {
            if ($this->orders !== []) {
                $sql .= ' ORDER BY ' . implode(', ', $this->orders);
            }
            if ($this->limitValue !== null) {
                $sql .= ' LIMIT ' . $this->limitValue;
                if ($this->offsetValue !== null) {
                    $sql .= ' OFFSET ' . $this->offsetValue;
                }
            }
        }

        return $sql;
    }

    public function get(): array
    {
        return $this->db->fetchAll($this->buildSql(), $this->bindings);
    }

    public function first(): ?array
    {
        $row = $this->db->fetch($this->buildSql() . ' LIMIT 1', $this->bindings);
        return $row;
    }

    public function count(): int
    {
        $row = $this->db->fetch($this->buildSql(true), $this->bindings);
        return (int)($row['aggregate'] ?? 0);
    }

    public function paginate(int $perPage = 10, int $page = 1): array
    {
        // 用克隆体统计总数，避免占位符混入后续查询
        $counter = clone $this;
        $total = $counter->count();

        $page = max(1, $page);
        $items = $this->page($page, $perPage)->get();

        return [
            'items'       => $items,
            'total'       => $total,
            'per_page'    => $perPage,
            'current'     => $page,
            'last_page'   => (int)max(1, ceil($total / $perPage)),
        ];
    }
}
