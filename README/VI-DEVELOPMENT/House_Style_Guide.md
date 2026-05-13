# Codex + GitHub PR Workflow Discipline

## Purpose

This README defines the authoritative operational workflow for using Codex with GitHub Pull Requests in the Sports Warehouse repository.

Its purpose is to prevent repeated workflow instability caused by confusion between Codex task state, GitHub PR state, and the local Windows/Laragon development environment.

This document exists to ensure that development effort is spent implementing bounded changes rather than recovering from stale branches, detached task state, ambiguous PR status, or broken synchronization between environments.

---

## Scope

This README covers:

- Codex operational workflow
- GitHub PR workflow discipline
- local post-merge synchronization
- PR review expectations
- recovery procedures when workflow state becomes unstable

This README does not cover:

- application architecture
- routing invariants
- business logic design
- database schema governance
- enforcement candidate policy

Those responsibilities remain governed by:

- `01-Codex-Behavioural_Rules.md`
- `02-Codex-Architecture_Invariants.md`
- `03-Codex-Routing_Invariants.md`
- `ENFORCEMENT_CANDIDATE_REGISTER.md`

---

## Conceptual Roles

### Codex Task Environment

Codex operates in a temporary cloud execution environment.

Its characteristics:

- independent Git state
- temporary task lifecycle
- isolated branch state
- possible divergence from GitHub
- possible divergence from local Windows state

Codex task state is not authoritative.

---

### GitHub Repository

GitHub is the authoritative shared repository.

Its responsibilities:

- branch comparison
- PR review
- mergeability determination
- conflict detection
- canonical `main` branch state

GitHub merge state is authoritative.

---

### Local Windows / Laragon Environment

The local Windows environment is the actual execution and verification environment.

Its responsibilities:

- PHP execution
- browser testing
- local Git synchronization
- cache-sensitive CSS/JS verification

Local state is authoritative for runtime verification.

---

## Guiding Principle

Codex is to be treated as:

**a single-task PR generator**

Codex is not to be treated as:

**a continuous long-lived development environment**

Each implementation stage should produce:

- one fresh Codex task
- one bounded implementation objective
- one GitHub PR
- one merge decision
- one local synchronization
- one local verification cycle

Then the task ends.

---

## Standard Workflow

## Step 1 — Prepare Local Main

Before starting any new Codex task:

Run:

`sports-warehouse`

Then:

`git checkout main`

Then:

`git pull origin main`

Then:

`git status`

Expected result:

`nothing to commit, working tree clean`

If local `main` is not clean:

stop.

Resolve local issues first.

---

## Step 2 — Confirm Codex Environment

Codex environment should be configured with:

- Agent internet access enabled
- Common dependencies domain allowlist
- All HTTP methods enabled

If Codex reports network failures such as:

`CONNECT tunnel failed, response 403`

stop.

Resolve Codex environment access before continuing.

Repeated prompting is not a valid network recovery strategy.

---

## Step 3 — Start Fresh Task

Begin from the Codex task home screen.

Create a new task.

Do not begin new implementation work inside:

- merged tasks
- archived tasks
- stale revision tasks
- unrelated prior PR tasks
- ambiguous recovery tasks

Each implementation stage begins as a fresh task.

---

## Step 4 — Define One Bounded Objective

Each Codex task must define:

- exact implementation objective
- intended stage name
- expected changed files
- explicit non-goals
- required verification steps
- PR completion requirement

Implementation scope must remain bounded.

Scope ambiguity creates workflow instability.

---

## Step 5 — Review Codex Output Before PR Creation

When Codex completes implementation:

review:

- summary
- changed files
- reported tests
- implementation scope

Confirm changes align with expectations.

Do not proceed blindly to PR creation.

---

## Step 6 — Create GitHub PR

If Codex presents:

`Create PR`

use the Codex PR creation workflow.

Completion is valid only when a real GitHub PR exists.

Internal metadata summaries such as:

- draft PR metadata
- make_pr metadata
- internal completion summaries

are not sufficient evidence of PR creation.

A real PR number must exist in GitHub.

---

## Step 7 — Review PR in GitHub

Review:

`Files changed`

Confirm:

- only intended files changed
- no unrelated files added
- no architecture drift
- no unintended schema changes
- no forbidden payload expansion
- no hidden behavioural regressions

If PR is draft:

mark it ready for review before merging.

---

## Step 8 — Merge Only if Stable

Merge only when:

- PR scope is correct
- branch is conflict-free
- implementation aligns with requested scope
- no unresolved ambiguity exists

If GitHub reports merge conflicts:

stop.

Do not merge.

---

## Step 9 — Synchronize Local Main

After merge:

Run:

`sports-warehouse`

Then:

`git checkout main`

Then:

`git pull origin main`

Then:

`git status`

Then:

`git log --oneline -5`

Expected:

local `main` matches GitHub `main`.

---

## Step 10 — Verify Runtime Behaviour

For frontend / JS / CSS work:

perform hard browser refresh.

Then verify runtime behaviour in the browser.

Where relevant:

- test UI behaviour
- test endpoint behaviour
- test navigation
- inspect browser console

Codex completion is not final acceptance.

Runtime verification is required.

---

## Step 11 — End Task

After successful merge and local verification:

the Codex task is complete.

Do not continue unrelated implementation work inside that task.

The next stage begins as a fresh task.

---

## Revision Policy

One narrow correction cycle is acceptable.

Examples:

- syntax correction
- narrow URL fix
- scoped UI repair
- targeted regression correction

Repeated revision loops are prohibited.

If branch state becomes ambiguous, stop and use recovery procedures.

---

## Stop Conditions

Immediately stop using the current Codex task if any of the following occur:

- GitHub reports merge conflicts
- Codex reports clean working tree but GitHub disagrees
- PR identity becomes unclear
- branch state becomes unclear
- Codex/GitHub synchronization becomes uncertain
- task lineage becomes ambiguous
- expected PR does not exist in GitHub

Workflow clarity is mandatory.

---

## Recovery Procedures

### Recovery Path A — Fresh Restart

Use when implementation is reproducible.

Procedure:

1. abandon confused PR
2. synchronize local `main`
3. begin fresh Codex task
4. reimplement bounded change

Preferred default recovery.

---

### Recovery Path B — Local Recovery

Use when implementation is valuable but PR workflow is unstable.

Procedure:

1. synchronize local repository
2. fetch remote branches
3. resolve conflicts locally
4. commit locally
5. push clean branch
6. create manual GitHub PR

This path restores full local control.

---

### Recovery Path C — Patch Recovery

Use when Codex cannot successfully create a PR.

Request:

- patch output
- git apply output
- complete changed file contents

Apply changes locally.

Then proceed manually.

---

## Known Gaps and Open Questions

Current uncertainties:

- Codex PR update behaviour may vary by environment state
- task recovery UX may differ between Codex sessions
- environment lifecycle behaviour may evolve over time

These gaps do not invalidate the workflow.

They reinforce the need for bounded implementation discipline.

---

## Non-Goals

This workflow does not attempt to:

- optimize Codex for continuous iterative development
- eliminate all manual review
- replace GitHub review discipline
- replace runtime verification
- abstract away Git branch reality

Its purpose is stability, not automation maximalism.

---

## Invariants

The following remain invariant:

- GitHub merge state is authoritative
- local runtime verification is mandatory
- Codex task state is temporary
- one task equals one bounded objective
- one objective equals one PR
- unstable workflow state requires explicit recovery

When workflow becomes confusing:

return to clean `main`, then restart cleanly.

