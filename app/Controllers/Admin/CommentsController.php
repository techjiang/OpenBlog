<?php
/**
 * OpenBlog - 后台评论管理
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Paginator;
use App\Core\Session;
use App\Models\Comment;

class CommentsController extends AdminController
{
    public function index(): string
    {
        $filters = [
            'status'  => (string)$this->request->query('status', ''),
            'keyword' => (string)$this->request->query('keyword', ''),
        ];

        $result = Comment::adminList($filters, $this->page(), 20);

        return $this->render('admin/comments/index', [
            'title'     => '评论管理',
            'comments'  => $result['items'],
            'total'     => $result['total'],
            'paginator' => new Paginator($result, url('admin/comments'), 'page'),
            'filters'   => $filters,
        ]);
    }

    public function status(int $id): string
    {
        $this->requireCsrf();

        $newStatus = (string)$this->request->post('status', 'approved');
        if (!in_array($newStatus, ['pending', 'approved', 'spam', 'trash'], true)) {
            $newStatus = 'approved';
        }

        Comment::updateById($id, ['status' => $newStatus]);

        $comment = Comment::find($id);
        if ($comment !== null) {
            $postId = (int)$comment->post_id;
            $count = Comment::countFor($postId);
            App::instance()->db->query('UPDATE `ob_posts` SET `comment_count` = :c WHERE `id` = :p', ['c' => $count, 'p' => $postId]);
        }

        Session::flash('success', '评论状态已更新');
        return $this->back('admin/comments');
    }

    public function reply(int $id): string
    {
        $this->requireCsrf();

        $parent = Comment::find($id);
        if ($parent === null) {
            Session::flash('error', '评论不存在');
            return $this->back('admin/comments');
        }

        $content = trim((string)$this->request->post('content', ''));
        if ($content === '') {
            Session::flash('error', '回复内容不能为空');
            return $this->back('admin/comments');
        }

        $user = Auth::user();
        Comment::create([
            'post_id'     => $parent->post_id,
            'parent_id'   => $id,
            'user_id'     => (int)$user['id'],
            'author_name' => $user['display_name'] ?: $user['username'],
            'author_email'=> $user['email'],
            'content'     => $content,
            'status'      => 'approved',
            'ip'          => $this->request->ip(),
        ]);

        $postId = (int)$parent->post_id;
        App::instance()->db->query(
            'UPDATE `ob_posts` SET `comment_count` = :c WHERE `id` = :p',
            ['c' => Comment::countFor($postId), 'p' => $postId]
        );

        Session::flash('success', '回复已发布');
        return $this->back('admin/comments');
    }

    public function destroy(int $id): string
    {
        $this->requireCsrf();

        $comment = Comment::find($id);
        $postId = $comment !== null ? (int)$comment->post_id : 0;

        Comment::deleteById($id);

        if ($postId > 0) {
            App::instance()->db->query(
                'UPDATE `ob_posts` SET `comment_count` = :c WHERE `id` = :p',
                ['c' => Comment::countFor($postId), 'p' => $postId]
            );
        }

        Session::flash('success', '评论已删除');
        return $this->back('admin/comments');
    }
}
