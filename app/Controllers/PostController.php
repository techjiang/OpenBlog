<?php
/**
 * OpenBlog - 文章详情
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Markdown;
use App\Models\Comment;
use App\Models\Post;

class PostController extends Controller
{
    public function show(string $slug): string
    {
        $post = Post::findPublishedBySlug($slug);

        if ($post === null) {
            http_response_code(404);
            return $this->view->render('errors/404', [
                'title'   => '文章不存在',
                'message' => '这篇文章可能已被删除或尚未发布。',
            ], 'main');
        }

        Post::incrementViews((int)$post['id']);

        [$prev, $next] = Post::neighbours($post);

        $html = (string)($post['content_html'] ?? '');
        if ($html === '') {
            $html = (new Markdown())->parse((string)$post['content']);
        }

        return $this->render('post/show', $this->shared([
            'title'        => $post['title'],
            'post'         => $post,
            'html'         => $html,
            'toc'          => $this->buildToc($html),
            'comments'     => Comment::tree((int)$post['id']),
            'commentCount' => Comment::countFor((int)$post['id']),
            'prev'         => $prev,
            'next'         => $next,
            'related'      => Post::related($post, 4),
            'description'  => $post['excerpt'] ?: Markdown::excerpt((string)$post['content'], 160),
        ]));
    }

    /** 点赞 */
    public function like(int $id): string
    {
        \App\Core\Security::abortIfInvalid();

        $ok = Post::like($id, $this->request->ip());
        $count = (int)(\App\Core\App::instance()->db->fetchColumn(
            'SELECT `like_count` FROM `ob_posts` WHERE `id` = :id',
            ['id' => $id]
        ) ?? 0);

        if ($this->request->isAjax()) {
            return $this->json(['liked' => $ok, 'count' => $count]);
        }

        $this->flash($ok ? 'success' : 'info', $ok ? '感谢点赞' : '今天已经点过赞啦');
        return $this->back();
    }

    /** 从正文 h2/h3 生成目录 */
    private function buildToc(string $html): array
    {
        if (setting('show_toc', '1') !== '1') {
            return [];
        }

        $toc = [];
        if (preg_match_all('/<h([23])[^>]*id="([^"]+)"[^>]*>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $toc[] = [
                    'level' => (int)$m[1],
                    'id'    => $m[2],
                    'text'  => strip_tags($m[3]),
                ];
            }
        }
        return $toc;
    }
}
