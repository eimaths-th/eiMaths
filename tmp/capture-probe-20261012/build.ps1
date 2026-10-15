$ErrorActionPreference = 'Stop'
$probeRoot = $PSScriptRoot
$sdkRoot = Join-Path $env:LOCALAPPDATA 'Android\Sdk'
$buildTools = Join-Path $sdkRoot 'build-tools\36.0.0'
$androidJar = Join-Path $sdkRoot 'platforms\android-36\android.jar'
$jdkRoot = 'C:\Program Files\Android\Android Studio\jbr'
$env:JAVA_HOME = $jdkRoot
$env:Path = "$jdkRoot\bin;$env:Path"
$classDir = Join-Path $probeRoot 'classes'
$dexDir = Join-Path $probeRoot 'dex'
New-Item -ItemType Directory -Force -Path $classDir, $dexDir | Out-Null
& "$jdkRoot\bin\javac.exe" -source 8 -target 8 -classpath $androidJar -d $classDir "$probeRoot\ProbeActivity.java"
if ($LASTEXITCODE -ne 0) { throw 'javac failed' }
$classes = @(Get-ChildItem -LiteralPath $classDir -Filter '*.class' -Recurse | ForEach-Object { $_.FullName })
& "$buildTools\d8.bat" --lib $androidJar --min-api 34 --output $dexDir @classes
if ($LASTEXITCODE -ne 0) { throw 'd8 failed' }
$unsigned = Join-Path $probeRoot 'unsigned.apk'
& "$buildTools\aapt.exe" package -f -M "$probeRoot\AndroidManifest.xml" -I $androidJar -F $unsigned
if ($LASTEXITCODE -ne 0) { throw 'aapt package failed' }
Push-Location $dexDir
try {
    & "$buildTools\aapt.exe" add $unsigned 'classes.dex'
    if ($LASTEXITCODE -ne 0) { throw 'aapt add failed' }
} finally { Pop-Location }
$aligned = Join-Path $probeRoot 'aligned.apk'
& "$buildTools\zipalign.exe" -f 4 $unsigned $aligned
if ($LASTEXITCODE -ne 0) { throw 'zipalign failed' }
$apk = Join-Path $probeRoot 'eimaths-capture-qa.apk'
& "$jdkRoot\bin\java.exe" -jar "$buildTools\lib\apksigner.jar" sign --ks "$env:USERPROFILE\.android\debug.keystore" --ks-key-alias androiddebugkey --ks-pass pass:android --key-pass pass:android --out $apk $aligned
if ($LASTEXITCODE -ne 0) { throw 'sign failed' }
& "$jdkRoot\bin\java.exe" -jar "$buildTools\lib\apksigner.jar" verify $apk
if ($LASTEXITCODE -ne 0) { throw 'signature verification failed' }
Get-Item -LiteralPath $apk | Select-Object FullName, Length
