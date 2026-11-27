@echo off
chcp 65001 >nul
title LaraMart — Yerel Sunucu Baslatici
cd /d "%~dp0"

echo ==================================================
echo   LaraMart (Laravel 13) Baslatiliyor...
echo ==================================================
echo.

if not exist ".env" (
    echo [1/4] .env dosyasi olusturuluyor...
    copy ".env.example" ".env" >nul
    php artisan key:generate
)

if not exist "database\database.sqlite" (
    echo [2/4] Veritabani olusturuluyor ve ornek veriler yukleniyor...
    type nul > "database\database.sqlite"
    php artisan migrate:fresh --seed
) else (
    echo [2/4] Veritabani hazir.
)

if not exist "public\build\manifest.json" (
    echo [3/4] Arayuz dosyalari derleniyor ^(npm run build^)...
    call npm run build
) else (
    echo [3/4] Arayuz dosyalari hazir.
)

echo [4/4] Kuyruk (queue) ve web sunucusu aciliyor...
start "LaraMart Queue Worker" /min cmd /c "cd /d "%~dp0" && php artisan queue:listen --tries=1"

echo.
echo   Site adresi : http://127.0.0.1:8000
echo   Kapatmak icin bu pencereyi kapatin veya Ctrl+C yapin.
echo ==================================================
echo.

start "" cmd /c "timeout /t 2 /nobreak >nul && start http://127.0.0.1:8000"
php artisan serve --host=127.0.0.1 --port=8000
