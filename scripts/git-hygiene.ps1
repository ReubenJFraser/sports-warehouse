<# 
.SYNOPSIS
  Repo hygiene checks with color output to validate .gitignore behavior.

.USAGE
  .\scripts\git-hygiene.ps1
  .\scripts\git-hygiene.ps1 -Full

.DESCRIPTION
  Runs a compact set of checks:
   1) git status --ignored (clutter scan)
   2) tracked .gitkeep files
   3) tracked .sql files (expect only db/sportswh_seed.sql)
   4) verify no vendor/node_modules tracked
   5) concise working tree status
   6) (optional) full tracked file list
   7) summary counts
#>

[CmdletBinding()]
param(
  [switch]$Full
)

function Write-Section($title) {
  Write-Host ""
  Write-Host ("=" * 70) -ForegroundColor DarkGray
  Write-Host " $title" -ForegroundColor Cyan
  Write-Host ("=" * 70) -ForegroundColor DarkGray
}

function Require-Git {
  if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    Write-Host "[ERROR] Git is not on PATH. Install Git or open a Git-enabled shell." -ForegroundColor Red
    exit 1
  }
}

function Require-Repo {
  git rev-parse --is-inside-work-tree 2>$null | Out-Null
  if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] This folder is not a Git repository." -ForegroundColor Red
    Write-Host "        Open a terminal in your repo root and re-run: scripts\git-hygiene.ps1" -ForegroundColor Yellow
    exit 1
  }
}

# --- Preamble checks ---
Require-Git
Require-Repo

# --- Header ---
Write-Section "REPO HYGIENE CHECK"
$branch = git rev-parse --abbrev-ref HEAD
$last = git log -1 --pretty=format:"%h - %s (%cr) by %an"
Write-Host ("Path   : {0}" -f (Get-Location)) -ForegroundColor Gray
Write-Host ("Branch : {0}" -f $branch) -ForegroundColor Gray
Write-Host ("Last   : {0}" -f $last) -ForegroundColor Gray

# 1) git status --ignored
Write-Section "[1/6] Clutter scan (git status --ignored)"
git status --ignored --short

# 2) Tracked .gitkeep
Write-Section "[2/6] Tracked .gitkeep files"
$keeps = git ls-files | Select-String -Pattern '\.gitkeep$'
if ($keeps) { $keeps.ToString() } else { Write-Host " (none)" -ForegroundColor DarkGray }

# 3) Tracked .sql (expect only db/sportswh_seed.sql)
Write-Section "[3/6] Tracked .sql files (expect ONLY db/sportswh_seed.sql)"
$sqls = git ls-files | Select-String -Pattern '\.sql$'
if ($sqls) { $sqls.ToString() } else { Write-Host " (none)" -ForegroundColor DarkGray }

# 4) Any vendor/node_modules tracked? (should be none)
Write-Section "[4/6] Tracked vendor / node_modules (expect NONE)"
$deps = git ls-files | Select-String -SimpleMatch "\vendor\" , "\node_modules\"
if ($deps) { 
  Write-Host "[WARN] Some dependency folders are tracked:" -ForegroundColor Yellow
  $deps.ToString()
} else { 
  Write-Host " (none)" -ForegroundColor DarkGray 
}

# 5) Working tree cleanliness
Write-Section "[5/6] Working tree status"
git status --short

# 6) Optional: full tracked file list
if ($Full) {
  Write-Section "[6/6] Full tracked file list"
  git ls-files | Out-Host
} else {
  Write-Host ""
  Write-Host "[6/6] Skipped full file list. Use -Full to show it." -ForegroundColor DarkGray
}

# 7) Summary counts
Write-Section "Summary"
$trackedCount = (git ls-files | Measure-Object).Count
$keepCount    = (git ls-files | Select-String -Pattern '\.gitkeep$' | Measure-Object).Count
$sqlCount     = (git ls-files | Select-String -Pattern '\.sql$' | Measure-Object).Count

Write-Host ("Tracked files total : {0}" -f $trackedCount) -ForegroundColor Green
Write-Host ("Tracked .gitkeep    : {0}" -f $keepCount) -ForegroundColor Green
Write-Host ("Tracked .sql        : {0}   (expect 1: db/sportswh_seed.sql)" -f $sqlCount) -ForegroundColor Green

Write-Host ""
Write-Host "Done." -ForegroundColor Cyan

