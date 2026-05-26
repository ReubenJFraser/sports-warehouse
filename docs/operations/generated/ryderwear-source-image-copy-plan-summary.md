# Ryderwear Source Image Copy Plan Summary

- Total worksheet rows read: 62
- Ready to copy rows: 25
- Exception rows: 37
- Excluded already-grandfathered rows: 23
- Excluded no-source rows: 0
- Excluded duplicate destination collision rows: 14
- Duplicate destination file paths detected: 39
- Total source files planned for copying after exclusions: 131
- Confirmation: No copy was executed by Codex. This task generated planning artifacts and a local-only script.

## Recommended local commands

Dry-run:

```powershell
pwsh -File scripts/copy_ryderwear_source_images_from_plan.ps1
```

Execution:

```powershell
pwsh -File scripts/copy_ryderwear_source_images_from_plan.ps1 -Execute
```
