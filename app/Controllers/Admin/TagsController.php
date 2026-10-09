<?php
/**
 * OpenBlog - 后台标签管理
 */

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Session;
use App\Models\Tag;

class TagsController extends AdminController
{
    public function index(): string
    {
        return $this->render('admin/tags/index', [
            'title' => '标签管理',
            'tags'  => Tag::allWithCount(),
        ]);
    }

    public function store(): string
    {
        $this->requireCsrf();

        $check = $this->validate(['name' => ['required', 'max:50']]);
        if (!$check['ok']) {
            return $this->redirect('admin/tags');
        }

        $name = trim((string)$this->request->post('name', ''));
        Tag::create([
            'name' => $name,
            'slug' => trim((string)$this->request->post('slug', '')) ?: slugify($name),
        ]);

        Session::flash('success', '标签已创建');
        return $this->redirect('admin/tags');
    }

    public function update(int $id): string
    {
        $this->requireCsrf();

        $check = $this->validate(['name' => ['required', 'max:50']]);
        if (!$check['ok']) {
            return $this->redirect('admin/tags');
        }

        $name = trim((string)$this->request->post('name', ''));
        Tag::updateById($id, [
            'name' => $name,
            'slug' => trim((string)$this->request->post('slug', '')) ?: slugify($name),
        ]);

        Session::flash('success', '标签已更新');
        return $this->redirect('admin/tags');
    }

    public function destroy(int $id): string
    {
        $this->requireCsrf();

        $db = App::instance()->db;
        $db->delete('ob_post_tag', '`tag_id` = :t', ['t' => $id]);
        Tag::deleteById($id);

        Session::flash('success', '标签已删除');
        return $this->redirect('admin/tags');
    }
}
