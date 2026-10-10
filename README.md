# OpenBlog

一个极简、现代、零框架依赖的纯 PHP 博客系统。前后台完整，支持深色模式与移动端响应式。

## 特性

- **零依赖**：无需 Composer，`app/Core` 内置 PSR-4 自动加载，开箱即用
- **双主题**：浅色 / 深色，跟随系统偏好并支持手动切换（localStorage 持久化）
- **响应式**：桌面端常驻侧边栏，移动端汉堡抽屉，断点统一
- **双数据库**：SQLite（开箱演示）与 MySQL（生产）均支持
- **完整后台**：文章、分类、标签、评论、媒体、站点设置

## 环境要求

- PHP 8.1+

## 本地一键运行（推荐，SQLite 演示）

Windows（PowerShell）：

```powershell
.\run.ps1
```

脚本会：自动定位 PHP → 首次运行生成演示数据 → 启动 `php -S` 开发服务器 → 打开浏览器。

- 前台：http://localhost:8000/
- 后台：http://localhost:8000/admin
- 演示账号：`admin` / `admin123`

重置演示数据：

```powershell
php tools/seed_sqlite.php
```

## 手动运行（任意平台）

```bash
# 1. 生成演示数据（脚本优先读取 app/config.dev.php 的 SQLite 配置）
php tools/seed_sqlite.php

# 2. 启动 PHP 内置服务器（用 dev-router.php 作为路由入口）
php -S localhost:8000 -t . dev-router.php
```

> 若要自定义 SQLite 数据库路径，编辑 `app/config.dev.php` 的 `db.path` 即可。

## 生产部署（MySQL）

1. 创建 MySQL 数据库，并建立数据表（建表语句见 `tools/seed_sqlite.php` 的 schema，或直接通过安装向导）
2. 编辑 `app/config.php` 的 `db` 段为 MySQL 连接信息，并将 `installed` 设为 `true`
3. 配置 Web 服务器（Nginx / Apache），文档根指向 `public/`，并将所有请求重写到根目录 `index.php`
   - `dev-router.php` 仅用于 PHP 内置服务器；生产环境请使用正式 Web 服务器 + `index.php` 入口
4. **删除 `app/config.dev.php`**（若存在），避免被优先加载为 SQLite 演示配置

## 目录结构

```
app/
  config.php        生产配置（MySQL）
  config.dev.php    本地演示配置（SQLite，优先加载，可删除）
  Core/             核心：自动加载、辅助函数、数据库、视图
  Controllers/      前台/后台控制器
  Models/           数据模型
  views/            视图模板
public/             静态资源（CSS / JS / 图片）
tools/seed_sqlite.php  SQLite 演示数据生成器
dev-router.php      PHP 内置服务器路由入口
run.ps1             Windows 一键启动脚本
```
