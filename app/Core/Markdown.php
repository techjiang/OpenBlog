<?php
/**
 * OpenBlog - Markdown 解析器（零依赖）
 *
 * 支持：ATX 标题、围栏代码块、行内代码、引用、有序/无序列表、表格、
 *       分割线、粗体、斜体、删除线、链接、图片、任务列表、脚注式段落。
 */

declare(strict_types=1);

namespace App\Core;

class Markdown
{
    /** @var array<int, string> 占位符：代码块 */
    private array $codeBlocks = [];

    public function parse(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = $this->extractCodeBlocks($text);

        $lines = explode("\n", $text);
        $html = '';
        $buffer = [];

        $flush = function () use (&$html, &$buffer): void {
            if ($buffer !== []) {
                $html .= $this->parseBlock(implode("\n", $buffer));
                $buffer = [];
            }
        };

        $total = count($lines);
        for ($i = 0; $i < $total; $i++) {
            $line = $lines[$i];

            if (preg_match('/^```PLACEHOLDER(\d+)```$/', trim($line), $m)) {
                $flush();
                $html .= $this->codeBlocks[(int)$m[1]];
                continue;
            }

            if (trim($line) === '') {
                $flush();
                continue;
            }

            // 表格：表头 + 分隔行 + 若干数据行
            if (isset($lines[$i + 1])
                && preg_match('/^\s*\|?[\s:\-|]+\|[\s:\-|]*$/', $lines[$i + 1])
                && str_contains($line, '|')) {
                $flush();

                $separator = $lines[$i + 1];
                $body = [];
                $i += 2;

                while ($i < $total && trim($lines[$i]) !== '' && str_contains($lines[$i], '|')) {
                    $body[] = $lines[$i];
                    $i++;
                }
                $i--;

                $html .= $this->parseTable($line, $separator, $body);
                continue;
            }

            $buffer[] = $line;
        }
        $flush();

        return $this->purify($html);
    }

    private function extractCodeBlocks(string $text): string
    {
        return (string)preg_replace_callback(
            '/```([\w+-]*)\n?(.*?)```/s',
            function (array $m): string {
                $lang = trim($m[1]);
                $code = htmlspecialchars($m[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $class = $lang !== '' ? ' class="language-' . e($lang) . '"' : '';
                $label = $lang !== '' ? '<div class="code-block__lang">' . e($lang) . '</div>' : '';

                $this->codeBlocks[] = '<div class="code-block">' . $label . '<pre><code' . $class . '>' . $code . '</code></pre></div>';
                return '```PLACEHOLDER' . (count($this->codeBlocks) - 1) . '```';
            },
            $text
        );
    }

    private function parseBlock(string $block): string
    {
        $lines = explode("\n", $block);

        // 标题（标题后若紧接正文，继续处理剩余行）
        if (preg_match('/^(#{1,6})\s+(.*)$/', $lines[0], $m)) {
            $level = strlen($m[1]);
            $text = $this->inline(trim($m[2]));
            $anchor = self::anchor(trim($m[2]));
            $html = "<h{$level} id=\"{$anchor}\">{$text}</h{$level}>";

            if (count($lines) > 1) {
                $html .= $this->parseBlock(implode("\n", array_slice($lines, 1)));
            }
            return $html;
        }

        // 分割线
        if (preg_match('/^\s*([-*_])\s*\1\s*\1[\s\1]*$/', trim($lines[0]))) {
            return '<hr>';
        }

        // 引用
        if (str_starts_with(trim($lines[0]), '>')) {
            $inner = implode("\n", array_map(
                static fn ($l) => preg_replace('/^\s*>\s?/', '', $l),
                $lines
            ));
            return '<blockquote>' . $this->parse($inner) . '</blockquote>';
        }

        // 列表
        if (preg_match('/^\s*([-*+]|\d+\.)\s+/', $lines[0])) {
            return $this->parseList($lines);
        }

        // 普通段落
        $paragraphs = [];
        $current = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                if ($current !== []) {
                    $paragraphs[] = implode("\n", $current);
                    $current = [];
                }
                continue;
            }
            $current[] = $line;
        }
        if ($current !== []) {
            $paragraphs[] = implode("\n", $current);
        }

        $html = '';
        foreach ($paragraphs as $p) {
            $html .= '<p>' . nl2br($this->inline($p)) . '</p>';
        }
        return $html;
    }

    private function parseList(array $lines): string
    {
        $ordered = (bool)preg_match('/^\s*\d+\.\s+/', $lines[0]);
        $html = $ordered ? '<ol>' : '<ul>';

        foreach ($lines as $line) {
            if (preg_match('/^\s*([-*+]|\d+\.)\s+(.*)$/', $line, $m)) {
                $item = $m[2];

                if (preg_match('/^\[([ xX])\]\s+(.*)$/', $item, $task)) {
                    $checked = strtolower($task[1]) === 'x' ? ' checked' : '';
                    $html .= '<li class="task-list-item"><input type="checkbox" disabled' . $checked . '> ' . $this->inline($task[2]) . '</li>';
                    continue;
                }

                $html .= '<li>' . $this->inline($item) . '</li>';
            } elseif (trim($line) !== '') {
                $html .= '<li>' . $this->inline(trim($line)) . '</li>';
            }
        }

        return $html . ($ordered ? '</ol>' : '</ul>');
    }

