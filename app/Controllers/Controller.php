<?php
/**
 * OpenBlog - 控制器基类
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Request;
use App\Core\Security;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;

abstract class Controller
{
    protected Request $request;
    protected View $view;

    public function __construct()
    {
        $this->request = App::instance()->request;
        $this->view = new View();
    }

    protected function render(string $name, array $data = [], ?string $layout = 'main'): string
    {
        return $this->view->render($name, $data, $layout);
    }

    protected function json(array $data, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        return (string)json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function redirect(string $to): string
    {
        $to = str_starts_with($to, 'http') ? $to : url($to);
        header('Location: ' . $to, true, 302);
        return '';
    }

    protected function back(string $fallback = ''): string
    {
        $referer = $this->request->referer();
        return $this->redirect($referer !== '' ? $referer : $fallback);
    }

    /** 校验 POST 数据，失败时回填并返回错误 */
    protected function validate(array $rules): array
    {
        Security::abortIfInvalid();

        $validator = new Validator($_POST, $rules);

        if ($validator->fails()) {
            Session::flashInput($_POST);
            Session::flash('errors', $validator->errors());
            return ['ok' => false, 'errors' => $validator->errors()];
        }

        return ['ok' => true, 'errors' => []];
    }

    protected function page(): int
    {
        return max(1, $this->request->int('page', 1));
    }

    protected function perPage(): int
    {
        return max(1, (int)setting('posts_per_page', '10'));
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    protected function shared(array $data): array
    {
        return array_merge([
            'siteName'    => setting('site_name', 'OpenBlog'),
            'siteTagline' => setting('site_tagline', ''),
            'categories'  => \App\Models\Category::withCount(),
            'tags'        => \App\Models\Tag::cloud(30),
            'popular'     => \App\Models\Post::popular(5),
            'latest'      => \App\Models\Post::latest(5),
        ], $data);
    }
}
