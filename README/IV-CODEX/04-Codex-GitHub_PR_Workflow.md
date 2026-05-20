# Codex + GitHub PR Workflow Discipline

## Purpose

This README defines the authoritative workflow for using Codex with GitHub Pull Requests in the Sports Warehouse repository.

Its purpose is to prevent repeated workflow instability caused by confusion between:

- Codex task state
- GitHub Pull Request state
- local Windows / Laragon runtime state

This document exists so that Codex-assisted development can proceed with **guardrailed implementation velocity**.

That means the project should not retreat into endless planning, excessive caution, or trivial micro-changes. Codex should be used to implement meaningful, bounded slices of work.

At the same time, Codex must not be allowed to create branch confusion, invisible PR state, stale task lineage, uncontrolled file changes, or unclear merge status.

The goal is disciplined execution, not defensive paralysis.

---

## Scope

This README covers:

- Codex task workflow
- GitHub PR creation and review
- manual PR recovery workflow
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
- detailed feature design
- UI design standards outside the Codex workflow

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

This means Codex should be used to produce meaningful implementation slices when the next step is clear.

This does not mean Codex should be given broad authority.

It means Codex should be given bounded authority.

---

## Core Balance

There are two workflow errors to avoid.

### Error 1 - Reckless Implementation

Do not prompt Codex with broad instructions such as:

- continue improving the project
- fix whatever needs fixing
- refactor the Hero Manager
- improve the whole admin area
- clean up related files as needed

This creates scope drift and review instability.

---

### Error 2 - Excessive Conservatism

Do not reduce Codex to trivial changes such as:

- change one selector
- rename one class
- write only a plan when the implementation is clear
- split one coherent UI feature into unnecessarily tiny PRs

This creates process drag and prevents useful progress.

---

### Correct Balance

The correct operating mode is:

**one coherent bounded implementation slice per Codex task**

A bounded slice may touch multiple files when those files belong to the same objective.

Examples of acceptable bounded slices:

- PHP markup plus CSS styling plus JavaScript behaviour for one UI feature
- endpoint consumption plus read-only display logic for one admin panel
- route correction plus browser-verifiable link behaviour
- documentation update plus one companion pointer update

Examples of unacceptable broad slices:

- redesign the full admin system
- refactor all hero tooling
- change endpoint payloads and UI layout together without approval
- alter schema, routing, and UI in one task
- clean up unrelated files while completing the requested feature

---

## Core Operating Model

Codex must be treated as:

**a single-task PR generator**

Codex must not be treated as:

**a continuous long-lived development environment**

The correct operating model is:

1. one fresh Codex task
2. one bounded implementation objective
3. one GitHub Pull Request
4. one review and merge decision
5. one local synchronization cycle
6. one local runtime verification cycle
7. then stop

The next implementation stage begins as a new Codex task.

---

## State Authority Model

Workflow confusion usually occurs when the wrong environment is treated as authoritative.

Use this authority model.

### Codex Task State

Codex task state is not authoritative.

Codex may have:

- its own branch state
- its own Git checkout
- its own task history
- its own execution logs
- its own temporary sandbox state

Codex task state can become stale.

It can diverge from GitHub.

It can diverge from local Windows.

A Codex summary is not proof that a GitHub PR exists.

---

### GitHub Repository State

GitHub PR state is authoritative for merge decisions.

GitHub determines:

- real PR existence
- PR number
- files changed
- branch mergeability
- conflict status
- canonical `main` after merge

A PR exists only when GitHub shows a real PR number.

Internal Codex metadata is not enough.

---

### Local Windows / Laragon State

Local Windows / Laragon state is authoritative for runtime verification.

The local environment determines whether the merged change works in the actual project.

The local site does not update automatically after a GitHub merge.

Local synchronization is mandatory.

---

## Codex Environment Requirements

The Codex environment for this repository should use:

- Agent internet access: On
- Domain allowlist: Common dependencies
- Allowed HTTP methods: All methods

If Codex reports:

`CONNECT tunnel failed, response 403`

then the environment is not correctly available for GitHub network operations.

That is an environment configuration problem.

It must be fixed through Codex environment settings, not by repeated prompting.

---

## Standard Codex Workflow

### Step 1 - Prepare Local Main

Before starting a new Codex task, local `main` should be current and clean.

Run:

- `sports-warehouse`
- `git checkout main`
- `git pull origin main`
- `git fetch --prune`
- `git status --short`
- `git log --oneline -8`

Expected result:

- local branch is `main`
- local branch is up to date with `origin/main`
- working tree is clean
- recent log matches the expected merged state

If local state is not clean, stop and resolve it before starting Codex work.

---

### Step 2 - Start a Fresh Codex Task

