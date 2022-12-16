@echo off

REM Get arguments
@set args=%*

REM If first arg is "reset"
if "%1"=="reset" (
    php "%~dp0vendor\bin\phinx" rollback -t 0
    php "%~dp0vendor\bin\phinx" migrate
    exit /b
)

REM Run Phinx
php "%~dp0vendor\bin\phinx" %args%