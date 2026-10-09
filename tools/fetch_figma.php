<?php
/**
 * 拉取 Figma 设计文件的结构化 JSON（节点树），供后续解析还原 UI。
 *
 * 用法:
 *   php tools/fetch_figma.php <FIGMA_PERSONAL_ACCESS_TOKEN> [FILE_KEY]
 *
 * 说明:
 *   - 需要 PHP 的 curl 与 json 扩展。
 *   - 令牌形如 figd_xxxxxxxx，在 Figma -> 头像 -> Settings -> Security
 *     -> Personal access tokens 生成。
 *   - 拉取结果保存为 data/figma.json（不纳入版本库）。
 *
 * 注意: 该令牌属于私有凭证，仅用于本次本地拉取，请勿提交到仓库。
 */

$fileKey = $argv[2] ?? 's5shZ0ABCFvzHabOMMEsOY';
$token   = $argv[1] ?? null;

if (!$token) {
    fwrite(STDERR, "用法: php tools/fetch_figma.php <FIGMA_PERSONAL_ACCESS_TOKEN> [FILE_KEY]\n");
    exit(1);
}

$url = sprintf('https://api.figma.com/v1/files/%s?geometry=0&depth=2', $fileKey);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER    => ['X-Figma-Token: ' . $token],
    CURLOPT_TIMEOUT       => 90,
    CURLOPT_USERAGENT     => 'OpenBlog-FigmaFetcher/1.0',
]);
$resp = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($code !== 200) {
    fwrite(STDERR, sprintf("Figma API 返回 HTTP %d\n%s\n", $code, substr((string)$resp, 0, 800)));
    if ($err) fwrite(STDERR, "curl: {$err}\n");
    exit(1);
}

$json = json_decode($resp, true);
if (!isset($json['document'])) {
    fwrite(STDERR, "响应中未包含 document 字段，可能令牌无效或文件不可访问。\n");
    exit(1);
}

$dir = __DIR__ . '/../data';
is_dir($dir) or mkdir($dir, 0775, true);
file_put_contents($dir . '/figma.json', $resp);

echo "已保存: data/figma.json\n";
echo "文件名称: " . ($json['name'] ?? '?') . "\n";
$pages = $json['document']['children'] ?? [];
echo "页面数量: " . count($pages) . "\n";
foreach ($pages as $p) {
    echo "  - " . ($p['name'] ?? '?') . "\n";
}
