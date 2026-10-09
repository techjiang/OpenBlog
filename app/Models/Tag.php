<?php
/**
 * OpenBlog - 标签模型
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Model;

class Tag extends Model
{
    protected static string $table = 'ob_tags';

    protected static array $fillable = ['name', 'slug'];

    public static function findBySlug(string $slug): ?array
    {
        return static::query()->where('slug', $slug)->first();
    }

    /** 标签云（含真实文章数） */
    public static function cloud(int $limit = 40): array
    {
        return App::instance()->db->fetchAll(
            "SELECT t.*, COUNT(pt.post_id) AS real_count
             FROM `ob_tags` t
             LEFT JOIN `ob_post_tag` pt ON pt.tag_id = t.id
             GROUP BY t.id
             HAVING real_count > 0
             ORDER BY real_count DESC, t.name ASC
             LIMIT {$limit}"
        );
    }

    public static function allWithCount(): array
    {
        return App::instance()->db->fetchAll(
            'SELECT t.*, COUNT(pt.post_id) AS real_count
             FROM `ob_tags` t
             LEFT JOIN `ob_post_tag` pt ON pt.tag_id = t.id
             GROUP BY t.id
             ORDER BY t.id DESC'
        );
    }
}