Begin from the Codex task home screen.

Do not start new implementation work inside:

- an already merged task
- an archived task
- a stale PR task
- a task whose PR number is unclear
- a prior task's request-change box
- a GitHub comment thread unless the workflow explicitly requires it

Each implementation stage should start as a fresh Codex task unless the task is receiving one narrow correction before merge.

---

### Step 3 - Give Codex One Bounded Objective

The prompt must define:

- stage name
- objective
- expected changed files
- allowed files
- forbidden files
- non-goals
- tests or checks to run
- completion requirement
- workflow expectation

Codex should not be asked to "continue everything" or "fix the whole project."

A task should produce one coherent reviewable outcome.

---

### Step 4 - Review Codex Output

When Codex finishes, review:

- summary
- changed files
- tests run
- implementation notes
- whether the scope remained bounded

Do not create or merge a PR without reviewing the changed file list.

If unexpected files changed, stop and inspect before proceeding.

---

### Step 5 - Create PR Through Codex UI

If Codex shows `Create PR`, use the Codex PR workflow.

If a dropdown offers `Create draft PR`, use it unless a non-draft PR is explicitly required.

Wait until Codex provides a visible `View PR` action.

Click `View PR`.

The PR is real only when GitHub opens a real PR number.

Internal summaries such as the following are not sufficient:

- `draft PR metadata`
- `make_pr metadata`
- `PR metadata prepared`
- internal completion summaries
- sandbox commit references

Do not treat the task as complete until GitHub shows a real PR.

---

### Step 6 - Review PR in GitHub

In GitHub, open `Files changed`.

Confirm:

- changed files match expected scope
- no unrelated files changed
- no endpoint contract changed unless approved
- no schema changed unless approved
- no enforcement behaviour changed unless approved
- no forbidden payload expansion occurred
- no architecture invariant was violated
- no routing invariant was violated
- no manual authority was weakened

If the PR is a draft, mark it ready for review only after the changed files are acceptable.

---

### Step 7 - Merge Only When Stable

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

### Step 8 - Pull Merged Work Locally

After merge, update local `main`.

Run:

- `sports-warehouse`
- `git checkout main`
- `git pull origin main`
- `git fetch --prune`
- `git status --short`
- `git log --oneline -8`

Expected result:

- local `main` includes the merge commit
- working tree is clean

---

### Step 9 - Verify Runtime Behaviour

For UI, PHP, CSS, JavaScript, route, or endpoint work:

- open the relevant local URL
- perform hard browser refresh when CSS or JavaScript changed
- inspect visible behaviour
- test links or buttons affected by the change
- check browser console if JavaScript changed
- directly test endpoint URLs where relevant

Local runtime verification is required before the stage is considered accepted.

Codex completion is not final acceptance.

GitHub merge is not final acceptance.

Local runtime verification is final acceptance.

---

### Step 10 - End the Task

After merge and local verification, the Codex task is finished.

Do not continue unrelated work in the same task.

Do not reuse the same PR branch for the next stage.

Start a fresh Codex task for the next bounded objective.

---

## Manual PR Workflow

Manual PR workflow is valid when Codex PR creation fails or when local recovery is deliberately chosen.

In manual workflow:

1. create or use a local branch
2. make the changes locally
3. run local checks
4. commit locally
5. push the branch
6. create the PR manually in GitHub
7. merge the PR manually in GitHub
8. pull `main` locally
9. delete the local and remote feature branch if appropriate

When manual workflow is used, Codex `Create PR` and `View PR` buttons are not involved.

Do not return to Codex to create a duplicate PR after a manual PR has already been created or merged.

Manual GitHub PR state is authoritative once the manual PR exists.

---

## Codex PR Buttons Versus Manual PRs

Use this distinction to avoid confusion.

### Use Codex `Create PR` / `View PR`

Use these buttons when:

- Codex performed the implementation
- Codex still controls the task branch
- no manual PR has already been created
- the Codex UI is being used as the PR creation path

The expected sequence is:

1. Codex completes implementation
2. Codex shows `Create PR`
3. operator clicks `Create PR`
4. Codex shows `View PR`
5. operator clicks `View PR`
6. GitHub opens a real PR number

---

### Do Not Use Codex PR Buttons

Do not use Codex `Create PR` / `View PR` when:

- the work was recovered locally
- the branch was pushed manually
- the PR was created manually in GitHub
- the PR has already been merged
- the branch has already been deleted
- the Codex task state is stale or ambiguous

In this case, the correct next step is local synchronization, not returning to Codex.

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

If a correction produces any of the following, stop and use recovery procedures:

- unexpected new PR number
- stale branch state
- unclear task lineage
- merge conflict that cannot be clearly resolved
- changed files outside scope
- Codex/GitHub state mismatch

