param(
    [string]$PhpExecutable = 'php',
    [string]$PopplerBin = ''
)

$ErrorActionPreference = 'Stop'
$backendDirectory = Split-Path $PSScriptRoot -Parent
$router = Join-Path $backendDirectory 'vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php'
$previousPdfinfo = $env:EBOOK_PDFINFO
$previousPdftoppm = $env:EBOOK_PDFTOPPM

try {
    if ($PopplerBin) {
        $pdfinfo = Join-Path $PopplerBin 'pdfinfo.exe'
        $pdftoppm = Join-Path $PopplerBin 'pdftoppm.exe'
        if (-not (Test-Path -LiteralPath $pdfinfo) -or -not (Test-Path -LiteralPath $pdftoppm)) {
            throw 'PopplerBin must contain pdfinfo.exe and pdftoppm.exe.'
        }
        $env:EBOOK_PDFINFO = $pdfinfo
        $env:EBOOK_PDFTOPPM = $pdftoppm
    }
    Push-Location -LiteralPath (Join-Path $backendDirectory 'public')
    try {
        Write-Output 'eiMaths local backend: http://127.0.0.1:8000/ebooks'
        # Settings apply only to this development process, not php.ini or .env.
        & $PhpExecutable -d upload_max_filesize=20M -d post_max_size=24M -d max_execution_time=0 -S 127.0.0.1:8000 $router
    } finally { Pop-Location }
} finally {
    $env:EBOOK_PDFINFO = $previousPdfinfo
    $env:EBOOK_PDFTOPPM = $previousPdftoppm
}
