<?php
/**
 * OpenBlog - 本地 SQLite 演示数据生成器
 *
 * 用法：php tools/seed_sqlite.php
 * 会优先读取 app/config.dev.php（SQLite 演示配置），
 * 若不存在则读取 app/config.php，请确保 db.driver 为 'sqlite'。
 */

declare(strict_types=1);

$configFile = is_file(__DIR__ . '/../app/config.dev.php')
    ? __DIR__ . '/../app/config.dev.php'
    : __DIR__ . '/../app/config.php';
$config = require $configFile;
$dbCfg  = $config['db'] ?? [];
if (($dbCfg['driver'] ?? 'mysql') !== 'sqlite') {
    fwrite(STDERR, "请在 app/config.php 中将 db.driver 设为 'sqlite' 后运行。\n");
    exit(1);
}
$path = $dbCfg['path'] ?? $dbCfg['database'] ?? __DIR__ . '/../storage/openblog.sqlite';

if (!is_dir(dirname($path))) {
    mkdir(dirname($path), 0755, true);
}
@unlink($path);

$pdo = new PDO('sqlite:' . $path);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = OFF;');

$schema = <<<'SQL'
CREATE TABLE `ob_users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name` VARCHAR(80) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `role` TEXT NOT NULL DEFAULT 'author',
  `status` INTEGER NOT NULL DEFAULT 1,
  `remember_token` VARCHAR(64) DEFAULT NULL,
  `last_login_at` TEXT DEFAULT NULL,
  `created_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX `uk_users_username` ON `ob_users`(`username`);
CREATE UNIQUE INDEX `uk_users_email` ON `ob_users`(`email`);

CREATE TABLE `ob_categories` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `color` VARCHAR(20) DEFAULT NULL,
  `parent_id` INTEGER DEFAULT NULL,
  `sort_order` INTEGER NOT NULL DEFAULT 0,
  `post_count` INTEGER NOT NULL DEFAULT 0,
  `created_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX `uk_categories_slug` ON `ob_categories`(`slug`);

CREATE TABLE `ob_tags` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  `post_count` INTEGER NOT NULL DEFAULT 0,
  `created_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX `uk_tags_slug` ON `ob_tags`(`slug`);

CREATE TABLE `ob_posts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `category_id` INTEGER DEFAULT NULL,
  `type` TEXT NOT NULL DEFAULT 'post',
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `content` TEXT NOT NULL,
  `content_html` TEXT DEFAULT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `status` TEXT NOT NULL DEFAULT 'draft',
  `featured` INTEGER NOT NULL DEFAULT 0,
  `allow_comment` INTEGER NOT NULL DEFAULT 1,
  `password` VARCHAR(64) DEFAULT NULL,
  `view_count` INTEGER NOT NULL DEFAULT 0,
  `like_count` INTEGER NOT NULL DEFAULT 0,
  `comment_count` INTEGER NOT NULL DEFAULT 0,
  `reading_time` INTEGER NOT NULL DEFAULT 1,
  `published_at` TEXT DEFAULT NULL,
  `created_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX `uk_posts_slug` ON `ob_posts`(`slug`);
CREATE INDEX `idx_posts_status_published` ON `ob_posts`(`status`, `published_at`);
CREATE INDEX `idx_posts_category` ON `ob_posts`(`category_id`);
CREATE INDEX `idx_posts_type` ON `ob_posts`(`type`);

CREATE TABLE `ob_post_tag` (
  `post_id` INTEGER NOT NULL,
  `tag_id` INTEGER NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`)
);

CREATE TABLE `ob_comments` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `post_id` INTEGER NOT NULL,
  `parent_id` INTEGER DEFAULT NULL,
  `user_id` INTEGER DEFAULT NULL,
  `author_name` VARCHAR(80) NOT NULL,
  `author_email` VARCHAR(120) DEFAULT NULL,
  `author_website` VARCHAR(255) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `status` TEXT NOT NULL DEFAULT 'pending',
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX `idx_comments_post` ON `ob_comments`(`post_id`);
CREATE INDEX `idx_comments_status` ON `ob_comments`(`status`);

CREATE TABLE `ob_media` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER DEFAULT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `mime_type` VARCHAR(80) DEFAULT NULL,
  `size` INTEGER NOT NULL DEFAULT 0,
  `width` INTEGER DEFAULT NULL,
  `height` INTEGER DEFAULT NULL,
  `created_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `ob_settings` (
  `key` VARCHAR(80) NOT NULL,
  `value` TEXT DEFAULT NULL,
  `group` VARCHAR(40) NOT NULL DEFAULT 'general',
  PRIMARY KEY (`key`)
);

CREATE TABLE `ob_subscribers` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `email` VARCHAR(120) NOT NULL,
  `status` INTEGER NOT NULL DEFAULT 1,
  `created_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX `uk_subscribers_email` ON `ob_subscribers`(`email`);

CREATE TABLE `ob_post_likes` (
  `post_id` INTEGER NOT NULL,
  `ip_hash` CHAR(64) NOT NULL,
  `created_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`, `ip_hash`)
);
SQL;