Do not nurse a confused Codex task indefinitely.

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
- Codex produces only internal PR metadata
- patch application fails against current `main`
- browser verification contradicts syntax checks

When a stop condition occurs, do not keep prompting the same task.

Use a recovery procedure.

Confusion is a stop signal.

---

## Recovery Procedure A - Fresh Restart

Use this when the implementation is reproducible.

Procedure:

1. close or abandon the confused PR
2. return to local `main`
3. run `git checkout main`
4. run `git pull origin main`
5. run `git status --short`
6. start a fresh Codex task
7. reimplement the bounded objective in one clean PR

This is the preferred recovery path for small and medium changes.

---

## Recovery Procedure B - Local-First Recovery

Use this when the Codex implementation is valuable but the PR workflow is unstable.

Procedure:

1. synchronize local `main`
2. fetch remote branches
3. check out or recreate the affected branch locally
4. resolve conflicts in the local editor
5. run appropriate checks
6. commit locally
7. push a clean branch
8. create or update the GitHub PR manually
9. merge manually if the PR is correct
10. pull merged `main` locally

This path restores direct local control.

It should be used when Codex task state has become unreliable.

---

## Recovery Procedure C - Patch Recovery

Use this only when the patch is small enough to verify safely.

Request one of:

- Copy patch
- Copy git apply
- complete changed file contents

Apply the result locally.

Then use the local-first workflow to commit, push, and create a PR.

Patch recovery is risky when:

- the file has moved on
- the patch touches nested PHP/HTML
- the patch spans multiple unrelated sections
- indentation or DOM nesting matters
- the patch changes CSS, PHP, and JavaScript together

If `git apply --check` fails, do not keep forcing the patch.

Use local-first recovery or fresh restart.

---

## Recovery Procedure D - Full File or Section Replacement

Use this when patch recovery is too fragile.

Preferred order:

1. complete file replacement for small or medium files
2. complete top-level section replacement for large files
3. narrow fragment replacement only when indentation and context are safe

Avoid nested fragments when indentation or DOM structure matters.

For PHP/HTML files, syntax success does not prove DOM correctness.

Browser verification remains mandatory.

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

## Branch Deletion Policy

After a PR is merged and closed, GitHub may report that the branch can be safely deleted.

If the PR is merged and no further work is needed on that branch:

- delete the remote branch in GitHub
- delete the local branch if present
- run `git fetch --prune`

Typical local cleanup:

- `git checkout main`
- `git pull origin main`
- `git branch -d BRANCH_NAME`
- `git fetch --prune`
- `git status --short`

Only delete branches after confirming the PR was merged.

Do not delete active branches tied to open PRs.

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
- browser-test generated HTML where layout or interaction is affected

If `php` is unavailable in the environment, the absence of `php -l` must be reported.

Important limitation:

`php -l` confirms PHP syntax only.

It does not confirm:

- correct HTML nesting
- correct DOM structure
- correct CSS layout
- correct browser behaviour

---

## CSS Change Policy

If Codex changes CSS:

- check changed selectors for scope
- avoid broad global overrides unless approved
- preserve component ownership where possible
- verify responsive behaviour in the browser
- hard refresh before judging the result
- inspect whether older rules are being overridden intentionally or accidentally

CSS changes are not accepted until visual behaviour is checked locally.

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

## Runtime Verification Policy

Runtime verification must match the kind of change made.

Examples:

- PHP page change: load the page locally
- endpoint change: open or fetch the endpoint directly
- route change: click the actual link
- JavaScript change: test the interaction and inspect console
- CSS change: hard refresh and inspect layout
- admin action change: test the actual button or form

A successful syntax check is necessary but not sufficient.

A successful GitHub merge is necessary but not sufficient.

The stage is accepted only after local runtime behaviour is verified.

---

## Guardrailed Implementation Velocity Policy

Codex should deliver meaningful bounded implementation slices when the risk is understood.

A bounded slice may include related changes across multiple files when they are part of one coherent objective.

Examples:

- PHP markup plus CSS styling plus JavaScript behaviour for one UI feature
- endpoint consumption plus display logic for one read-only feature
- a route fix plus browser-verifiable link correction
- a documentation update plus one companion pointer update

Codex should not create unnecessary planning-only stages when the next implementation step is clear and bounded.

Codex should also not use implementation velocity to justify:

- broad refactors
- architecture drift
- routing drift
- schema changes
- hidden behavioural changes
- unrelated cleanup
- expanding endpoint payload use beyond the approved mode

Velocity means disciplined execution.

It does not mean uncontrolled scope.

---

## Required Codex Prompt Footer

Future Codex prompts should include the following completion requirement in substance:

Workflow completion requirement:

