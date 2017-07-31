@echo off
@setlocal

set BASE_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

"%PHP_COMMAND%" "%BASE_PATH%je" %*

@endlocal