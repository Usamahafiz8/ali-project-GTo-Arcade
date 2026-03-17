@echo off
:: This script adds the required local domains to Windows hosts file.
:: Right-click this file and select "Run as administrator"

echo Adding FishThunder local domains to hosts file...

echo 127.0.0.1 www.localhost >> C:\Windows\System32\drivers\etc\hosts
echo 127.0.0.1 admin.localhost >> C:\Windows\System32\drivers\etc\hosts

echo.
echo Done! Hosts file updated.
echo.
echo www.localhost   -> Frontend (player site)
echo admin.localhost -> Backend (admin panel)
echo.
echo Both require port 8000 in your browser:
echo   http://www.localhost:8000
echo   http://admin.localhost:8000
echo.
pause
