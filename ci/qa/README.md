# AI first-round QA pipeline

Label-driven automation across the issue → PR lifecycle, for this app's
domain: invoices, orders, subscriptions/plans, coupons, the client/customer
panel, the admin panel, and payment-gateway plugins (Stripe, Razorpay, and
similar).
Claude authors test cases from an approved issue and later executes them
against a disposable instance as the first round of testing, with
deterministic security/API probes underneath the agent. QA Touch (project key
**`ekXp`**, "Agora Invoicing") is the store and review surface.

**The top-level pipeline definition is deliberately NOT in this repo.** It is
pasted directly into a Jenkins job's "Pipeline script" field (Jenkins' own
config, not SCM) — see whoever administers that job for its current copy.
Everything else — the helper scripts, prompts, and this file — lives here
under `ci/qa/`.

```
Issue created
  └─ label: "QA: Test case needed"     → Stage 1: Claude authors cases from
       │                                  the description, pushes to QA Touch
       └─ label: "QA Test Cases Added"  → cases posted on the issue + marker
            └─ reviewed in QA Touch → "QA: Test case Approved" (QA Lead)
                 └─ developer works, raises PR with "Fixes #<issue>"
                      └─ PR approved (review or "Code Approved") + checks green
                         + label: "Requires Functionality Review"
                           → Stage 3: gate → build a throwaway instance →
                             probes (security, API, Dusk) → execute the
                             approved cases → statuses in QA Touch, one report
                             on the PR, verdict label
```

Stage 3's verdict:

| Outcome | Label | When |
|---|---|---|
| pass | `QA: Round 1 Testing Approved` | no blocking probe failed, cases ran, none failed, none blocked |
| correction | `Functionality Correction` | a blocking probe failed, or a case failed |
| inconclusive | *none* | part of the round did not run — a pass would claim coverage that does not exist |

Set `QA_CI_SETS_VERDICT=false` in the job to make the round advisory instead.

## Files

| File | Role |
|---|---|
| `qatouch-facts.md` | **Read first.** Verified QA Touch API behaviour that contradicts the docs. |
| `marker.md` | The `<!-- qa-touch-cases -->` / `<!-- qa-first-round -->` contract between Stage 1 and Stage 3. |
| `qatouch-client.sh` | QA Touch client: auth, cases, modules, runs, results. |
| `gh-client.sh` | GitHub client: issues, labels, comments, PR readiness gates. |
| `fetch-issue.sh` | Hands the authoring agent the issue as a JSON file — no GitHub tool, no shell. |
| `fetch-attachments.sh` | Downloads the issue's images/PDFs/Google Docs so the agent can look at them. |
| `phase-summary.sh` | Turns a build's phase marks into a duration table, printed at the end of every Stage 3 build. |
| `code-map.sh` | Generates `routes.txt` / `permissions.txt` / `requests.txt` / `labels.txt` from the live tree so the agent looks things up instead of exploring. |
| `marker.sh` | Read/write/validate the marker. |
| `stage1-author-prompt.md` | The authoring brief Claude follows. |
| `stage1-publish.sh` | Validates authored JSON → QA Touch → issue comment → marker → labels. |
| `stage3-gate.sh` | Decides whether a PR earns a first round. Quiet skips by design. |
| `stage3-prompt.md` | Instructions the executor follows. |
| `mcp-ci.json` | Playwright MCP config for the agents (no Chrome extension in CI). |
| `discover.sh` | Lists issues/PRs needing attention — manual-sweep safety net. |
| `module-list.sh` | Fetches the QA Touch project's existing module names for the authoring agent. |
| `seed-qa-users.php` | Creates the admin and client logins on the per-build instance. |
| `label.sh` | Add/remove/check a GitHub label, resolved against the repo's real label names. |
| `limit-reset.sh` | Parses a Claude CLI stream for a usage/credit-limit reset time. |
| `pr-marker.sh` | Reads back a previous build's marker comment for a given commit sha (resume-on-rerun). |
| `dusk-changed.sh` | The Dusk test files this PR added or changed — the automatic trigger for `probes/dusk.sh`. |
| `probes/lib.sh` | Finding format, HTTP helpers, session login. |
| `probes/security.sh` | Runtime security: exposed files, debug output, the authorisation boundary, CSRF, cookies. |
| `probes/api.sh` | Session-authenticated JSON/AJAX endpoint contract: status codes, JSON-not-HTML errors, method handling. |
| `probes/dusk.sh` | Runs specific Dusk test files/directories and converts the report into findings. |
| `probes/summarize.sh` | Merges every probe into one markdown section and one verdict. |
| `instance-health.sh` | Serving? assets built? Refuses the round rather than reporting false failures. |
| `tests/*` | Offline tests for the shell scripts above — no network, no credentials, no Jenkins. Run with `bash ci/qa/tests/run.sh`. |

