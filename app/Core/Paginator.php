<?php
/**
 * OpenBlog - 分页渲染助手
 */

declare(strict_types=1);

namespace App\Core;

class Paginator
{
    public int $total;
    public int $perPage;
    public int $currentPage;
    public int $lastPage;

    /** @var array<int, mixed> */
    public array $items;

    private string $baseUrl;
    private string $pageParam;

    public function __construct(array $result, string $baseUrl, string $pageParam = 'page')
    {
        $this->items       = $result['items'] ?? [];
        $this->total       = (int)($result['total'] ?? 0);
        $this->perPage     = (int)($result['per_page'] ?? 10);
        $this->currentPage = (int)($result['current'] ?? 1);
        $this->lastPage    = (int)($result['last_page'] ?? 1);
        $this->baseUrl     = $baseUrl;
        $this->pageParam   = $pageParam;
    }

    private function url(int $page): string
    {
        if ($page <= 1) {
            return $this->baseUrl;
        }
        $separator = str_contains($this->baseUrl, '?') ? '&' : '?';
        return $this->baseUrl . $separator . $this->pageParam . '=' . $page;
    }

    public function onFirstPage(): bool
    {
        return $this->currentPage <= 1;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    /** @return array<int, array{0:int|string,1:string|null}> */
    public function links(): array
    {
        if (!$this->hasPages()) {
            return [];
        }

        $window = 2;
        $pages = [];

        $pages[] = [($this->currentPage > 1 ? $this->currentPage - 1 : 1), $this->url(max(1, $this->currentPage - 1))];

        for ($i = 1; $i <= $this->lastPage; $i++) {
            if ($i === 1 || $i === $this->lastPage || abs($i - $this->currentPage) <= $window) {
                $pages[] = [$i, $this->url($i)];
            } elseif (($pages[count($pages) - 1][0] ?? '') !== '...') {
                $pages[] = ['...', null];
            }
        }

        $pages[] = [$this->currentPage + 1 <= $this->lastPage ? $this->currentPage + 1 : $this->lastPage,
                    $this->url(min($this->lastPage, $this->currentPage + 1))];

        return $pages;
    }

    public function render(): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav class="pagination" aria-label="分页导航">';

        $prev = $this->currentPage > 1 ? $this->currentPage - 1 : 1;
        $html .= '<a class="page-link' . ($this->onFirstPage() ? ' is-disabled' : '') . '" href="' . e($this->url($prev)) . '" aria-label="上一页">‹</a>';

        foreach ($this->links() as [$label, $url]) {
            if ($url === null) {
                $html .= '<span class="page-dots">…</span>';
                continue;
            }
            $active = ($label === $this->currentPage) ? ' is-active' : '';
            $html .= '<a class="page-link' . $active . '" href="' . e($url) . '">' . e((string)$label) . '</a>';
        }

        $next = min($this->lastPage, $this->currentPage + 1);
        $html .= '<a class="page-link' . ($this->currentPage >= $this->lastPage ? ' is-disabled' : '') . '" href="' . e($this->url($next)) . '" aria-label="下一页">›</a>';

        $html .= '</nav>';
        return $html;
    }
}
