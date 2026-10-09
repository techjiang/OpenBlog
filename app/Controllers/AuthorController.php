<?php
/**
 * OpenBlog - 作者页
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Paginator;
use App\Models\Post;
use App\Models\User;

class AuthorController extends Controller
{
    public function show(string $username): string
    {
        $user = User::findByUsername($username);

        if ($user === null) {
            http_response_code(404);
            return $this->view->render('errors/404', ['title' => '作者不存在', 'message' => '该作者不存在。'], 'main');
        }

        $page = $this->page();
        $result = Post::paginateByAuthor((int)$user['id'], $page, $this->perPage());

        return $this->render('archive/listing', $this->shared([
            'title'     => ($user['display_name'] ?: $user['username']) . ' - 作者',
            'heading'   => $user['display_name'] ?: $user['username'],
            'subtitle'  => $user['bio'] ?: ('共 ' . $result['total'] . ' 篇文章'),
            'posts'     => $result['items'],
            'paginator' => new Paginator($result, url('author/' . $username)),
        ]));
    }
}
