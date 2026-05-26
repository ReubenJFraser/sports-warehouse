param(
    [string]$SourceRoot = 'C:\Users\rjfra\OneDrive\Hornsby\Assignments\Sport_Warehouse\images\Clothing\Ryderwear',
    [string]$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path,
    [string]$PlanPath = 'docs/operations/generated/ryderwear-source-image-copy-plan.csv',
    [switch]$Execute,
    [switch]$Overwrite
)

$resolvedPlanPath = if ([System.IO.Path]::IsPathRooted($PlanPath)) { $PlanPath } else { Join-Path $ProjectRoot $PlanPath }
$resultsPath = Join-Path $ProjectRoot 'docs/operations/generated/ryderwear-source-image-copy-results-local.csv'

if (-not (Test-Path -LiteralPath $resolvedPlanPath)) {
    throw "Plan CSV not found: $resolvedPlanPath"
}

$planRows = Import-Csv -LiteralPath $resolvedPlanPath
$results = New-Object System.Collections.Generic.List[object]

$mode = if ($Execute) { "EXECUTE" } else { "DRY-RUN" }
Write-Host "Mode: $mode"
Write-Host "SourceRoot: $SourceRoot"
Write-Host "ProjectRoot: $ProjectRoot"
Write-Host "PlanPath: $resolvedPlanPath"

foreach ($row in $planRows) {
    if ($row.copy_plan_status -ne 'ready_to_copy') { continue }

    $sourceFolder = Join-Path $SourceRoot $row.source_terminal_folder
    $destinationFolder = Join-Path $ProjectRoot $row.proposed_project_image_path
    $files = @()
    if ($row.source_image_files) {
        $files = $row.source_image_files -split '\|' | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne '' }
    }

    foreach ($fileName in $files) {
        $sourcePath = Join-Path $sourceFolder $fileName
        $destinationPath = Join-Path $destinationFolder $fileName
        $status = 'planned'
        $notes = ''

        if (-not (Test-Path -LiteralPath $sourcePath)) {
            $status = 'missing_source_file'
            $notes = 'Source file does not exist.'
        } elseif ((Test-Path -LiteralPath $destinationPath) -and -not $Overwrite) {
            $status = 'skipped_existing_destination'
            $notes = 'Destination exists and -Overwrite was not provided.'
        } elseif (-not $Execute) {
            $status = 'dry_run_would_copy'
            $notes = 'Dry-run mode only; no copy performed.'
            Write-Host "[DRY-RUN] COPY $sourcePath -> $destinationPath"
        } else {
            if (-not (Test-Path -LiteralPath $destinationFolder)) {
                New-Item -ItemType Directory -Path $destinationFolder -Force | Out-Null
            }
            Copy-Item -LiteralPath $sourcePath -Destination $destinationPath -Force:$Overwrite
            $status = 'copied'
            $notes = if ($Overwrite) { 'Copied with overwrite enabled.' } else { 'Copied.' }
            Write-Host "[COPIED] $sourcePath -> $destinationPath"
        }

        $results.Add([PSCustomObject]@{
            model_id = $row.model_id
            source_terminal_folder = $row.source_terminal_folder
            source_file = $fileName
            source_path = $sourcePath
            destination_path = $destinationPath
            status = $status
            notes = $notes
        }) | Out-Null
    }
}

$results | Export-Csv -NoTypeInformation -Encoding UTF8 -LiteralPath $resultsPath
Write-Host "Results written to: $resultsPath"
