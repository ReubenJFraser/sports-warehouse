# Codex + GitHub PR Workflow Discipline

## Purpose

This README defines the authoritative workflow for using Codex with GitHub Pull Requests in the Sports Warehouse repository.

Its purpose is to prevent repeated workflow instability caused by confusion between Codex task state, GitHub PR state, and the local Windows/Laragon development environment.

This document exists so that Codex-assisted development can proceed with guardrailed implementation velocity rather than becoming trapped in stale branches, internal task state, invisible PR metadata, merge conflicts, or local synchronization confusion.

The workflow described here is intended for Codex, ChatGPT, and human operators working with Codex-generated implementation tasks.

---

## Scope

This README covers:

- Codex task workflow
- GitHub PR creation and review
- draft PR handling
- local post-merge synchronization
- local runtime verification
- stop conditions
- recovery procedures
- conflict handling
- implementation velocity within workflow guardrails

This README does not cover:

- data authority
- schema authority
- architecture invariants
- routing invariants
- enforcement candidate governance
- detailed application feature design

Those responsibilities are governed separately by:

- `README/IV-CODEX/01-Codex-Behavioural_Rules.md`
- `README/IV-CODEX/02-Codex-Architecture_Invariants.md`
- `README/IV-CODEX/03-Codex-Routing_Invariants.md`
- `admin/ENFORCEMENT_CANDIDATE_REGISTER.md`

---

## Current Workflow Mode

The Sports Warehouse project has moved beyond its initial conservative stabilization phase.

The earlier conservative phase was necessary while:

- Codex workflow behaviour was being understood
- GitHub PR creation was unstable
- agent internet access was misconfigured
- branch state and task state were being confused
- architecture and routing rules were still being formalized
- the risk of uncontrolled AI changes was high

The active workflow mode is now:

**guardrailed implementation velocity**

This means Codex should be used to produce meaningful bounded implementation slices, not endless planning stages or excessively small micro-changes.

This does not reduce the need for review.

It changes the workflow expectation from defensive hesitation to disciplined execution.

---

## Core Operating Model

Codex must be treated as:

**a single-task PR generator**

Codex must not be treated as:

**a continuous long-lived development environment**

The correct operating model is:

- one fresh Codex task
- one bounded implementation objective
- one GitHub Pull Request
- one review and merge decision
- one local synchronization cycle
- one local runtime verification cycle
- then stop

The next implementation stage begins as a new Codex task.

---

## Conceptual Roles

### Codex Task Environment

The Codex task environment is a temporary cloud workspace.

It may have:

- its own branch state
- its own Git checkout
- its own task history
- its own execution logs
- its own temporary sandbox state

Codex task state is not authoritative.

It can become stale.

It can diverge from GitHub.

It can diverge from local Windows.

---

### GitHub Repository

GitHub is the authoritative shared repository.

GitHub determines:

- real PR existence
- PR number
- files changed
- branch mergeability
- conflict status
- canonical `main` after merge

A Codex task summary is not a substitute for a real GitHub PR.

GitHub PR state is authoritative for merge decisions.

---

### Local Windows / Laragon Environment

The local Windows environment is the runtime verification environment.

It determines whether the merged change works in the actual local project.

The local site does not update automatically after GitHub merge.

Local synchronization is mandatory.

---

## Codex Environment Requirements

The Codex environment for this repository should use:

- Agent internet access: On
- Domain allowlist: Common dependencies
- Allowed HTTP methods: All methods

If Codex reports `CONNECT tunnel failed, response 403`, the environment is not correctly available for GitHub network operations.

That is an environment configuration problem.

It must be fixed through Codex environment settings, not by repeated prompting.

---

## Standard Workflow

### Step 1 — Prepare Local Main

Before starting a new Codex task, local `main` should be current and clean.

Run:

- `sports-warehouse`
- `git checkout main`
- `git pull origin main`
- `git status`

Expected result:

- local branch is `main`
- local branch is up to date with `origin/main`
- working tree is clean

If local state is not clean, stop and resolve it before starting Codex work.

---

### Step 2 — Start a Fresh Codex Task

Begin from the Codex task home screen.

Do not start new implementation work inside:

- an already merged task
- an archived task
- a stale PR task
- a task whose PR number is unclear
- a prior task’s request-change box
- a GitHub comment thread unless the workflow explicitly requires it

Each implementation stage should start as a fresh Codex task unless the task is receiving one narrow correction before merge.

---

### Step 3 — Give Codex One Bounded Objective

The prompt must define:

- stage name
- objective
- likely changed files
- non-goals
- tests or checks to run
- completion requirement
- workflow expectation

Codex should not be asked to “continue everything” or “fix the whole project.”

A task should produce a coherent reviewable outcome.

---

### Step 4 — Review Codex Output

When Codex finishes, review:

