<?php
/**
 * OpenBlog - 分类与标签总览
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use App\Models\Tag;

class TopicsController extends Controller
{
    public function index(): string
    {
        return $this->render('topics/index', $this->shared([
            'title'         => '全部分类与标签',
            'categoryList'  => Category::withCount(),
            'tagList'       => Tag::allWithCount(),
        ]));
    }
}
