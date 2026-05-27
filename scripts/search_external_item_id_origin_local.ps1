[CmdletBinding()]
param(
    [string[]]$Roots = @('C:\laragon\www\sports-warehouse-home-page'),
    [switch]$IncludeDocx
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# Targeted local evidence discovery only:
# - Do not use broad cloud roots.
# - Use explicit operator-approved roots.
# - Keep scope narrow to avoid OneDrive Files On-Demand downloads.

Write-Warning 'Broad OneDrive root searches can trigger OneDrive Files On-Demand cloud downloads. Use only specific approved folders.'

$blockedRoots = @(
    'C:\Users\rjfra\OneDrive',
    'C:\Users\rjfra\OneDrive - TAFE NSW',
    'C:\Users\rjfra',
    'C:\'
)

function Normalize-PathString {
    param([Parameter(Mandatory = $true)][string]$PathText)
    $trimmed = $PathText.Trim()
    if ($trimmed.EndsWith('\\')) {
        return $trimmed.TrimEnd('\\')
    }
    return $trimmed
}

$normalizedBlocked = $blockedRoots | ForEach-Object { Normalize-PathString -PathText $_ }
$normalizedRoots = $Roots | ForEach-Object { Normalize-PathString -PathText $_ }

foreach ($root in $normalizedRoots) {
    if ($normalizedBlocked -contains $root) {
        throw "Refusing to search broad root '$root'. Please provide a narrower approved folder."
    }
}

if ($IncludeDocx) {
    Write-Warning '-IncludeDocx is reserved for future extension and is currently not implemented to avoid slow/cloud-heavy scans.'
}

$searchTerms = @(
    'external_item_id',
    'external item id',
    'external id',
    'model_id',
    'db_itemId',
    'itemId',
    'source id',
    'import id',
    'staging',
    'supplier'
)

$allowedExtensions = @('.md', '.txt', '.sql', '.csv', '.php', '.json', '.xml', '.html', '.log')

$repoRoot = Split-Path -Parent $PSScriptRoot
$outputDir = Join-Path $repoRoot 'docs/operations/generated'
$outputCsv = Join-Path $outputDir 'external-item-id-local-origin-search-results.csv'

if (-not (Test-Path -LiteralPath $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

$results = New-Object System.Collections.Generic.List[object]

foreach ($root in $normalizedRoots) {
    if (-not (Test-Path -LiteralPath $root)) {
        Write-Warning "Skipping missing root: $root"
        continue
    }

    Write-Host "Searching root: $root"

    $files = Get-ChildItem -LiteralPath $root -Recurse -File -ErrorAction SilentlyContinue |
        Where-Object { $allowedExtensions -contains $_.Extension.ToLowerInvariant() }

    foreach ($term in $searchTerms) {
        $matches = $files | Select-String -SimpleMatch -Pattern $term -ErrorAction SilentlyContinue
        foreach ($match in $matches) {
            $results.Add([pscustomobject]@{
                Path          = $match.Path
                LastWriteTime = $match.Path | ForEach-Object { (Get-Item -LiteralPath $_).LastWriteTime }
                Length        = $match.Path | ForEach-Object { (Get-Item -LiteralPath $_).Length }
                SearchTerm    = $term
                LineNumber    = $match.LineNumber
                Line          = $match.Line.Trim()
            }) | Out-Null
        }
    }
}

$results |
    Sort-Object Path, LineNumber, SearchTerm |
    Export-Csv -Path $outputCsv -NoTypeInformation -Encoding UTF8

Write-Host "Wrote results to: $outputCsv"
