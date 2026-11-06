@echo off
chcp 65001 >nul
title LaraMart Test ve Veritabani Kontrolu
set "PHP_DIR=C:\Users\Onur\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe"
set "PATH=%PHP_DIR%;C:\Program Files\nodejs;%PATH%"
cd /d "C:\Users\Onur\Desktop\LaraMart"

echo ========================================================
echo   1. VERITABANI SIFIRLANIYOR VE DEMO VERILERI YUKLENIYOR
echo ========================================================
"%PHP_DIR%\php.exe" artisan migrate:fresh --seed

echo.
echo ========================================================
echo   2. TUM TESTLER (61 TEST) CALISTIRILIYOR
echo ========================================================
"%PHP_DIR%\php.exe" artisan test

echo.
echo ========================================================
echo   ISLEM TAMAMLANDI!
echo ========================================================
pause

