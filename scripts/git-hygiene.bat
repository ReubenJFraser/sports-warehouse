@echo off
setlocal ENABLEDELAYEDEXPANSION

:: ------------------------------------------------------------
:: git-hygiene.bat
:: Quick repo hygiene checks to validate .gitignore behavior.
:: Usage:
::   scripts\git-hygiene.bat
::   scripts\git-hygiene.bat --full   (also lists every tracked file)
:: ------------------------------------------------------------

:: 1) Check that Git is available
where git >NUL 2>&1
if errorlevel 1 (
  echo [ERROR] Git is not on PATH. Install Git or open a Git-enabled shell.
  goto :end
)

:: 2) Verify we are inside a Git work tree
git rev-parse --is-inside-work-tree >NUL 2>&1
if errorlevel 1 (
  echo [ERROR] This folder is not a Git repository.
  echo         Open a terminal in your repo root and re-run:
  echo         scripts\git-hygiene.bat
  goto :end
)

echo.
echo ============================================================
echo   REPO HYGIENE CHECK
echo   Path: %CD%
echo   Branch: 
for /f "delims=" %%i in ('git rev-parse --abbrev-ref HEAD') do @echo     %%i
echo   Last commit:
for /f "delims=" %%i in ('git log -1 --pretty^=format:"%%h - %%s (%%cr) by %%an"') do @echo     %%i
echo ============================================================
echo.

:: 3) Show status including ignored files (quick clutter scan)
echo [1/6] git status --ignored
git status --ignored --short
echo.

:: 4) List tracked .gitkeep placeholders
echo [2/6] Tracked .gitkeep files
git ls-files | findstr /R /C:".gitkeep$"
if errorlevel 1 echo   (none)
echo.

:: 5) Verify only the seed SQL is tracked
echo [3/6] Tracked .sql files (expect ONLY db/sportswh_seed.sql)
git ls-files | findstr /R /C:".sql$"
if errorlevel 1 echo   (none)
echo.

:: 6) Ensure vendor/node_modules are not tracked
echo [4/6] Tracked vendor / node_modules (expect NONE)
git ls-files | findstr /R /C:"\\vendor\\" /C:"\\node_modules\\"
if errorlevel 1 echo   (none)
echo.

:: 7) Working tree cleanliness
echo [5/6] git status
git status --short
if errorlevel 1 (
  echo (status returned an error)
)
echo.

:: 8) Optional: full tracked file list
if /I "%~1"=="--full" (
  echo [6/6] Full tracked file list
  git ls-files | more
  echo.
) else (
  echo [6/6] Full tracked file list skipped. Use --full to show it.
  echo.
)

:: 9) Summary counts
for /f "delims=" %%i in ('git ls-files ^| find /c /v ""') do set TRACKED=%%i
for /f "delims=" %%i in ('git ls-files ^| findstr /R /C:".gitkeep$" ^| find /c /v ""') do set KEEPS=%%i
for /f "delims=" %%i in ('git ls-files ^| findstr /R /C:".sql$" ^| find /c /v ""') do set SQLS=%%i

echo ---------------- Summary ----------------
echo Tracked files total : %TRACKED%
echo Tracked .gitkeep    : %KEEPS%
echo Tracked .sql        : %SQLS%   (expect 1: db/sportswh_seed.sql)
echo -----------------------------------------

:end
echo.
echo Done. Press any key to close...
pause >NUL
endlocal

