<?php
/**
 * OpenBlog - 用户模型
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'ob_users';

    protected static array $fillable = [
        'username', 'email', 'password_hash', 'display_name', 'avatar',
        'bio', 'website', 'role', 'status',
    ];

    public static function createUser(array $data): int
    {
        $payload = [
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => password_hash((string)$data['password'], PASSWORD_DEFAULT),
            'display_name'  => $data['display_name'] ?? $data['username'],
            'role'          => $data['role'] ?? 'author',
            'status'        => 1,
            'website'       => $data['website'] ?? null,
            'bio'           => $data['bio'] ?? null,
            'avatar'        => $data['avatar'] ?? null,
        ];
        return static::create($payload);
    }

    public static function updateUser(int $id, array $data): void
    {
        $payload = array_intersect_key($data, array_flip(['username', 'email', 'display_name', 'role', 'status', 'website', 'bio', 'avatar']));

        if (($data['password'] ?? '') !== '') {
            $payload['password_hash'] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
        }

        static::updateById($id, $payload);
    }

    public static function findByUsername(string $username): ?array
    {
        return static::query()->where('username', $username)->first();
    }

    public static function allWithStats(): array
    {
        $db = static::db();
        return $db->fetchAll(
            'SELECT u.*, COUNT(p.id) AS post_count
             FROM `ob_users` u
             LEFT JOIN `ob_posts` p ON p.user_id = u.id AND p.type = \'post\'
             GROUP BY u.id
             ORDER BY u.id ASC'
        );
    }
}
