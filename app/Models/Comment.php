<?php
/**
 * OpenBlog - 评论模型
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Model;
use App\Core\Settings;

class Comment extends Model
{
    protected static string $table = 'ob_comments';

    protected static array $fillable = [
        'post_id', 'parent_id', 'user_id', 'author_name', 'author_email',
        'author_website', 'content', 'status', 'ip', 'user_agent',
    ];

    /** 文章下的已审核评论（树形结构） */
    public static function tree(int $postId): array
    {
        $rows = App::instance()->db->fetchAll(
            'SELECT * FROM `ob_comments`
             WHERE `post_id` = :pid AND `status` = \'approved\'
             ORDER BY `created_at` ASC',
            ['pid' => $postId]
        );

        return self::buildTree($rows);
    }

    private static function buildTree(array $rows, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($rows as $row) {
            $currentParent = $row['parent_id'] === null ? null : (int)$row['parent_id'];
            if ($currentParent === $parentId) {
                $children = self::buildTree($rows, (int)$row['id']);
                if ($children !== []) {
                    $row['children'] = $children;
                }
                $branch[] = $row;
            }
        }
        return $branch;
    }

    public static function countFor(int $postId): int
    {
        return (int)App::instance()->db->fetchColumn(
            'SELECT COUNT(*) FROM `ob_comments` WHERE `post_id` = :p AND `status` = \'approved\'',
            ['p' => $postId]
        );
    }

    /** 提交评论 */
    public static function submit(int $postId, array $input, ?int $userId): array
    {
        $content = trim((string)($input['content'] ?? ''));
        $name = trim((string)($input['author_name'] ?? ''));
        $email = trim((string)($input['author_email'] ?? ''));
        $website = trim((string)($input['author_website'] ?? ''));

        if ($content === '') {
            return [false, '评论内容不能为空'];
        }
        if (mb_strlen($content) > 2000) {
            return [false, '评论内容过长'];
        }
        if ($userId === null) {
            if ($name === '') {
                return [false, '请填写昵称'];
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [false, '请填写正确的邮箱'];
            }
        }

        $status = Settings::get('comment_review', '1') === '1' ? 'pending' : 'approved';

        $id = static::create([
            'post_id'        => $postId,
            'parent_id'      => ($input['parent_id'] ?? '') !== '' ? (int)$input['parent_id'] : null,
            'user_id'        => $userId,
            'author_name'    => $name,
            'author_email'   => $email,
            'author_website' => $website !== '' && filter_var($website, FILTER_VALIDATE_URL) ? $website : null,
            'content'        => $content,
            'status'         => $status,
            'ip'             => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent'     => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        App::instance()->db->query(
            'UPDATE `ob_posts` SET `comment_count` = (SELECT COUNT(*) FROM `ob_comments` WHERE `post_id` = :p AND `status` = \'approved\') WHERE `id` = :p2',
            ['p' => $postId, 'p2' => $postId]
        );

        return [true, $status === 'pending' ? '评论已提交，等待审核' : '评论发布成功'];
    }

    public static function adminList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $q = App::instance()->db->table('ob_comments c');
        $q->select('c.*', 'p.title AS post_title', 'p.slug AS post_slug');
        $q->leftJoin('ob_posts p', 'p.id = c.post_id');

        if (($filters['status'] ?? '') !== '') {
            $q->where('c.status', $filters['status']);
        }
        if (($filters['keyword'] ?? '') !== '') {
            $q->whereLike('c.content', $filters['keyword'], 'c.author_name');
        }

        return $q->orderByRaw('c.created_at DESC')->paginate($perPage, $page);
    }
}
