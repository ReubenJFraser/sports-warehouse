# Post-Audit Inspection — Ingestion Surface Map (Read-Only)

**Status:** Inspection-only

**Purpose**
Enumerate all known ingestion and mutation entry points that plausibly fall under the *Excel → DB Ingestion Authority Contract*. No behavior is changed. No enforcement is applied. This document records *where authority would apply* if and when enforcement is approved.

---

## Inclusion Criteria

A file is listed if it:

* Reads Excel / CSV or other external data sources
* Maps external values into canonical identity fields
* Writes identity-bearing columns or hero-related fields

---

## Ingestion Surface Register

| File                             | Category         | Reads External Data | Writes Identity Fields | Under Ingestion Contract | Notes                                             |
| -------------------------------- | ---------------- | ------------------: | ---------------------: | -----------------------: | ------------------------------------------------- |
| `tools/import_*.php`             | Importer         |                 Yes |                    Yes |                      Yes | Family placeholder — enumerate concrete filenames |
| `tools/update-hero-images.php`   | Maintenance      |           Yes (CSV) |           Yes (hero_*) |                      Yes | Guarded by HeroAuthority (maintenance)            |
| `scripts/rebuild-image-meta.php` | Maintenance      |    Yes (filesystem) |       Yes (image_meta) |                  Partial | Writes metadata, not catalog identity             |
| `admin/bulk-*.php`               | Admin bulk tools |            Possibly |               Possibly |                  Pending | Requires enumeration                              |
| `admin/hero-manager.php`         | Admin UI         |                  No |           Yes (hero_*) |                  No (UI) | Writes via explicit editorial authority           |
| `admin/hero-edit.php`            | Admin UI         |                  No |    Yes (hero_override) |                  No (UI) | Manual editorial path                             |
| `tools/importers/*.php`          | Importers        |                 Yes |                    Yes |                      Yes | Folder placeholder — enumerate                    |
| `legacy/*.php`                   | Legacy           |             Unknown |                Unknown |                  Pending | Sunset candidates                                 |

---

## Out of Scope (Explicit)

* Frontend rendering (`/inc/*`, `/js/*`) — read-only consumers
* Runtime selection logic — none
* Heuristic tuning — excluded

---

## Open Enumeration Tasks (Inspection Only)

* List concrete filenames under `tools/import_*.php`
* List concrete filenames under `tools/importers/`
* Identify any admin bulk tools that write identity fields
* Identify any cron or CI-triggered scripts

---

## Lock Statement

This document is **append-only** during inspection. No enforcement, refactors, or schema changes are implied. Any future enforcement must explicitly reference this register and select rows by filename.

---

## Inspection Freeze Statement

At the conclusion of this ingestion surface mapping, **no enforcement actions are authorized or implied**.

Specifically:

- No ingestion guards are applied
- No importer hard-fail or soft-fail rules are enforced
- No canonical validation is performed at write time
- No authority rejection logic is introduced
- No existing ingestion behavior is modified

This document exists solely to make **potential authority boundaries visible**, not to activate them.

The absence of enforcement is **intentional**, reflecting that:

- ingestion authority has not yet been formally declared
- enforcement criteria have not been approved
- architectural impact has not been adjudicated

Any future ingestion enforcement requires:

- a separate policy or inspection document declaring ingestion authority
- an explicit enforcement phase recorded in the Post-Audit Enforcement Log
- filename-scoped selection from the Ingestion Surface Register above

Until such a phase is declared, this document remains a **read-only reference map** and is considered **closed for inspection purposes**.

---

## Phase 6A Closure — Admin Visibility Surfaces

**Phase:** 6A  
**Scope:** Admin diagnostic visibility (read-only)  
**Status:** Closed

Phase 6A extended the post-audit inspection model into the admin system by
introducing **explicit, read-only diagnostic surfaces** for system and catalog state.

This phase includes:

- Admin Dashboard system status reporting
- Environment visibility (host, PHP version, DB connectivity)
- Hero coverage, override presence, and legacy hero detection
- Extraction of shared diagnostic logic into `/admin/inc/`
  - `environment-status.php`
  - `hero-status.php`

### Explicit guarantees

Phase 6A introduced **no enforcement, no mutation, and no automation**.

Specifically:

- No database writes were added
- No heuristics were introduced
- No authority rules were activated
- No existing ingestion or admin behavior was modified

All new logic is:
- read-only
- deterministic
- callable from multiple admin surfaces
- guarded by `ADMIN_CONTEXT`

### Relationship to Ingestion Inspection

Phase 6A does **not** alter the Ingestion Surface Register above.

It operates **downstream** of ingestion and exists solely to:
- make system state visible
- support human inspection
- prepare the ground for future enforcement phases

### Closure statement

Phase 6A is hereby declared **complete and closed**.

No further Phase 6A changes are authorized.

Any future admin changes must declare a new phase
(e.g. Phase 7 — Enforcement or Phase 7 — Tooling),
and must not retroactively modify Phase 6A behavior or intent.


