# external_item_id Historical-Origin Local Search Plan (Safe Scope)

## Why this plan exists
PR #198 improved repository-based analysis, but it still does **not** fully answer the true historical-origin question:

- The Git history available in this repository starts too late for the period when `external_item_id` appears to have been originally introduced.
- Current evidence in-repo reaches the May 2026 repository-history window, while the historical-origin question requires evidence from earlier local sources.

## Key constraint for Codex / ChatGPT workflows
Codex cannot directly inspect arbitrary older local folders on your machine unless you:

1. run a local search yourself in approved folders, and
2. commit the generated evidence file(s) to the repo **or** paste relevant excerpts back into chat.

Without those local outputs, historical-origin conclusions remain incomplete.

## Why broad OneDrive root searches are unsafe
Broad recursive searches over:

- `C:\Users\rjfra\OneDrive`
- `C:\Users\rjfra\OneDrive - TAFE NSW`

can trigger OneDrive Files On-Demand behavior (including Windows “Automatic file downloads”) when cloud-only files are touched. This creates unnecessary risk, noise, and download volume.

This workflow avoids that by:

- defaulting to repo-only scope,
- requiring explicit operator-approved roots for anything else, and
- hard-refusing known broad roots.

## Workflow summary
1. Start from the project root and run repo-only search first.
2. If needed, rerun with a **specific approved folder** (not a broad profile/cloud root).
3. Review `docs/operations/generated/external-item-id-local-origin-search-results.csv` locally.
4. Share only relevant lines/paths with Codex/ChatGPT or commit sanitized evidence.

## Safe command examples
From project root, repo-only search:

```powershell
.\scripts\search_external_item_id_origin_local.ps1
```

Search Laragon project folders only:

```powershell
.\scripts\search_external_item_id_origin_local.ps1 -Roots @("C:\laragon\www")
```

Search a specific TAFE/Sports Warehouse folder only:

```powershell
.\scripts\search_external_item_id_origin_local.ps1 -Roots @("C:\Users\rjfra\OneDrive - TAFE NSW\Cert_IV-Website_Design\Hornsby\Assignments\Sport_Warehouse")
```

Search a specific personal OneDrive assignment folder only, not all OneDrive:

```powershell
.\scripts\search_external_item_id_origin_local.ps1 -Roots @("C:\Users\rjfra\OneDrive\Hornsby\Assignments\Sport_Warehouse")
```

## Notes
- This is evidence discovery only; it does not modify MySQL, ProductDB data, runtime/admin/frontend/import code, or SQL generation.
- Historical-origin findings should remain provisional until pre-February 2026 evidence is located in approved local sources.