- summary
- files changed
- tests run
- implementation notes
- whether the scope remained bounded

Do not create or merge a PR without reviewing the changed file list.

---

### Step 5 — Create PR Through Codex UI

If Codex shows `Create PR`, use the Codex PR workflow.

If a dropdown offers `Create draft PR`, use it unless a non-draft PR is explicitly required.

Wait until Codex provides a visible `View PR` action.

Click `View PR`.

The PR is real only when GitHub opens a real PR number.

Internal summaries such as `draft PR metadata`, `make_pr metadata`, or `PR metadata prepared` are not sufficient.

---

### Step 6 — Review PR in GitHub

In GitHub, open `Files changed`.

Confirm:

- changed files match expected scope
- no unrelated files changed
- no endpoint contract changed unless approved
- no schema changed unless approved
- no enforcement behaviour changed unless approved
- no forbidden payload expansion occurred
- no architecture or routing invariant was violated

If the PR is a draft, mark it ready for review only after the changed files are acceptable.

---

### Step 7 — Merge Only When Stable

Merge only when:

- the PR has a real GitHub number
- the PR is not draft
- files changed are correct
- GitHub reports no conflicts
- tests or checks are acceptable
- the implementation remains inside scope

If GitHub reports conflicts, do not merge.

GitHub mergeability is authoritative.

---

### Step 8 — Pull Merged Work Locally

After merge, update local `main`.

Run:

- `sports-warehouse`
- `git checkout main`
- `git pull origin main`
- `git status`
- `git log --oneline -5`

Expected result:

- local `main` includes the merge commit
- working tree is clean

---

### Step 9 — Verify Runtime Behaviour

For UI, PHP, CSS, JavaScript, route, or endpoint work:

- open the relevant local URL
- perform hard browser refresh when CSS or JavaScript changed
- inspect visible behaviour
- test links or buttons affected by the change
- check browser console if JavaScript changed
- directly test endpoint URLs where relevant

Local runtime verification is required before the stage is considered accepted.

---

### Step 10 — End the Task

After merge and local verification, the Codex task is finished.

Do not continue unrelated work in the same task.

Do not reuse the same PR branch for the next stage.

Start a fresh Codex task for the next bounded objective.

---

## Revision Policy

One narrow correction cycle is allowed before merge.

Acceptable correction examples:

- syntax fix
- URL normalization fix
- small UI repair
- missing local check
- misplaced JavaScript closure
- unsafe interpolation correction
- narrowly scoped bug fix inside the same objective

Repeated correction loops are not allowed.

If a correction produces a new PR number unexpectedly, stale branch state, or a conflict that cannot be clearly resolved, stop and use a recovery procedure.

---

## Stop Conditions

Stop using the current Codex task if any of these occur:

- GitHub reports merge conflicts
- Codex says working tree is clean but GitHub reports conflict
- PR number changes unexpectedly
- `Create new PR` appears when an existing PR should be updated
- `Update branch` is unavailable and the branch is conflicted
- Codex task state and GitHub PR state no longer match
- the branch cannot be found on GitHub
- the task disappears from the task list
- Codex claims success but GitHub has no PR
- the operator cannot confidently identify which task maps to which PR

When a stop condition occurs, do not keep prompting the same task.

Use a recovery procedure.

---

## Recovery Procedure A — Fresh Restart

Use this when the implementation is reproducible.

Procedure:

1. close or abandon the confused PR
2. return to local `main`
3. run `git checkout main`
4. run `git pull origin main`
5. run `git status`
6. start a fresh Codex task
7. reimplement the bounded objective in one clean PR

This is the preferred recovery path for small and medium changes.

---

## Recovery Procedure B — Local-First Recovery

Use this when the Codex implementation is valuable but the PR branch is stale or conflicted.

Procedure:

1. synchronize local `main`
2. fetch remote branches
3. check out or recreate the affected branch locally
4. resolve conflicts in the local editor
5. run appropriate checks
6. commit locally
7. push a clean branch
8. create or update the GitHub PR manually

This path restores direct local control.

It should be used when Codex task state has become unreliable.

---

## Recovery Procedure C — Patch Recovery

Use this when Codex cannot create or update a PR.

Request one of:

- Copy patch
- Copy git apply
- complete changed file contents

Apply the result locally.

Then use the local-first workflow to commit, push, and create a PR.

Do not accept an internal Codex commit on a sandbox branch as final.

---

## Conflict Policy

If GitHub reports branch conflicts:

- do not merge
- do not assume Codex can see the same conflict
- do not rely on Codex reporting clean working tree
- do not keep prompting indefinitely

GitHub conflict state is authoritative.

If the conflict is simple and `Update branch` is clearly available, it may be used.

If not, use fresh restart or local-first recovery.

---

## Draft PR Policy

Draft PRs cannot be merged.

When Codex creates a draft PR:

