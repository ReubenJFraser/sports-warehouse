# Sports Warehouse — PowerShell Helper Functions

This document records the PowerShell helper functions used for daily development and deployment of the **Sports Warehouse** project.

These helpers are intentionally **not part of the Git repository**. They are defined in the user’s PowerShell profile so that they are automatically available in every PowerShell session.

This file exists to prevent loss of operational knowledge.

---

## Location of the PowerShell Profile File

The helper functions live in the PowerShell profile file referenced by `$PROFILE`.

On this system, the profile file is located at:

C:\Users\rjfra\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1

This path is visible in the PowerShell / Notepad title bar when opening the profile using:

notepad $PROFILE

The location is not inside the Sports Warehouse project directory and is not tracked by Git.

---

## Purpose of Using the PowerShell Profile

PowerShell automatically loads the profile file on startup.

Defining functions here ensures:

- Commands are available globally
- No manual sourcing is required
- Project navigation is instant
- Deployment steps are repeatable
- SSH access is standardized

This is the correct and intended place for these helpers.

---

## Sports Warehouse Helper Functions (Authoritative Copy)

The following functions are defined verbatim in the PowerShell profile file.

### 1. sports-warehouse

Moves directly to the Sports Warehouse project root.

Purpose:
- Eliminate manual directory navigation
- Ensure the correct working directory before any operation

Definition:

function sports-warehouse {
    Set-Location "C:\laragon\www\sports-warehouse-home-page"
}

Usage:
Run this at the start of any PowerShell session.

---

### 2. sports-warehouse-git

Moves to the project root and shows Git status.

Purpose:
- Confirm repository state
- Verify working directory
- Quickly check for uncommitted changes

Definition:

function sports-warehouse-git {
    Set-Location "C:\laragon\www\sports-warehouse-home-page"
    git status
}

Usage:
Run before committing or deploying.

---

### 3. sports-warehouse-deploy

Performs a full local Git deployment.

Purpose:
- Stage all changes
- Commit with a standard message
- Push to the remote repository

Definition:

function sports-warehouse-deploy {
    Set-Location "C:\laragon\www\sports-warehouse-home-page"
    git add .
    git commit -m "Auto deploy"
    git push
}

Notes:
- This assumes intentional changes
- No interactive confirmation is performed
- Use only when ready to deploy

---

### 4. cloudways-ssh

Opens an SSH session to the production Cloudways server.

Purpose:
- Server inspection
- Log review
- Emergency fixes
- Manual verification

Definition:

function cloudways-ssh {
    ssh master_cvnneryvqb@170.64.146.120
}

---

## Server Details (Operational Reference)

Cloud Server:
- Provider: Cloudways
- Server Name: UdnjrmZ46XRJ
- IP Address: 170.64.146.120
- SSH User: master_cvnneryvqb

---

## Database Details (Operational Reference)

Production Database:
- Database Name: ZBatq37epY

These values are recorded here purely for personal operational continuity.

---

## Important Notes

- This file documents helpers; it does not install them
- Changes to helper behavior must be made in `$PROFILE`
- If PowerShell is reinstalled or the user profile changes, this file must be recreated
- This document should be updated if helper functions are added, removed, or renamed

---

## Status

This document is authoritative for the current development environment.

No automation, security hardening, or abstraction is applied by design.

