-- =====================================================================
--  OpenBlog - MySQL 数据库结构
--  版本: 1.0.0     字符集: utf8mb4 / utf8mb4_unicode_ci
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 用户
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_users`;
CREATE TABLE `ob_users` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`       VARCHAR(50)  NOT NULL,
  `email`          VARCHAR(120) NOT NULL,
  `password_hash`  VARCHAR(255) NOT NULL,
  `display_name`   VARCHAR(80)  DEFAULT NULL,
  `avatar`         VARCHAR(255) DEFAULT NULL,
  `bio`            TEXT         DEFAULT NULL,
  `website`        VARCHAR(255) DEFAULT NULL,
  `role`           ENUM('admin','editor','author') NOT NULL DEFAULT 'author',
  `status`         TINYINT(1)   NOT NULL DEFAULT 1,
  `remember_token` VARCHAR(64)  DEFAULT NULL,
  `last_login_at`  DATETIME     DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_username` (`username`),
  UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 分类
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_categories`;
CREATE TABLE `ob_categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(80)  NOT NULL,
  `slug`        VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `color`       VARCHAR(20)  DEFAULT NULL,
  `parent_id`   INT UNSIGNED DEFAULT NULL,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `post_count`  INT          NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 标签
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_tags`;
CREATE TABLE `ob_tags` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(50)  NOT NULL,
  `slug`       VARCHAR(80)  NOT NULL,
  `post_count` INT          NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 文章 / 页面
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_posts`;
CREATE TABLE `ob_posts` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED NOT NULL,
  `category_id`     INT UNSIGNED DEFAULT NULL,
  `type`            ENUM('post','page') NOT NULL DEFAULT 'post',
  `title`           VARCHAR(255) NOT NULL,
  `slug`            VARCHAR(255) NOT NULL,
  `excerpt`         TEXT         DEFAULT NULL,
  `content`         LONGTEXT     NOT NULL,
  `content_html`    LONGTEXT     DEFAULT NULL,
  `cover_image`     VARCHAR(255) DEFAULT NULL,
  `status`          ENUM('published','draft','private') NOT NULL DEFAULT 'draft',
  `featured`        TINYINT(1)   NOT NULL DEFAULT 0,
  `allow_comment`   TINYINT(1)   NOT NULL DEFAULT 1,
  `password`        VARCHAR(64)  DEFAULT NULL,
  `view_count`      INT          NOT NULL DEFAULT 0,
  `like_count`      INT          NOT NULL DEFAULT 0,
  `comment_count`   INT          NOT NULL DEFAULT 0,
  `reading_time`    SMALLINT     NOT NULL DEFAULT 1,
  `published_at`    DATETIME     DEFAULT NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_posts_slug` (`slug`),
  KEY `idx_posts_status_published` (`status`, `published_at`),
  KEY `idx_posts_category` (`category_id`),
  KEY `idx_posts_type` (`type`),
  FULLTEXT KEY `ft_posts_search` (`title`, `excerpt`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 文章 - 标签 关联
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_post_tag`;
CREATE TABLE `ob_post_tag` (
  `post_id` INT UNSIGNED NOT NULL,
  `tag_id`  INT UNSIGNED NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 评论
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_comments`;
CREATE TABLE `ob_comments` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id`        INT UNSIGNED NOT NULL,
  `parent_id`      INT UNSIGNED DEFAULT NULL,
  `user_id`        INT UNSIGNED DEFAULT NULL,
  `author_name`    VARCHAR(80)  NOT NULL,
  `author_email`   VARCHAR(120) DEFAULT NULL,
  `author_website` VARCHAR(255) DEFAULT NULL,
  `content`        TEXT         NOT NULL,
  `status`         ENUM('pending','approved','spam','trash') NOT NULL DEFAULT 'pending',
  `ip`             VARCHAR(45)  DEFAULT NULL,
  `user_agent`     VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comments_post` (`post_id`),
  KEY `idx_comments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 媒体库
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_media`;
CREATE TABLE `ob_media` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED DEFAULT NULL,
  `filename`      VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `mime_type`     VARCHAR(80)  DEFAULT NULL,
  `size`          INT UNSIGNED NOT NULL DEFAULT 0,
  `width`         SMALLINT UNSIGNED DEFAULT NULL,
  `height`        SMALLINT UNSIGNED DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 站点设置
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_settings`;
CREATE TABLE `ob_settings` (
  `key`   VARCHAR(80) NOT NULL,
  `value` TEXT        DEFAULT NULL,
  `group` VARCHAR(40) NOT NULL DEFAULT 'general',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 订阅者
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_subscribers`;
CREATE TABLE `ob_subscribers` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(120) NOT NULL,
  `status`     TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_subscribers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 点赞记录（防刷）
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ob_post_likes`;
CREATE TABLE `ob_post_likes` (
  `post_id`    INT UNSIGNED NOT NULL,
  `ip_hash`    CHAR(64)     NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`, `ip_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 默认设置
-- ---------------------------------------------------------------------
INSERT INTO `ob_settings` (`key`, `value`, `group`) VALUES
('site_name',        'OpenBlog',                                  'general'),
('site_tagline',     '一个纯粹的、自由的博客系统',                  'general'),
('site_description', 'OpenBlog 是一个零框架依赖的纯 PHP 博客系统。', 'general'),
('site_keywords',    'OpenBlog,PHP,博客,Blog',                    'general'),
('site_logo',        '',                                          'general'),
('site_icp',         '',                                          'general'),
('admin_email',      'admin@example.com',                         'general'),
('posts_per_page',   '10',                                        'reading'),
('comment_enabled',  '1',                                         'reading'),
('comment_review',   '1',                                         'reading'),
('upload_max_size',  '5',                                         'reading'),
('theme',            'default',                                   'appearance'),
('accent_color',     '#4f46e5',                                   'appearance'),
('home_layout',      'list',                                      'appearance'),
('show_toc',         '1',                                         'appearance'),
('footer_text',      'Powered by OpenBlog',                                  'appearance'),
('social_github',    'https://github.com/techjiang/OpenBlog',     'social'),
('social_twitter',   '',                                          'social'),
('social_email',     '',                                          'social'),
('stat_code',        '',                                          'advanced');

-- ---------------------------------------------------------------------
-- 默认分类
-- ---------------------------------------------------------------------
INSERT INTO `ob_categories` (`name`, `slug`, `description`, `color`, `sort_order`) VALUES
('技术笔记', 'tech',        '记录开发过程中的思考与沉淀', '#4f46e5', 1),
('生活随笔', 'life',        '关于生活、旅行与日常',       '#10b981', 2),
('开源项目', 'open-source', '开源项目进展与发布记录',     '#f59e0b', 3);
