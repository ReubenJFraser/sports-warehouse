@echo off
setlocal

set "DBEAVER_EXE=C:\Program Files\DBeaver\dbeaver.exe"
if not exist "%DBEAVER_EXE%" (
  set "DBEAVER_EXE=C:\Program Files\DBeaver Community\dbeaver.exe"
)

set "HERE=%~dp0"
pushd "%HERE%"
set "APPLY_SQL=%cd%\apply.sql"

if exist "%DBEAVER_EXE%" (
  start "" "%DBEAVER_EXE%" "%APPLY_SQL%"
) else (
  echo [INFO] DBeaver not found at default locations.
  echo        Please open DBeaver manually and load:
  echo        %APPLY_SQL%
)

popd
exit /b

