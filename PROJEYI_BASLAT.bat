@echo off
chcp 65001 >nul
title LaraMart E-Ticaret Pazaryeri Sunucusu
set "PHP_DIR=C:\Users\Onur\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe"
set "PATH=%PHP_DIR%;C:\Program Files\nodejs;%PATH%"
cd /d "C:\Users\Onur\Desktop\LaraMart"

echo ========================================================
echo   LARAMART E-TICARET PAZARYERI BASLATILIYOR...
echo ========================================================
echo.
echo Tarayicinizda su adresi acin: http://localhost:8000
echo.
echo Kapatmak icin bu pencereyi kapatabilir veya CTRL+C basabilirsiniz.
echo ========================================================
echo.

start http://localhost:8000
"%PHP_DIR%\php.exe" artisan serve
pause

