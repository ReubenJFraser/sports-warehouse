📘 Chat Rules for Sports Warehouse

Canonical Deployment & Collaboration Contract

Purpose

This document defines the non-negotiable rules governing how ChatGPT and the user collaborate on the Sports Warehouse project.

Its purpose is to:

prevent production drift

enforce Git-first discipline

avoid repeated regressions when starting new chat windows

ensure local development and production hosting remain synchronized by design

This document is the authoritative contract.
Short trigger phrases in chat refer back to this document.

1. Source of Truth

GitHub repository is the single source of truth

All tracked files must originate from the local development environment

Production (Cloudways) must always be a clean checkout of GitHub

There is no “parallel” source of truth.

2. Local-First Editing Rule (Critical)

All real changes to tracked files must be made locally

Changes flow only in this direction:

Local (Laragon)
→ Git commit
→ GitHub
→ Cloudways deploy


Editing tracked files directly on Cloudways is forbidden by default

3. Production Is Read-Only by Default

Production (Cloudways) is treated as read-only.

Allowed exceptions (rare):

Temporary diagnostic probes

These must be:

explicitly labeled as probes

justified as impossible to perform locally

short-lived

reverted or incorporated back into local code immediately

If a change can be made locally, it must be made locally.

4. Explicitly Allowed Production-Only Files

Some files are designed not to sync and are intentionally excluded from Git:

Examples:

private_html/db.production.php (production secrets)

.env (local secrets)

logs

caches

uploads

generated runtime artifacts

These files:

must live outside Git

must be referenced by code that is in Git

are the only acceptable differences between local and production

Any other difference is considered process drift and must be investigated.

5. No Silent Divergence

If a discrepancy exists between:

local files

GitHub files

production files

Then one of the following must be true:

It is an intentional, documented exception, or

It is a bug in the deployment process

Unexplained divergence is never acceptable.

6. Mandatory Justification Rule (For ChatGPT)

If ChatGPT proposes:

editing production files

SSH-side changes

Cloudways-only edits

Then ChatGPT must first answer:

“Why can this not be done locally and synced via Git?”

If no convincing answer exists, the proposal is invalid.

7. Drift Recovery Protocol

If confusion arises:

Stop

Run git status on production

Restore production to Git state if needed

Re-establish local → Git → prod flow

Resume only after invariants are restored

8. New Chat Window Rule (Most Important)

At the start of any new chat related to this project, the user will write:

Chat Rules for Sports Warehouse

That phrase means:

This contract is in force

Git-first discipline applies

Production is immutable by default

Any violation must be called out immediately

The phrase is a binding activation trigger, not a suggestion.

9. Accountability

If ChatGPT violates these rules after the trigger phrase is present, that is a procedural error

The user is correct to stop the conversation and realign

Speed is never more important than correctness

10. Design Philosophy

This project prioritizes:

correctness over convenience

clarity over shortcuts

reproducibility over “quick fixes”

The goal is not merely to “make it work once”, but to establish a stable, professional workflow that survives:

long gaps

new chat windows

platform quirks

future expansion