    private function parseTable(string $headerLine, string $separatorLine, array $bodyLines = []): string
    {
        $split = static fn (string $row): array => array_values(array_filter(
            array_map('trim', explode('|', trim($row, " \t\n\r\0\x0B|"))),
            static fn ($v) => $v !== ''
        ));

        $headers = $split($headerLine);

        $aligns = [];
        foreach ($split($separatorLine) as $cell) {
            $left = str_starts_with($cell, ':');
            $right = str_ends_with($cell, ':');
            $aligns[] = $left && $right ? 'center' : ($right ? 'right' : ($left ? 'left' : ''));
        }

        $styleFor = static fn (int $i): string =>
            ($aligns[$i] ?? '') !== '' ? ' style="text-align:' . $aligns[$i] . '"' : '';

        $html = '<div class="table-wrap"><table><thead><tr>';
        foreach ($headers as $i => $h) {
            $html .= '<th' . $styleFor($i) . '>' . $this->inline($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($bodyLines as $row) {
            $cells = $split($row);
            if ($cells === []) {
                continue;
            }
            $html .= '<tr>';
            foreach ($headers as $i => $_) {
                $html .= '<td' . $styleFor($i) . '>' . $this->inline($cells[$i] ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';
        return $html;
    }

    private function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // 行内代码
        $codes = [];
        $text = (string)preg_replace_callback('/`([^`]+)`/', static function (array $m) use (&$codes): string {
            $codes[] = '<code>' . $m[1] . '</code>';
            return "\x00C" . (count($codes) - 1) . "\x00";
        }, $text);

        // 图片
        $text = (string)preg_replace_callback(
            '/!\[([^\]]*)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/',
            static fn (array $m) => '<img src="' . $m[2] . '" alt="' . $m[1] . '" loading="lazy"' . (isset($m[3]) ? ' title="' . $m[3] . '"' : '') . '>',
            $text
        );

        // 链接
        $text = (string)preg_replace_callback(
            '/\[([^\]]+)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/',
            static function (array $m): string {
                $external = str_starts_with($m[2], 'http') && !str_contains($m[2], $_SERVER['HTTP_HOST'] ?? '');
                $attrs = $external ? ' target="_blank" rel="noopener noreferrer"' : '';
                return '<a href="' . $m[2] . '"' . $attrs . (isset($m[3]) ? ' title="' . $m[3] . '"' : '') . '>' . $m[1] . '</a>';
            },
            $text
        );

        // 自动链接
        $text = (string)preg_replace(
            '/(?<!["\'>])(https?:\/\/[^\s<]+)(?!["\'>])/',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $text
        );

        // 强调
        $text = (string)preg_replace('/\*\*\*(.+?)\*\*\*/s', '<strong><em>$1</em></strong>', $text);
        $text = (string)preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = (string)preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $text);
        $text = (string)preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $text);
        $text = (string)preg_replace('/==(.+?)==/s', '<mark>$1</mark>', $text);

        // 还原行内代码
        $text = (string)preg_replace_callback('/\x00C(\d+)\x00/', static fn (array $m) => $codes[(int)$m[1]], $text);

        return $text;
    }

    /** 生成标题锚点 */
    public static function anchor(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $slug) ?? '';
        $slug = preg_replace('/\s+/u', '-', trim($slug)) ?? '';
        return $slug !== '' ? $slug : 'section';
    }

    /** 极简白名单净化，移除脚本类标签 */
    private function purify(string $html): string
    {
        $html = (string)preg_replace('#<\s*(script|style|iframe|object|embed|link|meta|form)[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
        $html = (string)preg_replace_callback('#<(\w+)([^>]*)>#i', static function (array $m): string {
            $tag = strtolower($m[1]);
            if (in_array($tag, ['script', 'iframe', 'object', 'embed', 'style'], true)) {
                return '';
            }
            $attrs = (string)preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $m[2]);
            $attrs = (string)preg_replace('/\s(?:href|src)\s*=\s*(["\']?)\s*javascript:/i', ' $1', $attrs);
            return '<' . $tag . $attrs . '>';
        }, $html);

        return trim($html);
    }

    /** 生成纯文本摘要 */
    public static function excerpt(string $markdown, int $limit = 160): string
    {
        $text = (string)preg_replace('/```.*?```/s', '', $markdown);
        $text = (string)preg_replace('/[#>*`_\-\[\]()!]/', ' ', $text);
        $text = trim((string)preg_replace('/\s+/', ' ', $text));
        return mb_substr($text, 0, $limit);
    }

    /** 估算阅读时长（分钟） */
    public static function readingTime(string $markdown, int $wordsPerMinute = 300): int
    {
        $text = (string)preg_replace('/```.*?```/s', '', $markdown);
        $chars = mb_strlen(trim($text));
        return max(1, (int)ceil($chars / $wordsPerMinute));
    }
}
