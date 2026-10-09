<?php
/**
 * OpenBlog - 认证与授权
 */

declare(strict_types=1);

namespace App\Core;

class Auth
{
    private const KEY = 'auth_user_id';

    /** @var array<string, mixed>|null */
    private static ?array $user = null;

    public static function attempt(string $account, string $password, bool $remember = false): bool
    {
        $db = App::instance()->db;

        $user = $db->fetch(
            'SELECT * FROM `ob_users` WHERE (`username` = :a OR `email` = :a2) AND `status` = 1 LIMIT 1',
            ['a' => $account, 'a2' => $account]
        );

        if ($user === null || !password_verify($password, (string)$user['password_hash'])) {
            return false;
        }

        if (password_needs_rehash((string)$user['password_hash'], PASSWORD_DEFAULT)) {
            $db->update('ob_users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], '`id` = :id', ['id' => $user['id']]);
        }

        Session::regenerate();
        Session::set(self::KEY, (int)$user['id']);
        self::$user = $user;

        $db->update('ob_users', ['last_login_at' => date('Y-m-d H:i:s')], '`id` = :id', ['id' => $user['id']]);

        if ($remember) {
            self::issueRememberToken((int)$user['id']);
        }

        return true;
    }

    private static function issueRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $db = App::instance()->db;
        $db->update('ob_users', ['remember_token' => hash('sha256', $token)], '`id` = :id', ['id' => $userId]);

        setcookie('openblog_remember', $userId . ':' . $token, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function resolveRemember(): void
    {
        if (self::check()) {
            return;
        }
        $cookie = $_COOKIE['openblog_remember'] ?? '';
        if (!is_string($cookie) || !str_contains($cookie, ':')) {
            return;
        }
        [$id, $token] = explode(':', $cookie, 2);
        $userId = (int)$id;
        if ($userId <= 0) {
            return;
        }

        $db = App::instance()->db;
        $user = $db->fetch('SELECT * FROM `ob_users` WHERE `id` = :id AND `status` = 1 LIMIT 1', ['id' => $userId]);

        if ($user !== null && hash_equals((string)$user['remember_token'], hash('sha256', $token))) {
            Session::set(self::KEY, $userId);
            self::$user = $user;
        }
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function id(): int
    {
        return (int)(self::user()['id'] ?? 0);
    }

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }

        $id = Session::get(self::KEY);
        if (!$id) {
            return null;
        }

        self::$user = App::instance()->db->fetch('SELECT * FROM `ob_users` WHERE `id` = :id LIMIT 1', ['id' => $id]);
        if (self::$user === null) {
            Session::forget(self::KEY);
        }
        return self::$user;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    public static function can(string $permission): bool
    {
        $role = (string)(self::user()['role'] ?? '');
        $matrix = [
            'admin'  => ['*'],
            'editor' => ['post.*', 'category.*', 'tag.*', 'comment.*', 'media.*', 'page.*'],
            'author' => ['post.create', 'post.edit.own', 'post.delete.own', 'media.upload'],
        ];

        $granted = $matrix[$role] ?? [];
        if (in_array('*', $granted, true)) {
            return true;
        }

        foreach ($granted as $p) {
            if ($p === $permission) {
                return true;
            }
            if (str_ends_with($p, '.*') && str_starts_with($permission, rtrim($p, '*'))) {
                return true;
            }
        }
        return false;
    }

    public static function logout(): void
    {
        Session::forget(self::KEY);
        self::$user = null;

        if (isset($_COOKIE['openblog_remember'])) {
            setcookie('openblog_remember', '', time() - 3600, '/');
            unset($_COOKIE['openblog_remember']);
        }
        Session::regenerate();
    }
}
