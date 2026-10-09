# OpenBlog

> 零框架依赖的纯 PHP 博客系统。单入口、MVC 分层、自带 Markdown 解析、完整后台。

OpenBlog 不依赖 Composer、不依赖任何第三方库，只要有一台支持 **PHP 8.0+** 与 **MySQL 5.7+ / MariaDB** 的虚拟主机或服务器即可运行。

---

## 特性

### 内容创作
- Markdown 写作（自研解析器，零依赖）：标题、代码块、表格、任务列表、引用、脚注式段落
- 实时预览、编辑器快捷键（Tab 缩进、`Ctrl/Cmd + S` 保存）
- 文章 / 独立页面两种内容类型
- 分类（支持层级）、标签、封面图、精选、摘要自动提取
- 阅读时长估算、浏览量、点赞统计

### 前台
- 响应式杂志风布局，深色 / 浅色主题自动切换
- 首页精选大卡 + 文章列表、侧边栏（分类、标签云、热门、归档、订阅）
- 文章页：目录导航、代码一键复制、图片灯箱、上下篇、相关阅读
- 分类 / 标签 / 作者 / 年月归档 / 全文搜索
- 嵌套评论（审核、垃圾标记、后台回复）
- RSS 2.0、Atom、XML Sitemap
- 轻量 JSON API：`/api/posts`、`/api/search`

### 后台
- 仪表盘：统计卡、发布趋势图、热门文章、最新评论
- 文章 / 页面 CRUD、筛选、批量状态管理
- 分类、标签、评论审核、媒体库、用户与角色、站点设置
- 角色权限：管理员 / 编辑 / 作者

### 安全
- PDO 预处理，杜绝 SQL 注入
- CSRF Token 全站校验
- 密码 `password_hash` (bcrypt) 存储
- 登录限流、评论限流、上传类型与大小校验
- Markdown 输出白名单净化，移除脚本类标签
- `storage/`、`app/` 禁止直接访问

---

## 环境要求

| 项目 | 要求 |
| --- | --- |
| PHP | ≥ 8.0（推荐 8.1+） |
| 扩展 | `pdo_mysql`、`mbstring`、`fileinfo` |
| 数据库 | MySQL 5.7+ / MariaDB 10.2+ |
| Web 服务器 | Apache（mod_rewrite）或 Nginx |

---

## 安装

### 1. 获取代码

```bash
git clone https://github.com/techjiang/OpenBlog.git
cd OpenBlog
```

### 2. 配置 Web 服务器

**Apache**：项目已自带 `.htaccess`，确认开启 `mod_rewrite` 即可。建议把站点根目录指向项目根目录。

**Nginx**：

```nginx
server {
    listen 80;
    server_name blog.example.com;
    root /var/www/OpenBlog;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # 禁止访问敏感目录
    location ~ ^/(app|storage)/ { deny all; }
    location ~ /\. { deny all; }
}
```

### 3. 目录权限

```bash
mkdir -p storage/uploads storage/cache
chmod -R 775 storage
chmod 666 app/config.php
```

### 4. 运行安装向导

浏览器访问 `http://your-domain/install/`，按提示填写：

- 数据库连接信息（主机、端口、库名、用户、密码、表前缀）
- 站点名称
- 管理员账号 / 邮箱 / 密码

向导会自动建库、建表、写入默认设置并生成管理员。

### 5. 安装完成后

```bash
rm -rf install/     # 务必删除，防止被重复安装
```

访问 `/` 查看前台，`/admin/login` 进入后台。

---

## 目录结构

```
OpenBlog/
├── index.php                 # 唯一入口（含静态资源直通）
├── .htaccess                 # Apache 规则
├── app/
│   ├── config.php            # 配置（安装向导生成）
│   ├── bootstrap.php         # 启动
│   ├── routes.php            # 路由表
│   ├── Core/                 # 框架核心
│   │   ├── App.php           # 引导 / 依赖容器
│   │   ├── Router.php        # 路由分发
│   │   ├── Database.php      # PDO 封装
│   │   ├── Query.php         # 链式查询构造器
│   │   ├── Model.php         # 模型基类
│   │   ├── View.php          # 模板渲染
│   │   ├── Request.php       # 请求封装
│   │   ├── Auth.php          # 认证与权限
│   │   ├── Security.php      # CSRF / 限流
│   │   ├── Validator.php     # 表单校验
│   │   ├── Settings.php      # 站点设置
│   │   ├── Upload.php        # 文件上传
│   │   ├── Markdown.php      # Markdown 解析
│   │   ├── Paginator.php     # 分页
│   │   └── helpers.php       # 全局函数
│   ├── Controllers/          # 前台控制器
│   ├── Controllers/Admin/    # 后台控制器
│   ├── Middleware/           # 中间件
│   ├── Models/               # 数据模型
│   └── views/                # 视图模板
├── public/
│   ├── css/                  # 样式
│   └── js/                   # 脚本
├── storage/
│   ├── uploads/              # 上传文件
│   └── cache/                # 缓存
├── install/                  # 安装向导（安装后请删除）
└── README.md
```

