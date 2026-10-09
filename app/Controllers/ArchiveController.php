<?php
/**
 * OpenBlog - 归档页
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Paginator;
use App\Models\Post;

class ArchiveController extends Controller
{
    public function index(): string
    {
        $archives = Post::archives();

        $grouped = [];
        foreach ($archives as $row) {
            $grouped[$row['year']][] = $row;
        }

        $result = Post::paginatePublished($this->page(), 20);

        return $this->render('archive/index', $this->shared([
            'title'     => '文章归档',
            'grouped'   => $grouped,
            'total'     => array_sum(array_column($archives, 'total')),
            'posts'     => $result['items'],
            'paginator' => new Paginator($result, url('archive')),
        ]));
    }

    public function year(string $year): string
    {
        return $this->listing($year, null);
    }

    public function month(string $year, string $month): string
    {
        return $this->listing($year, $month);
    }

    private function listing(string $year, ?string $month): string
    {
        $yearMonth = $month === null ? $year : $year . '-' . $month;
        $result = Post::paginateByMonth($yearMonth, $this->page(), $this->perPage());

        $heading = $month === null ? $year . ' 年' : $year . ' 年 ' . (int)$month . ' 月';

        return $this->render('archive/listing', $this->shared([
            'title'     => $heading . ' 归档',
            'heading'   => $heading,
            'subtitle'  => '共 ' . $result['total'] . ' 篇文章',
            'posts'     => $result['items'],
            'paginator' => new Paginator($result, url('archive/' . $year . ($month ? '/' . $month : ''))),
        ]));
    }
}
