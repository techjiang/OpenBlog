<?php
/**
 * OpenBlog - 后台文章管理
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Paginator;
use App\Core\Session;
use App\Models\Category;
use App\Models\Post;

class PostsController extends AdminController
{
    public function index(): string
    {
        $filters = [
            'type'        => 'post',
            'status'      => (string)$this->request->query('status', ''),
            'category_id' => (string)$this->request->query('category_id', ''),
            'keyword'     => (string)$this->request->query('keyword', ''),
        ];

        $result = Post::adminList($filters, $this->page(), 15);

        return $this->render('admin/posts/index', [
            'title'        => '文章管理',
            'posts'        => $result['items'],
            'total'        => $result['total'],
            'paginator'    => new Paginator($result, url('admin/posts'), 'page'),
            'filters'      => $filters,
            'categoryList' => Category::withCount(),
        ]);
    }

    public function create(): string
    {
        return $this->render('admin/posts/form', [
            'title'        => '写文章',
            'post'         => null,
            'categoryOpts' => $this->categories(),
            'tagsValue'    => '',
        ]);
    }

    public function store(): string
    {
        $this->requireCsrf();

        $check = $this->validate([
            'title' => ['required', 'max:255'],
        ]);
        if (!$check['ok']) {
            return $this->redirect('admin/posts/create');
        }

        $data = $this->collect();
        $data['user_id'] = Auth::id();

        $id = Post::savePost(null, $data);

        Session::flash('success', '文章已保存');
        return $this->redirect('admin/posts/edit/' . $id);
    }

    public function edit(int $id): string
    {
        $post = Post::findById($id);

        if ($post === null) {
            Session::flash('error', '文章不存在');
            return $this->redirect('admin/posts');
        }

        return $this->render('admin/posts/form', [
            'title'        => '编辑：' . $post['title'],
            'post'         => $post,
            'categoryOpts' => $this->categories(),
            'tagsValue'    => implode(', ', array_column($post['tags'] ?? [], 'name')),
        ]);
    }

    public function update(int $id): string
    {
        $this->requireCsrf();

        $check = $this->validate(['title' => ['required', 'max:255']]);
        if (!$check['ok']) {
            return $this->redirect('admin/posts/edit/' . $id);
        }

        Post::savePost($id, $this->collect());

        Session::flash('success', '文章已更新');
        return $this->redirect('admin/posts/edit/' . $id);
    }

    public function destroy(int $id): string
    {
        $this->requireCsrf();

        \App\Core\App::instance()->db->delete('ob_post_tag', '`post_id` = :p', ['p' => $id]);
        Post::deleteById($id);
        Post::refreshCategoryCount();

        Session::flash('success', '文章已删除');
        return $this->redirect('admin/posts');
    }

    public function generateSlug(): string
    {
        $this->requireCsrf();
        $title = (string)$this->request->post('title', '');
        return $this->json(['slug' => slugify($title)]);
    }

    /** Markdown 实时预览 */
    public function preview(): string
    {
        $this->requireCsrf();
        $content = (string)$this->request->post('content', '');
        return $this->json(['html' => (new \App\Core\Markdown())->parse($content)]);
    }

    private function collect(): array
    {
        $rawTags = (string)$this->request->post('tags', '');
        $tags = array_values(array_filter(array_map('trim', preg_split('/[,，]/u', $rawTags) ?: [])));

        return [
            'title'         => $this->request->post('title', ''),
            'slug'          => $this->request->post('slug', '') ?: slugify((string)$this->request->post('title', '')),
            'content'       => $this->request->post('content', ''),
            'excerpt'       => $this->request->post('excerpt', ''),
            'category_id'   => $this->request->post('category_id', ''),
            'cover_image'   => $this->request->post('cover_image', ''),
            'status'        => $this->request->post('status', 'draft'),
            'type'          => 'post',
            'featured'      => $this->request->bool('featured'),
            'allow_comment' => $this->request->bool('allow_comment'),
            'published_at'  => $this->request->post('published_at', ''),
            'tags'          => $tags,
        ];
    }
}