Do not stop at draft PR metadata, internal `make_pr` summaries, sandbox commits, or task summaries.

Task completion requires either:

- a visible Codex `View PR` button opening a real GitHub PR number
- or a complete recoverable patch, git apply output, or full changed file content

If the visible Codex UI shows `Create PR`, use the Codex UI PR workflow after completing the task.

If PR creation fails, report that clearly and stop.

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
- CSS or layout behaviour was browser-tested if CSS changed
- URL behaviour was browser-tested if routes changed
- no database or schema change occurred unless approved
- no enforcement behaviour changed unless approved
- no manual authority was weakened
- endpoint payload boundaries were respected
- local `main` will be pulled after merge
- local runtime verification will be performed

---

## Human-in-the-Loop Operator Sequence

Between Codex completion and local acceptance, the operator often performs mechanical integration steps across:

- ChatGPT task instructions
- Codex task execution
- GitHub PR review and merge
- local PowerShell synchronization
- Laragon runtime verification
- DBeaver inspection where needed
- local generated report output review

This manual coordination is expected workflow behaviour.

It is not a workflow failure.

The disciplined requirement is that each handoff point stays explicit, observable, and reversible.

---

## Required Local Post-Merge Checklist

After merging any PR, decide the local sequence by working tree state:

- if the working tree is clean, run the normal local post-merge sequence
- if tracked generated reports are modified and disposable/regenerable, restore them before pulling
- if local changes are intentional, commit or stash deliberately before pulling

### Normal Local Post-Merge Sequence

Run:

- `sports-warehouse`
- `git checkout main`
- `git pull origin main`
- `git fetch --prune`
- `git status --short`
- `git log --oneline -8`

### Tracked Generated Report Variation

Files under `docs/operations/generated/` may be tracked by Git.

If a local PHP report generator rewrites tracked generated files, `git status --short` can show modified entries such as:

- `M docs/operations/generated/2026-05-20-live-schema-verification-report.md`
- `M docs/operations/generated/2026-05-18-db_itemid-model_id-readiness-audit.md`

A later `git pull origin main` can be blocked when the merged PR also changed one of those tracked files.

Typical error:

- `Your local changes to the following files would be overwritten by merge`

When those local report outputs are disposable, regenerable, and not intended for commit, use this safe recovery sequence:

- `git restore -- docs\operations\generated\2026-05-20-live-schema-verification-report.md`
- `git restore -- docs\operations\generated\2026-05-18-db_itemid-model_id-readiness-audit.md`
- `git pull origin main`
- `git fetch --prune`
- `git status --short`
- `git log --oneline -8`

Use this restore flow only when the local generated output is not intended to be committed.

If the generated report output is intended to become a committed artifact, do not restore blindly; review it and commit it deliberately.

Untracked generated files shown with `??` usually do not block `git pull`, for example:

- `?? docs/operations/generated/image-sync-reconciliation-report.csv`
- `?? docs/operations/generated/image-sync-reconciliation-summary.md`

Do not add untracked generated outputs unless they are intentionally part of the stage scope.

Then run checks relevant to the changed files.

Examples:

- `php -l path/to/file.php`
- `node --check path/to/file.js`
- `git diff --check`

Then browser-test the relevant local page or endpoint.

---

## Stage Completion Definition

A stage is complete only when all of the following are true:

- a real GitHub PR was merged
- local `main` has been pulled
- local working tree is clean
- relevant syntax checks have passed
- relevant browser or endpoint tests have passed
- changed files stayed within approved scope
- branch cleanup has been performed if appropriate

A Codex task summary alone does not complete a stage.

A pushed branch alone does not complete a stage.

A merged PR alone does not complete a stage.

---

## Known Gaps and Open Questions

The Codex interface may evolve.

Open questions include:

- whether Codex PR update behaviour will become more reliable
- whether future tasks will expose clearer branch state
- whether GitHub comments will reliably trigger Codex revisions
- whether Codex task search and archive behaviour will become more predictable
- whether environment state will remain stable across browser sessions
- whether Codex will consistently distinguish internal PR metadata from real GitHub PR state

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
- reduce all work to micro-changes
- prevent meaningful implementation progress

The goal is clean, reviewable implementation history.

---

## Guiding Principles

The workflow principles are:

- current `main` is the restart point
- one task produces one PR
- one PR produces one merge decision
- GitHub mergeability is authoritative
- local runtime verification is mandatory
- stale branches should not be nursed indefinitely
- recovery should be decisive
- implementation slices should be meaningful
- broad scope is not velocity
- excessive caution is not discipline
- confusion is a stop signal

If the workflow becomes tangled, return to:

- current `main`
- fresh Codex task
- bounded objective
- real PR
- local pull
- local verification