## What the round covers

**Deterministic probes** run first, in seconds, on every round — the generic
ground that a written test case is bad at describing: `.env`/`.git` exposure,
debug stack traces, the auth boundary (a customer session reaching
admin-only endpoints, an anonymous request reaching either), CSRF, cookie
flags, security headers, JSON-not-HTML error shapes.

**The project's own Dusk suite** runs alongside them: automatically for any
`tests/Browser/**/*Test.php` file the PR added or changed (blocking — an
author's own new browser test failing is not a judgement call), and
optionally via the `QA_DUSK_PATH` parameter for anything else (non-blocking
unless `QA_DUSK_BLOCKING=1`). There is no `phpunit.dusk.xml` suite-name config
in this repo, so both work by file/directory **path**, not by suite name.

**The agent** then executes the approved QA Touch cases — about *this
change*: the feature working, the endpoint answering, the role that should be
refused actually being refused.

Only unambiguous findings block the verdict: a served `.env`, a stack trace in
a response, an admin endpoint answering an anonymous or wrong-role request, a
panel that renders blank. Everything else is reported and left to a person.

Static analysis is **not** repeated here — Larastan/ESLint/SonarQube already
run in the main pipeline. What they cannot see is a running application, which
is the whole point of this round.

## The fixture cast

This app has no ticket-routing/department concept, so the disposable instance
is seeded with exactly two accounts:

| env prefix | role (`users.role`) | exists so cases can test |
|---|---|---|
| `QA_ADMIN_` | `admin` | plans, coupons, settings, payment-gateway config, anything admin-only |
| `QA_CLIENT_` | `user` | the client panel: their own invoices, orders, subscriptions, checkout |

`seed-qa-users.php` creates both from scratch on the per-build instance
(never reusing the seeded demo admin from `database/seeders/v2_0_0/
DatabaseSeeder.php` — its `demo@admin.com`/`password` is hardcoded in the
repo and shared by every environment, exactly what a disposable-instance round
should not depend on).

## Labels

**No new labels beyond this pipeline's own nine**, and none of them carry an
emoji shortcode. They do **not exist on GitHub yet** —
create them by hand (Settings → Labels) before wiring the webhook live:

| Label | Suggested color | Who sets it | When |
|---|---|---|---|
| `QA: Test case needed` | `#fbca04` (yellow) | human | trigger: author cases for this issue |
| `QA: Test case In Progress` | `#fbca04` (yellow) | CI | authoring running |
| `QA Test Cases Added` | `#0e8a16` (green) | CI | cases published on the issue |
| `QA: Test case Approved` | `#0e8a16` (green) | human (QA Lead) | cases ready for a developer |
| `Code Approved` | `#0e8a16` (green) | human | code signed off (a GitHub review approval also satisfies this) |
| `Requires Functionality Review` | `#fbca04` (yellow) | human | trigger: run the first round |
| `QA: Round 1 Testing In Progress` | `#fbca04` (yellow) | CI | execution running |
| `QA: Round 1 Testing Approved` | `#0e8a16` (green) | CI (unless `QA_CI_SETS_VERDICT=false`) | first round passed |
| `Functionality Correction` | `#d93f0b` (red) | CI (unless `QA_CI_SETS_VERDICT=false`) | first round found defects |

Plus the opt-out label:

| Label | Meaning |
|---|---|
| `Manual` | a person is handling this issue/PR — no cases authored, no round run, no comment, no label touched |
| `Need more info about issues by QA team` | *(optional, ported from `stage1-publish.sh`'s `QA_NEEDS_INFO_LABEL`)* applied to an issue when an attachment could not be read. `label.sh` refuses to create a label that doesn't exist, so until this one is created by hand it simply never gets applied — harmless, not a hard dependency. |

`ci/qa/label.sh` resolves a requested name against the repo's real labels
(exact match, then a tolerant fallback) so a near-miss is never silently
created as a second, near-duplicate label.

**Code sign-off is either/or.** A standing GitHub review approval satisfies
the gate, and so does the `Code Approved` label.

**Re-running is prevented by a head-SHA-keyed marker comment**, not a label,
so pushing a fix correctly earns a fresh run. The report itself is
**upserted** — one comment per PR, replaced on each run.

## Deliberately not included, and why

- **A plugin-activation step.** `testing-setup`'s seeders leave exactly four
  `plugins` rows: `Ccavenue` x2 at `status=0` and `Stripe`/`Razorpay` at
  `status=1`. `app/Plugins/` has no `Ccavenue` directory at all — those two
  rows are dead leftovers from a 2017 migration with no route or controller
  behind them, not a gateway a round could hit. The two real gateways already
  seed active. Nothing to flip.
- **A knowledge-base sync step** — no public knowledge-base/docs API to
  mirror in this application. The authoring agent works from the issue and
  the code map only (two sources, not three).
- **An account-lockout relaxer** — this app has no account-lockout mechanism
  on login (`RateLimiter::hit()` in `LoginController` records attempts but
  does not currently block). Nothing to relax.
- **A v3/token-API enabler** — no v3/token-API feature flag exists;
  `routes/api.php` is essentially empty and the app is session+CSRF
  authenticated throughout.
- **A license-activation step** — this app has no `CheckValidLicense`-style
  middleware. There is nothing to activate.
- **A TLS proxy** — this app does not force an `https://` scheme
  (no `URL::forceScheme('https')`), so the disposable instance serves plain
  `http://127.0.0.1:<port>` directly with no proxy or self-signed cert needed.
- **A scheduled-job whitelist wrapper** — rather than a script whitelisting
  which scheduled jobs are safe to run standalone, `stage3-prompt.md` tells
  the executor to run the real artisan command directly (`php artisan
  renewal:cron`, etc. — see `app/Console/Kernel.php`) when a case depends on
  one having run.
- **A committed-Jenkinsfile linter/local-runner** — those need a committed
  `Jenkinsfile.qa` to point at, and the pipeline script lives outside this
  repo (see the top of this file). `tests/jenkinsfile-parse.sh` and
  `tests/build-timeout.sh` still exist and work — pass the pipeline script's
  path as `$1` (or set `QA_PIPELINE_SCRIPT_PATH`) to point them at a local
  copy saved from the Jenkins UI; with neither given they explain this and
  skip cleanly.
- **A pre-existing-findings baseline file** — nothing to seed one with yet;
  create one here after the first few real rounds if a genuine pre-existing
  finding needs to stop blocking every PR.
- **A Dusk suite-name mapping** (`application`, `service-desk`, …) — no such
  config file exists in this repo. `dusk-changed.sh` / `probes/dusk.sh` / the
  `QA_DUSK_PATH` parameter all work by file or directory path instead.

## First run: dry run, no writes

Before labelling anything: run the Jenkins job with `QA_STAGE=author`,
`QA_NUMBER=<a real issue>`, `QA_DRY_RUN` checked. That authors the cases,
validates them, prints them in the log, and writes nothing to QA Touch or the
issue.

Then run the offline tests locally: `bash ci/qa/tests/run.sh`.

Jenkins job configuration, credentials, and node requirements are documented
alongside the pipeline script itself, not in this repo — ask whoever set up
the job.
