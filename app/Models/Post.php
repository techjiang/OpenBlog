<?php
/**
 * OpenBlog - 文章模型
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Markdown;
use App\Core\Model;
use App\Core\Query;

class Post extends Model
{
    protected static string $table = 'ob_posts';

    protected static array $fillable = [
        'user_id', 'category_id', 'type', 'title', 'slug', 'excerpt', 'content',
        'content_html', 'cover_image', 'status', 'featured', 'allow_comment',
        'password', 'published_at', 'reading_time',
    ];

    /** 已发布文章查询 */
    public static function published(): Query
    {
        return static::query()
            ->where('status', 'published')
            ->where('type', 'post')
            ->orderBy('published_at', 'DESC');
    }

    /** 带作者与分类的查询 */
    public static function withMeta(): Query
    {
        $q = App::instance()->db->table('ob_posts p');
        $q->select(
            'p.*',
            'u.username AS author_username',
            'u.display_name AS author_name',
            'u.avatar AS author_avatar',
            'c.name AS category_name',
            'c.slug AS category_slug',
            'c.color AS category_color'
        );
        $q->leftJoin('ob_users u', 'u.id = p.user_id');
        $q->leftJoin('ob_categories c', 'c.id = p.category_id');
        return $q;
    }

    public static function paginatePublished(int $page = 1, int $perPage = 10): array
    {
        return static::withMeta()
            ->where('p.status', 'published')
            ->where('p.type', 'post')
            ->orderByRaw('p.published_at DESC')
            ->paginate($perPage, $page);
    }

    public static function paginateFeatured(int $page = 1, int $perPage = 10): array
    {
        return static::withMeta()
            ->where('p.status', 'published')
            ->where('p.type', 'post')
            ->where('p.featured', 1)
            ->orderByRaw('p.published_at DESC')
            ->paginate($perPage, $page);
    }

    public static function paginateByCategory(int $categoryId, int $page = 1, int $perPage = 10): array
    {
        return static::withMeta()
            ->where('p.status', 'published')
            ->where('p.type', 'post')
            ->where('p.category_id', $categoryId)
            ->orderByRaw('p.published_at DESC')
            ->paginate($perPage, $page);
    }

    public static function paginateByTag(int $tagId, int $page = 1, int $perPage = 10): array
    {
        $q = static::withMeta();
        $q->join('ob_post_tag pt', 'pt.post_id = p.id');
        return $q->where('p.status', 'published')
            ->where('p.type', 'post')
            ->where('pt.tag_id', $tagId)
            ->orderByRaw('p.published_at DESC')
            ->paginate($perPage, $page);
    }

    public static function paginateByAuthor(int $userId, int $page = 1, int $perPage = 10): array
    {
        return static::withMeta()
            ->where('p.status', 'published')
            ->where('p.type', 'post')
            ->where('p.user_id', $userId)
            ->orderByRaw('p.published_at DESC')
            ->paginate($perPage, $page);
    }

    public static function paginateByMonth(string $yearMonth, int $page = 1, int $perPage = 10): array
    {
        return static::withMeta()
            ->where('p.status', 'published')
            ->where('p.type', 'post')
            ->whereRaw("DATE_FORMAT(p.published_at, '%Y-%m') = ?", [$yearMonth])
            ->orderByRaw('p.published_at DESC')
            ->paginate($perPage, $page);
    }

    /** 关键词搜索（标题 / 摘要 / 正文） */
    public static function paginateSearch(string $keyword, int $page = 1, int $perPage = 10): array
    {
        return static::withMeta()
            ->where('p.status', 'published')
            ->where('p.type', 'post')
            ->whereLike('p.title', $keyword, 'p.excerpt', 'p.content')
            ->orderByRaw('p.published_at DESC')
            ->paginate($perPage, $page);
    }

    /** 前台文章详情 */
    public static function findPublishedBySlug(string $slug): ?array
    {
        $post = static::withMeta()
            ->where('p.status', 'published')
            ->where('p.slug', $slug)
            ->first();

        if ($post !== null) {
            $post['tags'] = static::tagsOf((int)$post['id']);
        }
        return $post;
    }

    public static function findAnyBySlug(string $slug): ?array
    {
        return static::withMeta()->where('p.slug', $slug)->first();
    }

    public static function findPageBySlug(string $slug): ?array
    {
        return static::withMeta()
            ->where('p.status', 'published')
            ->where('p.type', 'page')
            ->where('p.slug', $slug)
            ->first();
    }

    public static function findById(int $id): ?array
    {
        $post = static::withMeta()->where('p.id', $id)->first();
        if ($post !== null) {
            $post['tags'] = static::tagsOf($id);
        }
        return $post;
    }

    /** 文章标签 */
    public static function tagsOf(int $postId): array
    {
        return App::instance()->db->fetchAll(
            'SELECT t.* FROM `ob_tags` t
             INNER JOIN `ob_post_tag` pt ON pt.tag_id = t.id
             WHERE pt.post_id = :pid ORDER BY t.name',
            ['pid' => $postId]
        );
    }

    /** 同步标签，传入标签名数组 */
    public static function syncTags(int $postId, array $names): void
    {
        $db = App::instance()->db;
        $db->delete('ob_post_tag', '`post_id` = :pid', ['pid' => $postId]);

        $ids = [];
        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }
            $slug = slugify($name);
            $tag = $db->fetch('SELECT id FROM `ob_tags` WHERE `slug` = :s LIMIT 1', ['s' => $slug]);

            if ($tag === null) {
                $ids[] = $db->insert('ob_tags', ['name' => $name, 'slug' => $slug]);
            } else {
                $ids[] = (int)$tag['id'];
            }
        }

        foreach ($ids as $tagId) {
            $db->query(
                'INSERT IGNORE INTO `ob_post_tag` (`post_id`, `tag_id`) VALUES (:p, :t)',
                ['p' => $postId, 't' => $tagId]
            );
        }

        foreach ($ids as $tagId) {
            $db->query(
                'UPDATE `ob_tags` SET `post_count` = (SELECT COUNT(*) FROM `ob_post_tag` WHERE `tag_id` = :t) WHERE `id` = :t2',
                ['t' => $tagId, 't2' => $tagId]
            );
        }
    }

    /** 相关文章（同分类或共享标签） */
    public static function related(array $post, int $limit = 4): array
    {
        $db = App::instance()->db;
        return $db->fetchAll(
            "SELECT p.id, p.title, p.slug, p.cover_image, p.published_at, p.excerpt
             FROM `ob_posts` p
             WHERE p.status = 'published' AND p.type = 'post' AND p.id != :id
               AND (p.category_id = :cid OR p.id IN (
                    SELECT pt2.post_id FROM `ob_post_tag` pt2
                    WHERE pt2.tag_id IN (SELECT pt.tag_id FROM `ob_post_tag` pt WHERE pt.post_id = :id2)
               ))
             ORDER BY p.published_at DESC LIMIT {$limit}",
            ['id' => $post['id'], 'id2' => $post['id'], 'cid' => $post['category_id'] ?? 0]
        );
    }

    /** 上一篇 / 下一篇 */
    public static function neighbours(array $post): array
    {
        $db = App::instance()->db;
        $time = $post['published_at'];

        $prev = $db->fetch(
            "SELECT id, title, slug FROM `ob_posts`
             WHERE status = 'published' AND type = 'post' AND published_at < :t
             ORDER BY published_at DESC LIMIT 1",
            ['t' => $time]
        );
        $next = $db->fetch(
            "SELECT id, title, slug FROM `ob_posts`
             WHERE status = 'published' AND type = 'post' AND published_at > :t
             ORDER BY published_at ASC LIMIT 1",
            ['t' => $time]
        );

        return [$prev, $next];
    }

    /** 归档统计 */
    public static function archives(): array
    {
        return App::instance()->db->fetchAll(
            "SELECT DATE_FORMAT(published_at, '%Y') AS year,
                    DATE_FORMAT(published_at, '%m') AS month,
                    COUNT(*) AS total
             FROM `ob_posts`
             WHERE status = 'published' AND type = 'post'
             GROUP BY year, month
             ORDER BY year DESC, month DESC"
        );
    }

    /** 热门文章 */
    public static function popular(int $limit = 5): array
    {
        return static::query()
            ->select('id', 'title', 'slug', 'view_count')
            ->where('status', 'published')
            ->where('type', 'post')
            ->orderBy('view_count', 'DESC')
            ->limit($limit)
            ->get();
    }

    /** 最新文章 */
    public static function latest(int $limit = 5): array
    {
        return static::query()
            ->select('id', 'title', 'slug', 'published_at', 'cover_image')
            ->where('status', 'published')
            ->where('type', 'post')
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /** 浏览量 +1 */
    public static function incrementViews(int $id): void
    {
        App::instance()->db->query('UPDATE `ob_posts` SET `view_count` = `view_count` + 1 WHERE `id` = :id', ['id' => $id]);
    }

    /** 点赞（同 IP 24 小时内只计一次） */
    public static function like(int $id, string $ip): bool
    {
        $db = App::instance()->db;
        $hash = hash('sha256', $ip . date('Y-m-d'));

        $exists = $db->fetchColumn(
            'SELECT COUNT(*) FROM `ob_post_likes` WHERE `post_id` = :p AND `ip_hash` = :h',
            ['p' => $id, 'h' => $hash]
        );

        if ((int)$exists > 0) {
            return false;
        }

        $db->query('INSERT INTO `ob_post_likes` (`post_id`, `ip_hash`) VALUES (:p, :h)', ['p' => $id, 'h' => $hash]);
        $db->query('UPDATE `ob_posts` SET `like_count` = `like_count` + 1 WHERE `id` = :id', ['id' => $id]);
        return true;
    }

    /** 保存（含 Markdown 转换与摘要生成） */
    public static function savePost(?int $id, array $data): int
    {
        $content = (string)($data['content'] ?? '');
        $markdown = new Markdown();

        $payload = [
            'title'        => $data['title'] ?? '',
            'slug'         => $data['slug'] ?? '',
            'excerpt'      => ($data['excerpt'] ?? '') !== '' ? $data['excerpt'] : Markdown::excerpt($content, 180),
            'content'      => $content,
            'content_html' => $markdown->parse($content),
            'category_id'  => ($data['category_id'] ?? '') !== '' ? (int)$data['category_id'] : null,
            'status'       => $data['status'] ?? 'draft',
            'type'         => $data['type'] ?? 'post',
            'cover_image'  => $data['cover_image'] ?? null,
            'featured'     => !empty($data['featured']) ? 1 : 0,
            'allow_comment' => !empty($data['allow_comment']) ? 1 : 0,
            'reading_time' => Markdown::readingTime($content),
        ];

        if ($payload['status'] === 'published' && empty($data['published_at'])) {
            $existing = $id === null ? null : (static::findRaw($id)['published_at'] ?? null);
            $payload['published_at'] = $existing ?? date('Y-m-d H:i:s');
        } elseif (!empty($data['published_at'])) {
            $payload['published_at'] = $data['published_at'];
        }

        if ($id === null) {
            $payload['user_id'] = $data['user_id'] ?? \App\Core\Auth::id();
            $newId = static::create($payload);
        } else {
            static::updateById($id, $payload);
            $newId = $id;
        }

        static::syncTags($newId, $data['tags'] ?? []);
        static::refreshCategoryCount();

        return $newId;
    }

    public static function findRaw(int $id): array
    {
        return static::query()->where('id', $id)->first() ?? [];
    }

    public static function refreshCategoryCount(): void
    {
        App::instance()->db->query(
            'UPDATE `ob_categories` c SET c.post_count = (
                SELECT COUNT(*) FROM `ob_posts` p WHERE p.category_id = c.id AND p.status = \'published\' AND p.type = \'post\'
            )'
        );
    }

    /** 后台列表（含筛选） */
    public static function adminList(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $q = static::withMeta()->where('p.type', $filters['type'] ?? 'post');

        if (($filters['status'] ?? '') !== '') {
            $q->where('p.status', $filters['status']);
        }
        if (($filters['category_id'] ?? '') !== '') {
            $q->where('p.category_id', (int)$filters['category_id']);
        }
        if (($filters['keyword'] ?? '') !== '') {
            $q->whereLike('p.title', $filters['keyword'], 'p.content');
        }

        return $q->orderByRaw('p.created_at DESC')->paginate($perPage, $page);
    }

    /** 统计 */
    public static function stats(): array
    {
        $db = App::instance()->db;
        return [
            'posts'    => (int)$db->fetchColumn("SELECT COUNT(*) FROM `ob_posts` WHERE type='post'"),
            'pages'    => (int)$db->fetchColumn("SELECT COUNT(*) FROM `ob_posts` WHERE type='page'"),
            'drafts'   => (int)$db->fetchColumn("SELECT COUNT(*) FROM `ob_posts` WHERE status='draft'"),
            'comments' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `ob_comments`"),
            'pending'  => (int)$db->fetchColumn("SELECT COUNT(*) FROM `ob_comments` WHERE status='pending'"),
            'views'    => (int)$db->fetchColumn("SELECT IFNULL(SUM(view_count),0) FROM `ob_posts`"),
            'likes'    => (int)$db->fetchColumn("SELECT IFNULL(SUM(like_count),0) FROM `ob_posts`"),
        ];
    }
}
