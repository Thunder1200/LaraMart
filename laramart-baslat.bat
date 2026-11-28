@echo off
chcp 65001 >nul
title LaraMart — Hizli Yerel Sunucu Baslatici
cd /d "%~dp0"

set PHP_FAST_OPTS=-d zend_extension=opcache -d opcache.enable=1 -d opcache.enable_cli=1 -d opcache.memory_consumption=128 -d opcache.interned_strings_buffer=16 -d opcache.max_accelerated_files=10000 -d opcache.revalidate_freq=2

echo ==================================================
echo   LaraMart (Laravel 13) Hizli Modda Baslatiliyor
echo ==================================================
echo.

if not exist ".env" (
    echo [1/5] .env dosyasi olusturuluyor...
    copy ".env.example" ".env" >nul
    php artisan key:generate
)

if not exist "database\database.sqlite" (
    echo [2/5] Veritabani olusturuluyor ve ornek veriler yukleniyor...
    type nul > "database\database.sqlite"
    php artisan migrate:fresh --seed
) else (
    echo [2/5] Veritabani hazir.
)

if not exist "public\build\manifest.json" (
    echo [3/5] Arayuz dosyalari derleniyor ^(npm run build^)...
    call npm run build
) else (
    echo [3/5] Arayuz dosyalari hazir.
)

echo [4/5] Laravel onbellekleri hazirlaniyor ^(optimize^)...
php artisan optimize >nul 2>&1

echo [5/5] Kuyruk ^(queue:work^) ve OPcache destekli web sunucusu aciliyor...
start "LaraMart Queue Worker" /min cmd /c "cd /d "%~dp0" && php %PHP_FAST_OPTS% artisan queue:work --sleep=3 --tries=1"

echo.
echo   Site adresi : http://127.0.0.1:8000
echo   Performans  : OPcache + WebP + Gzip + Cache-Control + SQLite WAL
echo   Kapatmak icin bu pencereyi kapatin veya Ctrl+C yapin.
echo ==================================================
echo.

start "" cmd /c "timeout /t 2 /nobreak >nul && start http://127.0.0.1:8000"
php %PHP_FAST_OPTS% -S 127.0.0.1:8000 -t public public\router-fast.php
