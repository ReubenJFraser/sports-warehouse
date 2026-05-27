# Inactive Products Image Remediation — Next Step Summary

- Adidas rows reviewed: 4
- Ryderwear duplicate-collision rows reviewed: 12
- suspicious/remap rows deferred: 3 (1 suspicious mapping exclusion + 2 source-folder recovery)

## Recommended action counts

### Adidas worksheet
- needs_new_image_source: 4

### Ryderwear worksheet
- needs_manual_review: 12

## Discovery and ownership signals

- Adidas candidate project images found: yes.
- Ryderwear duplicate collisions with clear recommended ownership: no.

## Safest next implementation sequence

1. Keep all 12 Ryderwear collision rows in manual adjudication until canonical folder ownership is approved per model_id semantics.
2. Resolve each collision group at destination-folder level, then regenerate copy plan for only approved owners.
3. Run Adidas-specific source discovery separately from Ryderwear workflows and map only verifiable Adidas paths.
4. After worksheet sign-off, prepare follow-on non-destructive plans before any DB/ProductDB updates.

## Safety confirmation

- No MySQL changes were made.
- No ProductDB rows were modified.
- No code files were modified.
- No image files were copied, moved, renamed, or deleted.