1. review changed files
2. confirm scope
3. mark ready for review
4. merge only after mergeability is confirmed

Do not search for the merge button while the PR remains draft.

---

## Local Cache Policy

After CSS or JavaScript changes, browser cache may show stale behaviour.

Use hard refresh.

Expected action:

- `Ctrl + F5`

If JavaScript behaviour still appears stale, check:

- browser console
- Network tab
- script path
- cache-busting conventions where applicable

---

## JavaScript Change Policy

If Codex changes JavaScript:

- require syntax validation where available
- prefer `node --check` for standalone JavaScript files
- inspect for misplaced braces or closures
- avoid unsafe direct `innerHTML` interpolation of endpoint values
- prefer `document.createElement`, `textContent`, and explicit attribute assignment
- test browser console after local pull

JavaScript changes are not accepted until local browser behaviour is confirmed.

---

## PHP Change Policy

If Codex changes PHP:

- run PHP syntax checks where available
- prefer `php -l` on changed PHP files
- verify the relevant local page or endpoint
- inspect visible output or JSON structure

If `php` is unavailable in the environment, the absence of `php -l` must be reported.

---

## URL and Route Change Policy

If Codex changes URLs, links, fetch paths, form actions, or route-related JavaScript:

- preserve the local base path
- preserve required `/admin/` segments
- avoid duplicated `/admin/admin/`
- avoid dropped admin segments
- browser-test the actual link or fetch URL

Known local base path:

`/sports-warehouse-home-page/`

Known admin path expectation:

`/sports-warehouse-home-page/admin/...`

---

## Endpoint Payload Boundary Policy

Endpoint consumers must remain within their intended payload mode.

For Hero Manager shortlist scan mode:

- product-list UI may consume `recommended_candidates`
- product-list UI must not consume `all_candidates`
- full candidate review belongs in challenge mode

Challenge mode route:

`admin/hero-candidates.php?item_id=ITEM_ID&include_shortlist=1`

Manual hero authority remains final.

Automation suggests.

Manual curation decides.

---

## Guardrailed Implementation Velocity Policy

Codex should now deliver meaningful bounded implementation slices when risk is understood.

A bounded slice may include related changes across multiple files when they are part of one coherent objective.

Examples:

- PHP markup plus CSS styling plus JavaScript behaviour for one UI feature
- endpoint consumption plus display logic for one read-only feature
- a route fix plus browser-verifiable link correction
- a documentation update plus one companion pointer update

Codex should not create unnecessary planning-only stages when the next implementation step is clear and bounded.

Codex should also not use implementation velocity to justify broad refactors, architecture drift, or unrelated cleanup.

---

## Required Codex Prompt Footer

Future Codex prompts should include the following completion requirement in substance:

Workflow completion requirement:

Do not stop at draft PR metadata or internal make_pr summaries.

Task completion requires either:

- a visible Codex `View PR` button opening a real GitHub PR number
- or a complete recoverable patch, git apply output, or full changed file content

If the visible Codex UI shows `Create PR`, use the Codex UI PR workflow after completing the task.

Do not claim completion without a real GitHub PR or recoverable content.

---

## Required PR Review Checklist

Before merging any Codex PR, confirm:

- real GitHub PR number exists
- files changed match intended scope
- no unrelated files changed
- PR is not draft
- PR has no conflicts
- tests reported by Codex are plausible
- JavaScript syntax check passed if JavaScript changed
- PHP syntax check passed if PHP changed and PHP was available
- URL behaviour was browser-tested if routes changed
- no database or schema change occurred unless approved
- no enforcement behaviour changed unless approved
- no manual authority was weakened
- local `main` will be pulled after merge
- local runtime verification will be performed

---

## Known Gaps and Open Questions

The Codex interface may evolve.

Open questions include:

- whether Codex PR update behaviour will become more reliable
- whether future tasks will expose clearer branch state
- whether GitHub comments will reliably trigger Codex revisions
- whether Codex task search and archive behaviour will become more predictable
- whether environment state will remain stable across browser sessions

These gaps do not change the workflow rule.

When state becomes unclear, stop and recover.

---

## Non-Goals

This workflow does not attempt to:

- make Codex a persistent IDE
- remove human PR review
- remove local testing
- hide Git branch reality
- eliminate all manual recovery
- prioritize automation over correctness
- preserve confused task state at all costs

The goal is clean, reviewable implementation history.

---

## Guiding Principles

The workflow principles are:

- current `main` is the restart point
- one task produces one PR
- GitHub mergeability is authoritative
- local runtime verification is mandatory
- stale branches should not be nursed indefinitely
- recovery should be decisive
- implementation slices should be meaningful
- confusion is a stop signal

If the workflow becomes tangled, return to:

- current `main`
- fresh Codex task
- bounded objective
- real PR
- local pull
- local verification

