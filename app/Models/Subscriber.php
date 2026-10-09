<?php
/**
 * OpenBlog - 订阅者模型
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Subscriber extends Model
{
    protected static string $table = 'ob_subscribers';

    protected static array $fillable = ['email', 'status'];

    public static function subscribe(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        static::db()->query(
            'INSERT IGNORE INTO `ob_subscribers` (`email`, `status`) VALUES (:e, 1)',
            ['e' => $email]
        );
        return true;
    }

    public static function allRecent(int $limit = 200): array
    {
        return static::query()->orderBy('id', 'DESC')->limit($limit)->get();
    }
}
