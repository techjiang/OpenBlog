<?php
/**
 * OpenBlog - 路由定义
 */

declare(strict_types=1);

use App\Core\Router;
use App\Middleware\AdminMiddleware;

/**
 * @var Router $router  由 App::registerRoutes() 注入
 */

/* ------------------------------------------------------------------ *
 * 前台
 * ------------------------------------------------------------------ */
$router->get('/', 'HomeController@index');
$router->get('/page/{page:[0-9]+}', 'HomeController@index');
$router->get('/post/{slug}', 'PostController@show');
$router->get('/posts/{slug}', 'PostController@show');
$router->get('/archive', 'ArchiveController@index');
$router->get('/archive/{year:[0-9]{4}}', 'ArchiveController@year');
$router->get('/archive/{year:[0-9]{4}}/{month:[0-9]{2}}', 'ArchiveController@month');
$router->get('/category/{slug}', 'CategoryController@show');
$router->get('/tag/{slug}', 'TagController@show');
$router->get('/author/{username}', 'AuthorController@show');
$router->get('/search', 'SearchController@index');
$router->get('/page/{slug}', 'PageController@show');
$router->get('/topics', 'TopicsController@index');

$router->post('/posts/{id:[0-9]+}/like', 'PostController@like');
$router->post('/comments/{id:[0-9]+}', 'CommentController@store');
$router->post('/newsletter', 'HomeController@subscribe');

/* Feed 与站点地图 */
$router->get('/feed', 'FeedController@rss');
$router->get('/feed.xml', 'FeedController@rss');
$router->get('/rss.xml', 'FeedController@rss');
$router->get('/atom.xml', 'FeedController@atom');
$router->get('/sitemap.xml', 'FeedController@sitemap');

/* 轻量 JSON API */
$router->get('/api/search', 'ApiController@search');
$router->get('/api/posts', 'ApiController@posts');

/* ------------------------------------------------------------------ *
 * 后台
 * ------------------------------------------------------------------ */
$router->get('/admin/login', 'Admin\AuthController@showLogin');
$router->post('/admin/login', 'Admin\AuthController@login');
$router->get('/admin/logout', 'Admin\AuthController@logout');

$router->group('/admin', function (Router $r): void {
    $r->get('', 'DashboardController@index');
    $r->get('/', 'DashboardController@index');

    /* 文章 */
    $r->get('/posts', 'PostsController@index');
    $r->get('/posts/create', 'PostsController@create');
    $r->post('/posts', 'PostsController@store');
    $r->get('/posts/edit/{id:[0-9]+}', 'PostsController@edit');
    $r->post('/posts/update/{id:[0-9]+}', 'PostsController@update');
    $r->post('/posts/delete/{id:[0-9]+}', 'PostsController@destroy');
    $r->post('/posts/slug', 'PostsController@generateSlug');
    $r->post('/preview', 'PostsController@preview');

    /* 页面 */
    $r->get('/pages', 'PagesController@index');
    $r->get('/pages/create', 'PagesController@create');
    $r->post('/pages', 'PagesController@store');
    $r->get('/pages/edit/{id:[0-9]+}', 'PagesController@edit');
    $r->post('/pages/update/{id:[0-9]+}', 'PagesController@update');
    $r->post('/pages/delete/{id:[0-9]+}', 'PagesController@destroy');

    /* 分类 */
    $r->get('/categories', 'CategoriesController@index');
    $r->post('/categories', 'CategoriesController@store');
    $r->post('/categories/update/{id:[0-9]+}', 'CategoriesController@update');
    $r->post('/categories/delete/{id:[0-9]+}', 'CategoriesController@destroy');

    /* 标签 */
    $r->get('/tags', 'TagsController@index');
    $r->post('/tags', 'TagsController@store');
    $r->post('/tags/update/{id:[0-9]+}', 'TagsController@update');
    $r->post('/tags/delete/{id:[0-9]+}', 'TagsController@destroy');

    /* 评论 */
    $r->get('/comments', 'CommentsController@index');
    $r->post('/comments/status/{id:[0-9]+}', 'CommentsController@status');
    $r->post('/comments/reply/{id:[0-9]+}', 'CommentsController@reply');
    $r->post('/comments/delete/{id:[0-9]+}', 'CommentsController@destroy');

    /* 媒体库 */
    $r->get('/media', 'MediaController@index');
    $r->post('/media/upload', 'MediaController@upload');
    $r->post('/media/delete/{id:[0-9]+}', 'MediaController@destroy');

    /* 用户 */
    $r->get('/users', 'UsersController@index');
    $r->post('/users', 'UsersController@store');
    $r->get('/users/edit/{id:[0-9]+}', 'UsersController@edit');
    $r->post('/users/update/{id:[0-9]+}', 'UsersController@update');
    $r->post('/users/delete/{id:[0-9]+}', 'UsersController@destroy');
    $r->get('/profile', 'UsersController@profile');
    $r->post('/profile', 'UsersController@updateProfile');

    /* 设置 */
    $r->get('/settings', 'SettingsController@index');
    $r->post('/settings', 'SettingsController@update');

    /* 工具 */
    $r->post('/cache/clear', 'DashboardController@clearCache');
}, 'Admin', [AdminMiddleware::class]);
