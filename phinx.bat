@echo off

REM Get arguments
@set args=%*

REM Run Phinx
php "%~dp0vendor\robmorgan\phinx\bin\phinx" %args%