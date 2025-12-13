@echo off
cd /d "%~dp0"
powershell -ExecutionPolicy Bypass -File "start-server.ps1"
pause

