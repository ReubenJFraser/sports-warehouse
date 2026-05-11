# Path & Environment Contract — Sports Warehouse

## 1. Purpose of This README

This README defines the **authoritative contract** governing how paths, URLs, and environment-related concerns are handled across the Sports Warehouse codebase.

Its purpose is to eliminate ambiguity between:

- filesystem paths vs browser URLs  
- localhost vs virtual host execution  
- development vs production environments  

This document exists to **prevent silent failures**, partial rendering, and environment-specific bugs by locking a single, auditable set of rules.

This is a **governance document**, not a tutorial.

---

## 2. Scope

This contract applies to:

- all PHP files (frontend and admin)
- all JavaScript files
- all asset loading (CSS, JS, images, video)
- all AJAX / fetch endpoints

This document does **not** define styling, business logic, or database schema.  
It defines **path correctness only**.

---

## 3. Core Principle (Non-Negotiable)

**Filesystem paths and browser URLs are different domains and must never be mixed.**

Every path used in the project must belong to exactly one of these domains, and the domain must be explicit at the point of use.

---

## 4. Domain Definitions

### 4.1 Filesystem Domain (PHP Only)

Used for:

- `require` / `require_once` / `include`
- loading PHP configuration
- reading files on disk
- checking file existence

Rules:

- Always use `__DIR__` (or `dirname(__DIR__)`)
- Never use URLs
- Never use `BASE_URL`
- Never assume the document root

Canonical example:

`require_once __DIR__ . '/../inc/db.php';`

This rule applies identically on:

- Laragon
- Cloudways
- CLI execution
- cron jobs

Filesystem correctness must never depend on how the site is accessed in a browser.

---

### 4.2 Browser URL Domain (HTML / JavaScript Only)

Used for:

- `<script src>`
- `<link rel="stylesheet">`
- `<img src>`
- `fetch()`
- `<a href>`

Rules:

- Must resolve relative to the browser-visible project root
- Must never reference filesystem paths
- Must be environment-agnostic

Canonical examples:

`<script src="<?= BASE_URL ?>/js/admin/hero.js"></script>`

`fetch(\`\${window.BASE_URL}/admin/hero-candidates.php?item_id=79\`)`

These paths are evaluated by the browser, not by PHP.

---

## 5. PROJECT_ROOT Contract (Filesystem Anchor)

### 5.1 Definition

`PROJECT_ROOT` represents the **absolute filesystem root** of the Sports Warehouse project.

It is defined once during admin bootstrap as:

`PROJECT_ROOT = dirname(__DIR__)`

This value:

- is filesystem-only
- is never exposed to the browser
- is used for file existence checks and disk reads

### 5.2 Invariants

- `PROJECT_ROOT` must never be recomputed elsewhere
- All filesystem resolution must derive from it
- No relative assumptions are permitted outside this anchor

---

## 6. BASE_URL Contract (Browser Anchor)

### 6.1 What BASE_URL Is

`BASE_URL` represents the **browser-visible root** of the Sports Warehouse project.

It exists to support:

- subfolder installs (e.g. `/sports-warehouse-home-page`)
- virtual hosts (e.g. `https://sports-warehouse-home-page.test`)

`BASE_URL` is **not** an environment detector.  
It is a **path prefix only**.

---

### 6.2 Where BASE_URL Is Defined

`BASE_URL` is defined exactly once during admin layout bootstrap:

`admin/_layout.php`

Authoritative definition (local vhost):

`define('BASE_URL', '/sports-warehouse-home-page');`

This explicit definition is intentional and contract-bound.

---

### 6.3 JavaScript Access to BASE_URL

PHP exports `BASE_URL` to JavaScript explicitly:

`window.BASE_URL = "<?= BASE_URL ?>";`

JavaScript must never guess paths.  
It must use this value or derive URLs relative to it.

---

## 7. Admin Image Rendering Contract

Admin-side code must **never** hardcode `/images/...` URLs.

All admin image handling must go through the approved helpers:

- `admin_normalize_image_url()`
- `admin_image_exists()`
- `admin_render_thumbnail()`
- `admin_render_thumbnail_safe()`

Responsibilities:

- URL normalization always prepends `BASE_URL`
- Filesystem existence checks always derive from `PROJECT_ROOT`
- Rendering logic must not assume environment shape

Direct `<img src="/images/...">` usage in admin code is a contract violation.

---

## 8. Documented Failure Mode (Why This Contract Exists)

A prior violation of this contract produced the following observable failure:

- Admin pages rendered `<img src="/images/...">`
- Images existed on disk under the project directory
- Under a virtual host, the browser resolved `/images/...` against the wrong root
- Result: consistent 404 errors for all admin images

The failure manifested only after switching from localhost subfolder access to a virtual host, masking the defect during earlier testing.

The defect was resolved by:

- enforcing `BASE_URL` usage for all browser-visible paths
- routing all admin image output through the helper layer
- anchoring filesystem checks to `PROJECT_ROOT`

This failure mode is now **explicitly documented** to prevent recurrence.

---

## 9. sw_url() and sw_base() (Frontend Utility Boundary)

The helper functions in `inc/url.php` exist for **frontend routing only**.

Rules:

- Use `sw_url()` when generating frontend links dynamically
- Do not use `sw_base()` inside admin JavaScript
- Do not mix `sw_url()` with `BASE_URL`

Admin and frontend path concerns are deliberately separated.

---

## 10. Common Failure Modes and Their Meaning

### 10.1 404 Returning HTML Instead of JSON

Meaning:

- incorrect browser URL
- missing or ignored `BASE_URL`
- fetch path evaluated against the wrong root

This is **never** a JSON parsing problem.

---

### 10.2 Images Exist on Disk but Not in Browser

Meaning:

- filesystem paths leaked into HTML
- missing `BASE_URL` prefix
- incorrect environment assumptions

Filesystem existence does not imply browser reachability.

---

### 10.3 Works on One Machine but Not Another

Meaning:

- path logic violated this contract
- environment coincidence masked the error

This is a correctness failure, not an environment quirk.

---

## 11. Enforcement Rules

The following are prohibited everywhere:

- `require('http://...')`
- `require(BASE_URL . ...)`
- `fetch('/admin/...')` without `BASE_URL` awareness
- embedding filesystem paths in HTML or JavaScript
- hardcoded `/images/...` in admin rendering

Violations must be corrected at the source, not patched around.

---

## 12. Audit Strategy (Locked)

Audits must search for **causes**, not symptoms.

Primary targets:

- hardcoded `/admin/...` fetch calls
- PHP includes without `__DIR__`
- image `src` values not passing through helpers
- duplicated environment or root detection logic

Once this contract is enforced, localhost and virtual hosts function identically without conditional code.

---

## 13. Invariants

- PHP resolves files from disk
- Browsers resolve URLs
- These domains never overlap
- `PROJECT_ROOT` anchors filesystem resolution
- `BASE_URL` anchors browser resolution
- Environment differences must not affect path correctness

Breaking any invariant is a defect.

---

## 14. Status

This contract is **active and binding**.

Any future code or refactor must conform to it explicitly.  
Silent deviation is not permitted.




