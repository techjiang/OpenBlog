<?php
/**
 * OpenBlog - 轻量 JSON API
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;

class ApiController extends Controller
{
    public function search(): string
    {
        $keyword = trim((string)$this->request->query('q', ''));

        if (mb_strlen($keyword) < 1) {
            return $this->json(['items' => []]);
        }

        $rows = Post::published()
            ->select('id', 'title', 'slug', 'excerpt', 'published_at')
            ->whereLike('title', $keyword, 'excerpt')
            ->limit(10)
            ->get();

        return $this->json([
            'keyword' => $keyword,
            'items'   => array_map(static fn (array $r) => [
                'title'  => $r['title'],
                'url'    => url('post/' . $r['slug']),
                'excerpt'=> mb_substr((string)($r['excerpt'] ?? ''), 0, 80),
                'date'   => date('Y-m-d', strtotime((string)$r['published_at'])),
            ], $rows),
        ]);
    }

    public function posts(): string
    {
        $limit = min(50, max(1, $this->request->int('limit', 10)));
        $page = max(1, $this->request->int('page', 1));

        $result = Post::paginatePublished($page, $limit);

        return $this->json([
            'total' => $result['total'],
            'page'  => $result['current'],
            'items' => array_map(static fn (array $r) => [
                'id'         => (int)$r['id'],
                'title'      => $r['title'],
                'slug'       => $r['slug'],
                'excerpt'    => $r['excerpt'],
                'cover'      => $r['cover_image'],
                'category'   => $r['category_name'] ?? null,
                'author'     => $r['author_name'] ?? $r['author_username'] ?? null,
                'views'      => (int)$r['view_count'],
                'likes'      => (int)$r['like_count'],
                'comments'   => (int)$r['comment_count'],
                'published'  => $r['published_at'],
                'url'        => url('post/' . $r['slug']),
            ], $result['items']),
        ]);
    }
}
