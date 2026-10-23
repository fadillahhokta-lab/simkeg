@echo off
rem Mencari php.exe versi 8.3 ke atas: folder Laragon dulu, lalu PATH.
rem Hasil: variabel PHP berisi path php.exe. Exit code 1 bila tidak ketemu.

set "PHP="
for %%R in (C D E) do (
    for /d %%D in ("%%R:\laragon\bin\php\php-8*") do (
        if exist "%%D\php.exe" (
            "%%D\php.exe" -r "exit(version_compare(PHP_VERSION, '8.3.0', '>=') ? 0 : 1);" >nul 2>&1 && set "PHP=%%D\php.exe"
        )
    )
)

if not defined PHP (
    for /f "delims=" %%P in ('where php 2^>nul') do (
        if not defined PHP (
            "%%P" -r "exit(version_compare(PHP_VERSION, '8.3.0', '>=') ? 0 : 1);" >nul 2>&1 && set "PHP=%%P"
        )
    )
)

if not defined PHP (
    echo.
    echo [GAGAL] PHP 8.3 atau lebih baru tidak ditemukan.
    echo         Tambahkan PHP 8.3 ke Laragon. Lihat docs\panduan\instalasi-windows.md langkah 2.
    exit /b 1
)
exit /b 0
