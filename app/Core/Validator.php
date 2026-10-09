<?php
/**
 * OpenBlog - 表单校验
 */

declare(strict_types=1);

namespace App\Core;

class Validator
{
    /** @var array<string, array<int, string>> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $data;

    /** @var array<string, array<int, string>> */
    private array $rules;

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $value = $this->data[$field] ?? null;
            $label = $field;

            foreach ($ruleSet as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

                $error = match ($name) {
                    'required' => ($value === null || $value === '' || (is_array($value) && $value === []))
                        ? "{$label} 不能为空" : null,
                    'email'    => ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL))
                        ? "{$label} 格式不正确" : null,
                    'url'      => ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL))
                        ? "{$label} 必须是合法链接" : null,
                    'min'      => (is_string($value) && $value !== '' && mb_strlen($value) < (int)$param)
                        ? "{$label} 至少需要 {$param} 个字符" : null,
                    'max'      => (is_string($value) && mb_strlen($value) > (int)$param)
                        ? "{$label} 不能超过 {$param} 个字符" : null,
                    'numeric'  => ($value !== null && $value !== '' && !is_numeric($value))
                        ? "{$label} 必须是数字" : null,
                    'slug'     => ($value !== null && $value !== '' && !preg_match('/^[a-zA-Z0-9\-_]+$/', (string)$value))
                        ? "{$label} 只能包含字母、数字、连字符和下划线" : null,
                    'in'       => ($value !== null && $value !== '' && !in_array((string)$value, explode(',', (string)$param), true))
                        ? "{$label} 取值非法" : null,
                    'unique'   => $this->checkUnique($field, $value, (string)$param)
                        ? "{$label} 已存在" : null,
                    'confirmed' => (($this->data[$field . '_confirmation'] ?? null) !== $value)
                        ? "{$label} 两次输入不一致" : null,
                    default    => null,
                };

                if ($error !== null) {
                    $this->errors[$field][] = $error;
                }
            }
        }
    }

    private function checkUnique(string $field, mixed $value, string $param): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        [$table, $column, $except] = array_pad(explode(',', $param), 3, null);
        $column ??= $field;

        $db = App::instance()->db;
        $sql = sprintf('SELECT COUNT(*) FROM `%s` WHERE `%s` = :v', $table, $column);
        $params = ['v' => $value];

        if ($except !== null && $except !== '') {
            $sql .= ' AND `id` != :e';
            $params['e'] = (int)$except;
        }

        return (int)$db->fetchColumn($sql, $params) > 0;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}
