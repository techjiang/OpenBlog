<?php
/**
 * OpenBlog - 安装向导
 *
 * 步骤：环境检查 → 数据库配置 → 建表与管理员 → 完成
 * 安装完成后请删除 install 目录。
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

$configFile = BASE_PATH . '/app/config.php';
$config = is_file($configFile) ? require $configFile : ['installed' => false, 'db' => [], 'app' => []];

if (($config['installed'] ?? false) && ($_GET['force'] ?? '') !== '1') {
    exit('OpenBlog 已安装。如需重新安装请访问 <code>install/?force=1</code>。');
}

session_name('openblog_session');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$step = (int)($_GET['step'] ?? 1);
$errors = [];
$success = '';

/** ---------- 步骤 1：环境检查 ---------- */
function checkEnvironment(): array
{
    $base = dirname(__DIR__);
    $dirs = [
        'storage'         => $base . '/storage',
        'storage/uploads' => $base . '/storage/uploads',
        'storage/cache'   => $base . '/storage/cache',
        'app'             => $base . '/app',
    ];

    $checks = [
        [
            'label'   => 'PHP 版本 ≥ 8.0',
            'ok'      => PHP_VERSION_ID >= 80000,
            'current' => PHP_VERSION,
        ],
        [
            'label'   => 'PDO MySQL 扩展',
            'ok'      => extension_loaded('pdo_mysql'),
            'current' => extension_loaded('pdo_mysql') ? '已加载' : '缺失',
        ],
        [
            'label'   => 'mbstring 扩展',
            'ok'      => extension_loaded('mbstring'),
            'current' => extension_loaded('mbstring') ? '已加载' : '缺失',
        ],
        [
            'label'   => 'GD 或 fileinfo 扩展',
            'ok'      => extension_loaded('fileinfo'),
            'current' => extension_loaded('fileinfo') ? '已加载' : '缺失',
        ],
    ];

    foreach ($dirs as $name => $path) {
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $checks[] = [
            'label'   => "目录可写：{$name}",
            'ok'      => is_dir($path) && is_writable($path),
            'current' => is_dir($path) ? (is_writable($path) ? '可写' : '只读') : '不存在',
        ];
    }

    $checks[] = [
        'label'   => '配置文件可写：app/config.php',
        'ok'      => is_writable($base . '/app/config.php'),
        'current' => is_writable($base . '/app/config.php') ? '可写' : '只读（需手动填写）',
    ];

    return $checks;
}

/** ---------- 步骤 2/3：处理提交 ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim((string)($_POST['db_host'] ?? '127.0.0.1'));
    $dbPort = trim((string)($_POST['db_port'] ?? '3306'));
    $dbName = trim((string)($_POST['db_name'] ?? 'openblog'));
    $dbUser = trim((string)($_POST['db_user'] ?? 'root'));
    $dbPass = (string)($_POST['db_pass'] ?? '');
    $dbPrefix = trim((string)($_POST['db_prefix'] ?? 'ob_'));

    $siteName = trim((string)($_POST['site_name'] ?? 'OpenBlog'));
    $adminUser = trim((string)($_POST['admin_user'] ?? 'admin'));
    $adminEmail = trim((string)($_POST['admin_email'] ?? ''));
    $adminPass = (string)($_POST['admin_pass'] ?? '');

    if ($dbName === '') {
        $errors[] = '数据库名不能为空';
    }
    if ($adminUser === '' || mb_strlen($adminUser) < 3) {
        $errors[] = '管理员账号至少 3 个字符';
    }
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '管理员邮箱格式不正确';
    }
    if (mb_strlen($adminPass) < 6) {
        $errors[] = '管理员密码至少 6 位';
    }

    if ($errors === []) {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $dbHost, $dbPort),
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                str_replace('`', '', $dbName)
            ));
            $pdo->exec('USE `' . str_replace('`', '', $dbName) . '`');

            $sql = file_get_contents(BASE_PATH . '/install/openblog.sql');
            if ($dbPrefix !== 'ob_') {
                $sql = str_replace('`ob_', '`' . $dbPrefix, (string)$sql);
            }
            $pdo->exec($sql);

            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO `' . $dbPrefix . 'users` (username, email, password_hash, display_name, role, status, created_at)
                 VALUES (?, ?, ?, ?, \'admin\', 1, NOW())
                 ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
            );
            $stmt->execute([$adminUser, $adminEmail, $hash, $adminUser]);

            $pdo->prepare('UPDATE `' . $dbPrefix . 'settings` SET `value` = ? WHERE `key` = ?')
                ->execute([$siteName, 'site_name']);

            $newConfig = [
                'app' => [
                    'name'     => 'OpenBlog',
                    'url'      => '',
                    'debug'    => false,
                    'timezone' => 'Asia/Shanghai',
                    'locale'   => 'zh-CN',
                ],
                'db' => [
                    'host'     => $dbHost,
                    'port'     => $dbPort,
                    'database' => $dbName,
                    'username' => $dbUser,
                    'password' => $dbPass,
                    'charset'  => 'utf8mb4',
                ],
                'installed' => true,
            ];

            $export = var_export($newConfig, true);
            $content = "<?php\n/**\n * OpenBlog - 应用配置（由安装向导生成 " . date('Y-m-d H:i:s') . "）\n */\n\ndeclare(strict_types=1);\n\nreturn " . $export . ";\n";
            @file_put_contents($configFile, $content);

            $success = '安装成功';
            $step = 4;
        } catch (Throwable $e) {
            $errors[] = '安装失败：' . $e->getMessage();
            $step = 2;
        }
    } else {
        $step = 2;
    }
}

