<?php
/**
 * OpenBlog - RSS / Atom / Sitemap
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;

class FeedController extends Controller
{
    private function siteUrl(): string
    {
        return rtrim(url('/'), '/');
    }

    public function rss(): string
    {
        $posts = Post::published()->limit(20)->get();

        header('Content-Type: application/rss+xml; charset=utf-8');

        $siteUrl = $this->siteUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>' . $this->cdata(setting('site_name', 'OpenBlog')) . '</title>' . "\n";
        $xml .= '    <link>' . e($siteUrl) . '</link>' . "\n";
        $xml .= '    <description>' . $this->cdata(setting('site_description', '')) . '</description>' . "\n";
        $xml .= '    <language>zh-CN</language>' . "\n";
        $xml .= '    <lastBuildDate>' . date(DATE_RFC822) . '</lastBuildDate>' . "\n";
        $xml .= '    <atom:link href="' . e($siteUrl . '/feed.xml') . '" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($posts as $post) {
            $xml .= '    <item>' . "\n";
            $xml .= '      <title>' . $this->cdata((string)$post['title']) . '</title>' . "\n";
            $xml .= '      <link>' . e($siteUrl . '/post/' . $post['slug']) . '</link>' . "\n";
            $xml .= '      <guid isPermaLink="true">' . e($siteUrl . '/post/' . $post['slug']) . '</guid>' . "\n";
            $xml .= '      <pubDate>' . date(DATE_RFC822, strtotime((string)$post['published_at'])) . '</pubDate>' . "\n";
            $xml .= '      <description>' . $this->cdata((string)($post['excerpt'] ?? '')) . '</description>' . "\n";
            $xml .= '    </item>' . "\n";
        }

        $xml .= '  </channel>' . "\n</rss>";

        return $xml;
    }

    public function atom(): string
    {
        $posts = Post::published()->limit(20)->get();
        header('Content-Type: application/atom+xml; charset=utf-8');

        $siteUrl = $this->siteUrl();
        $updated = $posts === [] ? date(DATE_ATOM) : date(DATE_ATOM, strtotime((string)$posts[0]['published_at']));

        $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '  <title>' . e(setting('site_name', 'OpenBlog')) . '</title>' . "\n";
        $xml .= '  <link href="' . e($siteUrl) . '" />' . "\n";
        $xml .= '  <updated>' . $updated . '</updated>' . "\n";
        $xml .= '  <id>' . e($siteUrl . '/') . '</id>' . "\n";

        foreach ($posts as $post) {
            $xml .= '  <entry>' . "\n";
            $xml .= '    <title>' . e((string)$post['title']) . '</title>' . "\n";
            $xml .= '    <link href="' . e($siteUrl . '/post/' . $post['slug']) . '" />' . "\n";
            $xml .= '    <id>' . e($siteUrl . '/post/' . $post['slug']) . '</id>' . "\n";
            $xml .= '    <updated>' . date(DATE_ATOM, strtotime((string)$post['published_at'])) . '</updated>' . "\n";
            $xml .= '    <summary>' . e((string)($post['excerpt'] ?? '')) . '</summary>' . "\n";
            $xml .= '  </entry>' . "\n";
        }

        $xml .= '</feed>';
        return $xml;
    }

    public function sitemap(): string
    {
        $posts = Post::published()->select('slug', 'published_at', 'updated_at')->get();
        header('Content-Type: application/xml; charset=utf-8');

        $siteUrl = $this->siteUrl();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '  <url><loc>' . e($siteUrl . '/') . '</loc><priority>1.0</priority><changefreq>daily</changefreq></url>' . "\n";
        $xml .= '  <url><loc>' . e($siteUrl . '/archive') . '</loc><priority>0.6</priority></url>' . "\n";

        foreach ($posts as $post) {
            $xml .= '  <url><loc>' . e($siteUrl . '/post/' . $post['slug']) . '</loc>'
                  . '<lastmod>' . date('Y-m-d', strtotime((string)$post['updated_at'])) . '</lastmod>'
                  . '<priority>0.8</priority></url>' . "\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

    private function cdata(string $text): string
    {
        return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $text) . ']]>';
    }
}
