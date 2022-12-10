@echo off

REM Get arguments
@set args=%*

REM Run Phinx
php "%~dp0vendor\bin\phinx" %args%