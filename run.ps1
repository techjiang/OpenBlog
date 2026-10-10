#!/usr/bin/env pwsh
<#
.SYNOPSIS
    OpenBlog 本地一键启动（SQLite 演示数据）
.DESCRIPTION
    自动定位 PHP、按需生成演示数据、启动 PHP 内置开发服务器并打开浏览器。
#>
$ErrorActionPreference = 'Stop'
$repo = Split-Path -Parent $MyInvocation.MyCommand.Path

# 1) 定位 PHP 可执行文件
$phpCandidates = @('php', 'd:/XM/php8/php.exe', 'C:/php/php.exe', 'C:/Tools/php/php.exe')
$php = $null
foreach ($c in $phpCandidates) {
    if ($c -eq 'php') {
        $p = Get-Command php -ErrorAction SilentlyContinue
        if ($p) { $php = $p.Source; break }
    } elseif (Test-Path $c) {
        $php = $c; break
    }
}
if (-not $php) {
    Write-Error "未找到 PHP。请将 php 加入 PATH，或编辑本脚本的 `$phpCandidates 指定 php.exe 绝对路径。"
    exit 1
}
Write-Host "使用 PHP：$php"

# 2) 首次运行生成 SQLite 演示数据
$db = Join-Path $repo 'storage/openblog.sqlite'
if (-not (Test-Path $db)) {
    Write-Host "生成 SQLite 演示数据…"
    & $php (Join-Path $repo 'tools/seed_sqlite.php')
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

# 3) 启动开发服务器（dev-router 负责静态资源与入口分发）
$port = 8000
Write-Host "启动开发服务器 http://localhost:$port/ …"
$proc = Start-Process -FilePath $php -ArgumentList "-S", "localhost:$port", "-t", $repo, (Join-Path $repo 'dev-router.php') `
    -WorkingDirectory $repo -WindowStyle Hidden -PassThru

# 4) 打开浏览器
Start-Sleep -Seconds 2
Start-Process "http://localhost:$port/"

Write-Host ""
Write-Host "OpenBlog 运行中（后台进程 PID $($proc.Id)）。"
Write-Host "前台：http://localhost:$port/    后台：http://localhost:$port/admin"
Write-Host "演示账号：admin / admin123"
Write-Host "按任意键停止服务器…"
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')

Stop-Process -Id $proc.Id -Force
Write-Host "已停止服务器。"
