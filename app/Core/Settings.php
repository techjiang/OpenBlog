<?php
/**
 * OpenBlog - 站点设置
 *
 * 启动时一次性载入 ob_settings 到内存，避免重复查询。
 */

declare(strict_types=1);

namespace App\Core;

class Settings
{
    /** @var array<string, string> */
    private static array $cache = [];

    private static bool $loaded = false;

    public static function boot(Database $db): void
    {
        if (self::$loaded) {
            return;
        }

        try {
            $rows = $db->fetchAll('SELECT `key`, `value` FROM `ob_settings`');
        } catch (\Throwable $e) {
            self::$loaded = true;
            return;
        }

        foreach ($rows as $row) {
            self::$cache[(string)$row['key']] = (string)$row['value'];
        }
        self::$loaded = true;
    }

    public static function get(string $key, string $default = ''): string
    {
        $value = self::$cache[$key] ?? $default;
        return $value === '' ? $default : $value;
    }

    public static function all(): array
    {
        return self::$cache;
    }

    public static function set(string $key, string $value): void
    {
        self::$cache[$key] = $value;

        $db = App::instance()->db;
        $exists = $db->fetchColumn('SELECT COUNT(*) FROM `ob_settings` WHERE `key` = :k', ['k' => $key]);

        if ((int)$exists > 0) {
            $db->update('ob_settings', ['value' => $value], '`key` = :k', ['k' => $key]);
        } else {
            $db->insert('ob_settings', ['key' => $key, 'value' => $value]);
        }
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::set((string)$key, (string)$value);
        }
    }
}