---

## 路由一览

### 前台

| 方法 | 路径 | 说明 |
| --- | --- | --- |
| GET | `/` `/page/{n}` | 首页（分页） |
| GET | `/post/{slug}` | 文章详情 |
| GET | `/page/{slug}` | 独立页面 |
| GET | `/archive` `/archive/{y}` `/archive/{y}/{m}` | 归档 |
| GET | `/category/{slug}` | 分类 |
| GET | `/tag/{slug}` | 标签 |
| GET | `/author/{username}` | 作者 |
| GET | `/search?q=` | 搜索 |
| GET | `/topics` | 分类与标签总览 |
| POST | `/comments/{id}` | 提交评论 |
| POST | `/posts/{id}/like` | 点赞 |
| GET | `/feed.xml` `/rss.xml` `/atom.xml` | 订阅源 |
| GET | `/sitemap.xml` | 站点地图 |
| GET | `/api/posts` `/api/search` | JSON API |

### 后台（需登录）

`/admin`（仪表盘）、`/admin/posts`、`/admin/pages`、`/admin/categories`、`/admin/tags`、`/admin/comments`、`/admin/media`、`/admin/users`、`/admin/settings`

---

## 二次开发

### 新增一个页面

1. 在 `app/routes.php` 注册路由：

```php
$router->get('/hello', 'HelloController@index');
```

2. 建立控制器 `app/Controllers/HelloController.php`：

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

class HelloController extends Controller
{
    public function index(): string
    {
        return $this->render('hello/index', $this->shared([
            'title' => '你好',
        ]));
    }
}
```

3. 建立视图 `app/views/hello/index.php`。

### 数据库查询

```php
// 链式构造器
$rows = Post::published()->where('featured', 1)->limit(5)->get();

// 带关联
$rows = Post::withMeta()->where('p.category_id', 3)->orderByRaw('p.published_at DESC')->get();

// 原生 SQL（预处理）
$row = App::instance()->db->fetch('SELECT * FROM ob_posts WHERE id = :id', ['id' => 1]);

// 分页
$result = Post::paginatePublished($page, 10);   // ['items','total','per_page','current','last_page']
```

### 常用助手函数

| 函数 | 说明 |
| --- | --- |
| `e($str)` | HTML 转义 |
| `url($path)` | 生成站内绝对 URL |
| `asset($path)` | 生成静态资源 URL（带版本号） |
| `setting($key, $default)` | 读取站点设置 |
| `slugify($text)` | 生成别名（保留中文） |
| `markdown($text)` | Markdown 转 HTML |
| `human_date($dt)` / `time_ago($dt)` | 时间格式化 |
| `str_limit($str, $n)` | 字符串截断 |
| `csrf_field()` | 输出 CSRF 隐藏域 |
| `old($key)` | 表单回填 |
| `auth()` / `auth_check()` | 当前登录用户 |
| `redirect($to)` | 重定向 |

---

## 常见问题

**Q：页面 404 或路由不生效？**
Apache 请确认 `mod_rewrite` 已开启且 `AllowOverride All`；Nginx 请确认 `try_files` 配置正确。

**Q：样式没有加载？**
检查 `storage/` 与 `public/` 权限；资源通过 `/assets/*` 访问，由 `index.php` 直通输出，无需额外 rewrite。

**Q：上传图片失败？**
确认 `storage/uploads` 可写，且 `upload_max_size`（后台设置）小于 php.ini 的 `upload_max_filesize` 与 `post_max_size`。

**Q：想改主题色？**
后台「设置 → 外观 → 主题色」，或直接在 `ob_settings` 表修改 `accent_color`。

---

## 安全建议

- 安装完成后**删除 `install/` 目录**
- 生产环境将 `app/config.php` 中的 `debug` 改为 `false`
- 为 `app/` 与 `storage/` 配置禁止直接访问
- 定期备份数据库与 `storage/uploads`
- 使用 HTTPS，并在 `App::initSession()` 中启用 `secure` Cookie

---

## 许可

MIT License。详见 [LICENSE](LICENSE)。
