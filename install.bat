@echo off
setlocal
chcp 65001 >nul
cd /d "%~dp0"
title Instalasi SIMKEG

echo ==============================================
echo   Instalasi SIMKEG
echo ==============================================
echo.

call windows\cari-php.bat || goto :gagal
echo PHP ditemukan: %PHP%
echo.

if not exist "vendor\autoload.php" (
    echo [GAGAL] Folder vendor tidak ada.
    echo         Pakai file ZIP rilis SIMKEG untuk Windows, bukan "Download ZIP" dari GitHub.
    goto :gagal
)

if not exist ".env" (
    copy /y ".env.laragon.example" ".env" >nul || goto :gagal
    echo File pengaturan .env dibuat.
    echo.
)

"%PHP%" artisan simkeg:pasang || goto :gagal

echo.
pause
exit /b 0

:gagal
echo.
echo Instalasi belum selesai. Perbaiki masalah di atas, lalu klik dua kali install.bat lagi.
pause
exit /b 1
