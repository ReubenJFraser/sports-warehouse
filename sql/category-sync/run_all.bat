@echo off
setlocal enabledelayedexpansion

rem ===========================
rem Config (defaults for DOCKER)
rem ===========================
set "MODE=DOCKER"
if /i "%~1"=="LARAGON" set "MODE=LARAGON"

if /i "%MODE%"=="DOCKER" (
  set "DB_HOST=127.0.0.1"
  set "DB_PORT=3307"
) else (
  set "DB_HOST=127.0.0.1"
  set "DB_PORT=3306"
)

set "DB_NAME=sportswh"
set "DB_USER=root"

rem ===========================
rem Paths
rem ===========================
rem Repo-relative (this .bat is in sql\category-sync)
set "HERE=%~dp0"
pushd "%HERE%"
cd ..

rem Now we are in ...\sql
cd "category-sync"
set "CS_DIR=%cd%"

rem Project root = two levels up
cd ..\..
set "PROJECT_ROOT=%cd%"

rem SQL files
set "DRY_RUN=%CS_DIR%\dry-run.sql"
set "APPLY_SQL=%CS_DIR%\apply.sql"
set "AUDIT_SQL=%CS_DIR%\audit.sql"

rem Reports dir
set "REPORT_DIR=%CS_DIR%\reports"
if not exist "%REPORT_DIR%" mkdir "%REPORT_DIR%"

rem Timestamp
for /f "tokens=1-5 delims=/:. " %%a in ("%date% %time%") do (
  set "TS=%%a-%%b-%%c_%%d%%e"
)
rem sanitize TS: YYYY-MM-DD_HHMMSS-ish depends on locale; that's OK for filenames.

rem ===========================
rem Locate mysql.exe
rem ===========================
set "MYSQL_EXE="
rem Try Laragon auto-detect (common path)
if exist "C:\laragon\bin\mysql" (
  for /f "delims=" %%D in ('dir /b /ad "C:\laragon\bin\mysql"') do (
    if exist "C:\laragon\bin\mysql\%%D\bin\mysql.exe" set "MYSQL_EXE=C:\laragon\bin\mysql\%%D\bin\mysql.exe"
  )
)

rem Fallback to PATH
if "%MYSQL_EXE%"=="" (
  for %%I in (mysql.exe) do set "MYSQL_EXE=%%~$PATH:I"
)

if "%MYSQL_EXE%"=="" (
  echo [ERROR] mysql.exe not found. Ensure MySQL client is installed or Laragon path is correct.
  goto :end
)

echo.
echo === Using MySQL client: %MYSQL_EXE%
echo === Mode: %MODE%  Host: %DB_HOST%  Port: %DB_PORT%  DB: %DB_NAME%  User: %DB_USER%
echo.

rem ===========================
rem Dry Run (non-destructive)
rem ===========================
set "DRY_OUT=%REPORT_DIR%\dry-run-%TS%.tsv"
echo [1/3] Running DRY RUN and saving to "%DRY_OUT%"
"%MYSQL_EXE%" -h %DB_HOST% -P %DB_PORT% -u %DB_USER% -p --default-character-set=utf8mb4 --batch --raw %DB_NAME% < "%DRY_RUN%" > "%DRY_OUT%"
if errorlevel 1 (
  echo [ERROR] Dry run failed. Check credentials or SQL.
  goto :maybe_open_dbeaver
)
echo     Done. (Tab-separated; open in Excel or DBeaver)

rem ===========================
rem Audit snapshot (pre-apply)
rem ===========================
set "AUDIT_OUT=%REPORT_DIR%\audit-%TS%-pre.tsv"
echo [2/3] Running AUDIT (pre-apply snapshot) and saving to "%AUDIT_OUT%"
"%MYSQL_EXE%" -h %DB_HOST% -P %DB_PORT% -u %DB_USER% -p --default-character-set=utf8mb4 --batch --raw %DB_NAME% < "%AUDIT_SQL%" > "%AUDIT_OUT%"
if errorlevel 1 (
  echo [WARN] Audit (pre) failed. You can still review in DBeaver.
)

rem ===========================
rem Open DBeaver with apply.sql
rem ===========================
:maybe_open_dbeaver
set "DBEAVER_EXE=C:\Program Files\DBeaver\dbeaver.exe"
if not exist "%DBEAVER_EXE%" (
  rem Try typical 64-bit community path variant
  set "DBEAVER_EXE=C:\Program Files\DBeaver Community\dbeaver.exe"
)

echo.
if exist "%DBEAVER_EXE%" (
  echo [3/3] Opening DBeaver with apply.sql
  start "" "%DBEAVER_EXE%" "%APPLY_SQL%"
  echo     In DBeaver: connect to %DB_NAME% on %DB_HOST%:%DB_PORT%, review the transaction in apply.sql
  echo     Then COMMIT or ROLLBACK as appropriate, and re-run audit.sql.
) else (
  echo [INFO] DBeaver not found at the default location.
  echo        Please open DBeaver manually and load:
  echo        %APPLY_SQL%
)

echo.
echo Next recommended step:
echo   - In DBeaver, run apply.sql inside a transaction (it's already set up).
echo   - If you COMMIT, run audit.sql again and compare with "%REPORT_DIR%\audit-%TS%-pre.tsv".
echo.

:end
popd
exit /b

