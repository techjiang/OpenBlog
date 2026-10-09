<?php
/**
 * OpenBlog - CSRF 防护与安全助手
 */

declare(strict_types=1);

namespace App\Core;

class Security
{
    private const TOKEN_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::TOKEN_KEY);
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
        }
        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . self::token() . '">';
    }

    public static function meta(): string
    {
        return '<meta name="csrf-token" content="' . self::token() . '">';
    }

    public static function verify(?string $token = null): bool
    {
        $token ??= (string)($_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return $token !== '' && hash_equals(self::token(), $token);
    }

    public static function abortIfInvalid(): void
    {
        if (!self::verify()) {
            http_response_code(419);
            echo '<!doctype html><meta charset="utf-8">';
            echo '<div style="font-family:system-ui;padding:80px;text-align:center">';
            echo '<h1>419</h1><p>页面已过期，请刷新后重试。</p></div>';
            exit;
        }
    }

    /** 生成随机文件名，避免目录穿越 */
    public static function randomName(int $length = 24): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /** 简单速率限制（基于会话） */
    public static function throttle(string $key, int $limit = 5, int $seconds = 60): bool
    {
        $bucket = Session::get('_throttle', []);
        $now = time();
        $bucket[$key] = array_filter($bucket[$key] ?? [], static fn ($t) => $t > $now - $seconds);

        if (count($bucket[$key]) >= $limit) {
            Session::set('_throttle', $bucket);
            return false;
        }

        $bucket[$key][] = $now;
        Session::set('_throttle', $bucket);
        return true;
    }
}
