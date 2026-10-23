@echo off
setlocal
chcp 65001 >nul
cd /d "%~dp0"
title SIMKEG

call windows\cari-php.bat || goto :gagal

if not exist ".env" (
    echo [GAGAL] SIMKEG belum dipasang. Klik dua kali install.bat terlebih dahulu.
    goto :gagal
)

"%PHP%" artisan simkeg:cek-database >nul 2>&1
if errorlevel 1 (
    echo [GAGAL] Database belum menyala. Buka Laragon, klik "Start All", lalu jalankan mulai.bat lagi.
    goto :gagal
)

echo ==============================================
echo   SIMKEG berjalan di http://127.0.0.1:8000
echo.
echo   Biarkan jendela ini terbuka selama memakai SIMKEG.
echo   Tutup jendela ini untuk mematikan SIMKEG.
echo ==============================================
echo.

rem Buka browser setelah server sempat menyala.
start "" /min cmd /c "timeout /t 3 /nobreak >nul & start http://127.0.0.1:8000"

"%PHP%" artisan serve --host=127.0.0.1 --port=8000
exit /b 0

:gagal
echo.
pause
exit /b 1
