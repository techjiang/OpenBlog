<?php
/**
 * OpenBlog - 评论提交
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Security;
use App\Core\Settings;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(int $id): string
    {
        Security::abortIfInvalid();

        if (Settings::get('comment_enabled', '1') !== '1') {
            $this->flash('error', '本站已关闭评论功能');
            return $this->back();
        }

        if (!Security::throttle('comment:' . $this->request->ip(), 3, 60)) {
            $this->flash('error', '评论过于频繁，请稍后再试');
            return $this->back();
        }

        $user = Auth::user();
        $input = [
            'content'        => $this->request->post('content', ''),
            'parent_id'      => $this->request->post('parent_id', ''),
            'author_name'    => $user['display_name'] ?? $user['username'] ?? $this->request->post('author_name', ''),
            'author_email'   => $user['email'] ?? $this->request->post('author_email', ''),
            'author_website' => $this->request->post('author_website', ''),
        ];

        [$ok, $message] = Comment::submit($id, $input, $user['id'] ?? null);

        if ($ok) {
            \App\Core\Session::clearOld();
            // 评论后回到文章锚点
            $referer = $this->request->referer();
            return $this->redirect(str_contains($referer, '#') ? $referer : $referer . '#comments');
        }

        \App\Core\Session::flashInput($_POST);
        $this->flash('error', $message);
        return $this->back();
    }
}
