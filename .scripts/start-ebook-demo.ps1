$ErrorActionPreference = 'Stop'
$backendDirectory = Split-Path $PSScriptRoot -Parent
Set-Location -LiteralPath $backendDirectory
if (-not (Test-Path -LiteralPath (Join-Path $backendDirectory 'storage\app\ebook-demo.sqlite'))) {
    & php -d extension=pdo_sqlite -d extension=sqlite3 .scripts/ebook-demo-setup.php
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}
Write-Output 'eiMaths Demo API: http://127.0.0.1:8787/api/ebook/v1'
Write-Output 'Demo admin: http://127.0.0.1:8787/ebook-demo'
& php -d extension=pdo_sqlite -d extension=sqlite3 -d upload_max_filesize=20M -d post_max_size=24M -d max_execution_time=0 -S 127.0.0.1:8787 -t public .scripts/ebook-demo-router.php