$checks = checkEnvironment();
$allPassed = !in_array(false, array_column($checks, 'ok'), true);
if ($step === 1 && !$allPassed) {
    // 仍允许继续，但给出提示
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>安装 OpenBlog</title>
<style>
  :root{--accent:#4f46e5;--bg:#f5f6fa;--card:#fff;--text:#1f2430;--muted:#6b7280;--border:#e5e7eb}
  *{box-sizing:border-box}
  body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;
       background:var(--bg);color:var(--text);line-height:1.6}
  .wrap{max-width:720px;margin:48px auto;padding:0 20px}
  .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px;box-shadow:0 10px 30px rgba(17,24,39,.05)}
  h1{margin:0 0 4px;font-size:26px;letter-spacing:-.02em}
  .sub{color:var(--muted);margin:0 0 24px;font-size:14px}
  .steps{display:flex;gap:8px;margin-bottom:28px}
  .steps span{flex:1;height:4px;border-radius:999px;background:var(--border)}
  .steps span.on{background:var(--accent)}
  label{display:block;font-size:13px;font-weight:600;margin:16px 0 6px}
  input{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;font-size:14px;font-family:inherit;
        transition:border-color .15s,box-shadow .15s}
  input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,70,229,.12)}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}
  .btn{margin-top:24px;width:100%;padding:12px;border:0;border-radius:10px;background:var(--accent);color:#fff;
       font-size:15px;font-weight:600;cursor:pointer;font-family:inherit}
  .btn:hover{background:#4338ca}
  .chk{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:14px}
  .chk b{font-weight:500}
  .ok{color:#059669;font-weight:600}.bad{color:#dc2626;font-weight:600}
  .err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:14px}
  .ok-box{background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:14px 16px;border-radius:10px;font-size:14px}
  code{background:#f3f4f6;padding:2px 6px;border-radius:5px;font-size:13px}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>安装 OpenBlog</h1>
    <p class="sub">零框架依赖的纯 PHP 博客系统 · v1.0.0</p>

    <div class="steps">
      <span class="<?= $step >= 1 ? 'on' : '' ?>"></span>
      <span class="<?= $step >= 2 ? 'on' : '' ?>"></span>
      <span class="<?= $step >= 3 ? 'on' : '' ?>"></span>
      <span class="<?= $step >= 4 ? 'on' : '' ?>"></span>
    </div>

    <?php if ($errors !== []): ?>
      <div class="err"><?php foreach ($errors as $err) echo '<div>' . htmlspecialchars($err, ENT_QUOTES) . '</div>'; ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
      <h3 style="margin:0 0 8px;font-size:16px">环境检查</h3>
      <?php foreach ($checks as $c): ?>
        <div class="chk">
          <b><?= htmlspecialchars($c['label']) ?></b>
          <span class="<?= $c['ok'] ? 'ok' : 'bad' ?>"><?= htmlspecialchars((string)$c['current']) ?></span>
        </div>
      <?php endforeach; ?>
      <form method="get" action="">
        <input type="hidden" name="step" value="2">
        <button class="btn" type="submit" <?= $allPassed ? '' : 'onclick="return confirm(\'存在未通过的检查项，仍要继续吗？\')"' ?>>下一步</button>
      </form>

    <?php elseif ($step === 2): ?>
      <h3 style="margin:0 0 8px;font-size:16px">数据库与管理员</h3>
      <form method="post" action="">
        <label>数据库主机</label>
        <input name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? '127.0.0.1') ?>" required>
        <div class="grid2">
          <div>
            <label>端口</label>
            <input name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>" required>
          </div>
          <div>
            <label>数据库名</label>
            <input name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'openblog') ?>" required>
          </div>
        </div>
        <div class="grid2">
          <div>
            <label>用户名</label>
            <input name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? 'root') ?>" required>
          </div>
          <div>
            <label>密码</label>
            <input name="db_pass" type="password" value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
          </div>
        </div>
        <label>表前缀</label>
        <input name="db_prefix" value="<?= htmlspecialchars($_POST['db_prefix'] ?? 'ob_') ?>">

        <hr style="border:0;border-top:1px solid var(--border);margin:26px 0">

        <label>站点名称</label>
        <input name="site_name" value="<?= htmlspecialchars($_POST['site_name'] ?? 'OpenBlog') ?>" required>
        <label>管理员账号</label>
        <input name="admin_user" value="<?= htmlspecialchars($_POST['admin_user'] ?? 'admin') ?>" required>
        <label>管理员邮箱</label>
        <input name="admin_email" type="email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" required>
        <label>管理员密码</label>
        <input name="admin_pass" type="password" required placeholder="至少 6 位">

        <button class="btn" type="submit">开始安装</button>
      </form>

    <?php else: ?>
      <div class="ok-box">
        <strong><?= htmlspecialchars($success) ?></strong><br>
        数据库已初始化，管理员账号已创建。
      </div>
      <p style="margin-top:20px;font-size:14px">
        下一步：<br>
        1. 删除 <code>install</code> 目录以禁止再次安装<br>
        2. 访问 <a href="../">网站首页</a> 或 <a href="../admin/login">后台登录</a>
      </p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