foreach (explode(";\n", $schema) as $stmt) {
    if (trim($stmt) !== '') {
        $pdo->exec($stmt);
    }
}

// 设置
$settings = [
    'site_name' => 'OpenBlog',
    'site_tagline' => '记录、分享与沉淀',
    'site_description' => 'OpenBlog 是一个零框架依赖的纯 PHP 博客系统。',
    'site_keywords' => 'OpenBlog,PHP,博客,Blog',
    'site_logo' => '',
    'site_icp' => '',
    'admin_email' => 'admin@example.com',
    'posts_per_page' => '10',
    'comment_enabled' => '1',
    'comment_review' => '1',
    'upload_max_size' => '5',
    'theme' => 'default',
    'accent_color' => '#8b7bff',
    'home_layout' => 'list',
    'show_toc' => '1',
    'footer_text' => 'Powered by OpenBlog',
    'social_github' => 'https://github.com/techjiang/OpenBlog',
    'social_twitter' => '',
    'social_email' => '',
    'stat_code' => '',
];
$ins = $pdo->prepare('INSERT INTO `ob_settings` (`key`, `value`, `group`) VALUES (?, ?, ?)');
foreach ($settings as $k => $v) {
    $ins->execute([$k, $v, 'general']);
}

// 分类
$catStmt = $pdo->prepare('INSERT INTO `ob_categories` (`name`,`slug`,`description`,`color`,`sort_order`,`post_count`) VALUES (?,?,?,?,?,?)');
$cats = [
    ['技术笔记', 'tech', '记录开发过程中的思考与沉淀', '#8b7bff', 1, 6],
    ['生活随笔', 'life', '关于生活、旅行与日常', '#34d399', 2, 1],
    ['开源项目', 'open-source', '开源项目进展与发布记录', '#fbbf24', 3, 1],
];
$catIds = [];
foreach ($cats as $c) {
    $catStmt->execute($c);
    $catIds[] = $pdo->lastInsertId();
}

// 标签
$tagStmt = $pdo->prepare('INSERT INTO `ob_tags` (`name`,`slug`,`post_count`) VALUES (?,?,?)');
$tags = [['PHP', 'php', 4], ['CSS', 'css', 2], ['设计', 'design', 2], ['架构', 'arch', 2], ['最佳实践', 'best', 3]];
$tagIds = [];
foreach ($tags as $t) {
    $tagStmt->execute($t);
    $tagIds[] = $pdo->lastInsertId();
}

// 用户
$pdo->prepare('INSERT INTO `ob_users` (`username`,`email`,`password_hash`,`display_name`,`role`,`bio`) VALUES (?,?,?,?,?,?)')
    ->execute(['admin', 'admin@example.com', password_hash('admin123', PASSWORD_DEFAULT), '管理员', 'admin', '热爱写作的开发者']);
$pdo->prepare('INSERT INTO `ob_users` (`username`,`email`,`password_hash`,`display_name`,`role`) VALUES (?,?,?,?,?)')
    ->execute(['shen', 'shen@example.com', password_hash('admin123', PASSWORD_DEFAULT), '沈知白', 'author']);
$pdo->prepare('INSERT INTO `ob_users` (`username`,`email`,`password_hash`,`display_name`,`role`) VALUES (?,?,?,?,?)')
    ->execute(['lin', 'lin@example.com', password_hash('admin123', PASSWORD_DEFAULT), '林清欢', 'author']);
$adminId = 1;

