<?php
/**
 * OpenBlog - 分类模型
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Model;

class Category extends Model
{
    protected static string $table = 'ob_categories';

    protected static array $fillable = ['name', 'slug', 'description', 'color', 'parent_id', 'sort_order'];

    public static function withCount(): array
    {
        return App::instance()->db->fetchAll(
            'SELECT c.*, COUNT(p.id) AS real_count
             FROM `ob_categories` c
             LEFT JOIN `ob_posts` p ON p.category_id = c.id AND p.status = \'published\' AND p.type = \'post\'
             GROUP BY c.id
             ORDER BY c.sort_order ASC, c.id ASC'
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return static::query()->where('slug', $slug)->first();
    }

    /** 分类下拉（含层级缩进） */
    public static function options(?int $excludeId = null): array
    {
        $rows = static::query()
            ->select('id', 'name', 'parent_id')
            ->orderBy('sort_order', 'ASC')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id']] = $row;
        }

        $options = [];
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            if ($excludeId !== null && $id === $excludeId) {
                continue;
            }
            $depth = 0;
            $parent = $row['parent_id'] ? (int)$row['parent_id'] : 0;
            while ($parent > 0 && isset($map[$parent]) && $depth < 5) {
                $depth++;
                $parent = $map[$parent]['parent_id'] ? (int)$map[$parent]['parent_id'] : 0;
            }
            $options[$id] = str_repeat('　', $depth) . $row['name'];
        }
        return $options;
    }
}
