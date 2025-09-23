# export-code-with-structure.ps1
# Purpose: Create a clean ZIP of the project code (with folder structure preserved)
#          and generate a manifest CSV (path, bytes, mtime, sha256) + a file-list.txt
# Usage:   From anywhere, run: .\_tools\export-code-with-structure.ps1

$ErrorActionPreference = 'Stop'

# Determine project root as the parent of this script's folder (_tools\..)
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$root      = Split-Path -Parent $scriptDir   # project root
$stamp     = Get-Date -Format 'yyyy-MM-dd'
$exportDir = Join-Path $root '_exports'
New-Item -ItemType Directory -Force -Path $exportDir | Out-Null

$zipPath      = Join-Path $exportDir "SportsWarehouse_code-pack_$stamp.zip"
$manifestPath = Join-Path $exportDir "SportsWarehouse_code-manifest_$stamp.csv"
$fileListPath = Join-Path $exportDir "SportsWarehouse_file-list_$stamp.txt"

# What to include/exclude
$includeExt = @('php','css','js','sql','md','json','csv','htaccess')
$excludePattern = '(\\|/)(node_modules|vendor|\.git|\.history|images|videos|uploads|dist|build|cache|logs|storage|\.idea|\.vscode)(\\|/)'

# Collect files relative to the project root
Push-Location $root
try {
  $files = Get-ChildItem -Recurse -File |
    Where-Object {
      $_.FullName -notmatch $excludePattern -and
      $includeExt -contains $_.Extension.TrimStart('.')
    }

  if ($files.Count -eq 0) {
    Write-Error "No files matched the include/exclude rules. Nothing to export."
  }

  # Build relative paths (preserves folder structure in the zip)
  $relativePaths = $files | ForEach-Object { $_.FullName.Substring(($root + '\').Length) }

  # Recreate zip (overwrite if exists)
  if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
  Compress-Archive -Path $relativePaths -DestinationPath $zipPath -Force

  # Manifest CSV (path, bytes, mtime ISO, sha256)
  if (Test-Path $manifestPath) { Remove-Item $manifestPath -Force }
  $files | ForEach-Object {
    $rel = $_.FullName.Substring(($root + '\').Length)
    $hash = Get-FileHash $_.FullName -Algorithm SHA256
    [PSCustomObject]@{
      path   = $rel
      bytes  = $_.Length
      mtime  = $_.LastWriteTime.ToString('s') # ISO-ish (sortable)
      sha256 = $hash.Hash
    }
  } | Export-Csv $manifestPath -NoTypeInformation -Encoding UTF8

  # Simple text file list (one path per line)
  $relativePaths | Set-Content -Path $fileListPath -Encoding UTF8

  # Summary by extension (handy sanity check)
  $byExt = $files | Group-Object { $_.Extension.TrimStart('.').ToLower() } |
           Sort-Object Count -Descending |
           Select-Object Name, Count
  Write-Host "`nExport complete:"
  Write-Host "  ZIP:       $zipPath"
  Write-Host "  MANIFEST:  $manifestPath"
  Write-Host "  FILE LIST: $fileListPath"
  Write-Host "`nFile counts by extension:"
  $byExt | Format-Table -AutoSize
}
finally {
  Pop-Location
}

