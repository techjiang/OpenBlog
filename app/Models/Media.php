<?php
/**
 * OpenBlog - 媒体模型
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Media extends Model
{
    protected static string $table = 'ob_media';

    protected static array $fillable = ['user_id', 'filename', 'original_name', 'mime_type', 'size', 'width', 'height'];

    public static function recent(int $limit = 60): array
    {
        return static::query()->orderBy('id', 'DESC')->limit($limit)->get();
    }
}