// 文章
$posts = [
    ['PHP 8 下的属性提升与枚举实战', 'php8-promotion-enum', 0, ['PHP', '最佳实践'], '从构造器属性提升到枚举类型，盘点 PHP 8 带来的语法糖如何减少样板代码。', 1, 1203, 42, 6, <<<'MD'
# 一、构造器属性提升

过去我们需要先声明属性，再在构造器里赋值。现在可以把可见性直接写在参数前：

```php
class Post {
    public function __construct(
        public string $title,
        public string $status = 'draft',
    ) {}
}
```

# 二、枚举让状态更安全

用枚举替代字符串常量，既能在类型系统层约束取值，也能附加方法。

# 三、两者结合

把枚举用作属性类型，构造器一行即可完成声明与校验，让业务对象既简洁又稳健。
MD
    ],
    ['深色模式：用 CSS 变量优雅切换主题', 'dark-mode-css-vars', 0, ['CSS', '设计'], '一套设计令牌（design token）即可让整站主题随手切换，无需重写样式。', 0, 1542, 58, 6, <<<'MD'
# 为什么用 CSS 变量

把颜色集中为 `--accent`、`--bg`、`--text` 等令牌，切换主题只需改写 `:root` 上的变量。

```css
:root { --accent: #8b7bff; }
[data-theme="dark"] { --bg: #0f0f17; }
```

# 用户偏好

读取 `prefers-color-scheme` 与 localStorage，即可持久化用户选择。
MD
    ],
    ['大圆角卡片与弥散阴影的克制之美', 'soft-card-design', 0, ['设计', 'CSS'], '当柔和的边框与圆角相遇，界面才能在信息密度与呼吸感之间找到平衡。', 0, 980, 31, 4, <<<'MD'
# 圆角不是越大越好

12 到 16px 的圆角适合卡片，按钮可用 pill 全圆角。

# 阴影要轻

弥散阴影的秘诀是低透明度、大模糊半径、轻微下移。
MD
    ],
    ['从零写一个轻量 MVC 路由', 'tiny-mvc-router', 0, ['PHP', '架构'], '不依赖任何框架，用不到 200 行 PHP 实现一个可扩展的路由分发器。', 0, 800, 27, 8, <<<'MD'
# 路由的本质

把请求路径映射到控制器方法。

```php
$routes['/post/(?P<slug>[\w-]+)'] = [PostController::class, 'show'];
```

# 分发

正则匹配路径，提取参数，反射调用目标方法。
MD
    ],
    ['PDO 预处理：别再拼接 SQL 了', 'pdo-prepared-statements', 0, ['PHP', '最佳实践'], '一次注入漏洞带来的教训，以及参数化查询为何是底线。', 0, 720, 19, 7, <<<'MD'
# 拼接的代价

`"SELECT * FROM posts WHERE id = $id"` 一旦 `$id` 来自用户输入便可被注入。

# 预处理

```php
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
```
MD
    ],
    ['排版系统：字号阶梯与行高的取舍', 'typography-scale', 1, ['设计'], '好的阅读体验，一半来自内容，一半来自看不见的排版节奏。', 0, 640, 22, 5, <<<'MD'
# 比例阶梯

用 1.2 的乘数生成 12 / 14 / 17 / 20 / 24 的字号阶梯。

# 行高

正文行高 1.7 左右最易读，标题可收紧到 1.2。
MD
    ],
    ['用 Figma 设计一套现代博客 UI', 'figma-blog-ui', 0, ['设计'], '我们把一整套博客系统拆解为可复用的卡片、导航与表单组件，并以浅紫主色构建统一视觉。', 1, 510, 14, 9, <<<'MD'
# 组件化思维

Hero、Card、Nav、Panel 都是独立组件，组合出页面。

# 主色

选定浅紫 `#8b7bff` 作为强调，其余用中性灰阶。
MD
    ],
    ['我的开源第一年里学到了什么', 'open-source-one-year', 2, ['最佳实践'], '从发布第一个仓库到收获 star，复盘这一年的成长与踩坑。', 0, 430, 11, 7, <<<'MD'
# 起点

把内部工具开源，意外收到第一个 PR。

# 维护

回应 issue、写文档、定贡献规范，比写代码更费时也更值得。
MD
    ],
];

$postStmt = $pdo->prepare('INSERT INTO `ob_posts` (`user_id`,`category_id`,`type`,`title`,`slug`,`excerpt`,`content`,`content_html`,`status`,`featured`,`view_count`,`like_count`,`reading_time`,`published_at`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
$linkStmt = $pdo->prepare('INSERT INTO `ob_post_tag` (`post_id`,`tag_id`) VALUES (?,?)');

foreach ($posts as $i => $p) {
    [$title, $slug, $catIdx, $tagNames, $excerpt, $featured, $views, $likes, $read, $md] = $p;
    $html = markdownToHtml($md);
    $pub = date('Y-m-d H:i:s', time() - ($i + 1) * 86400 - 3600);
    $postStmt->execute([$adminId, $catIds[$catIdx], 'post', $title, $slug, $excerpt, $md, $html, 'published', $featured, $views, $likes, $read, $pub]);
    $pid = $pdo->lastInsertId();
    foreach ($tagNames as $tn) {
        $idx = array_search($tn, array_column($tags, 0));
        if ($idx !== false) {
            $linkStmt->execute([$pid, $tagIds[$idx]]);
        }
    }
}

// 评论
$cmtStmt = $pdo->prepare('INSERT INTO `ob_comments` (`post_id`,`author_name`,`author_email`,`content`,`status`) VALUES (?,?,?,?,?)');
$cmtStmt->execute([1, '林清欢', 'lin@example.com', '枚举那段太实用了，正好在重构旧项目。', 'approved']);
$cmtStmt->execute([1, '周屿', 'zhou@example.com', '属性提升之后代码确实清爽很多。', 'approved']);
$cmtStmt->execute([2, '匿名', null, '排版系统那篇受教了，收藏了。', 'approved']);

// 订阅者
$pdo->prepare('INSERT INTO `ob_subscribers` (`email`) VALUES (?)')->execute(['reader@example.com']);

echo "SQLite 演示数据已生成：{$path}\n";
echo "后台账号：admin / admin123\n";

function markdownToHtml(string $md): string
{
    $html = htmlspecialchars($md, ENT_QUOTES, 'UTF-8');
    $html = preg_replace_callback('/```(\w*)\n(.*?)```/s', static fn ($m) => '<pre><code>' . $m[2] . '</code></pre>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/\n\n+/', "</p>\n<p>", $html);
    return '<p>' . trim($html) . '</p>';
}
