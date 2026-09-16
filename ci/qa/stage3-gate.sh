#!/usr/bin/env bash
# Stage 3 gate: decide whether a PR earns a first round of AI testing.
#
#   ci/qa/stage3-gate.sh <pr-number>
#
# On success, writes the resolved context to stdout as JSON and exits 0:
#   {"pr":1240,"sha":"…","issues":[{"issue":1234,"marker":{…},"milestone":"…"}],
#    "passed_over":[{"issue":1240,"reason":"…"}],
#    "issue":1234,"marker":{…},"milestone":"…"}
#
# `issues` is every linked issue that qualifies, in ascending issue number, and the
# round executes all of them. The trailing scalar issue/marker/milestone repeat the
# FIRST entry and exist only so anything still reading the old single-issue shape
# keeps working.
#
# Exit codes:
#   0   go
#   3   skip, quietly — expected and common, not a failure
#   4   stop, loudly — misconfiguration or a malformed marker
#
# This runs on every labeled / check_suite / pull_request_review event, so the
# skip path must stay cheap and silent. A workflow that comments "not ready yet"
# on every unrelated PR event becomes noise nobody reads, and then nobody reads
# the real reports either.

set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/gh-client.sh"
. "${here}/marker.sh"

# Existing repo labels — see stage1-publish.sh for why nothing new is created.
#
# QA_PR_TRIGGER_LABEL, not QA_TRIGGER_LABEL: stage 1 already owns the latter for
# the issue-side label ("QA: Test case needed"), and both scripts run with the
# same environment. Sharing the variable made this gate look for the issue's
# label on a pull request, which never matches — a pipeline that skips every PR
# and says nothing about why.
TRIGGER_LABEL="${QA_PR_TRIGGER_LABEL:-Requires Functionality Review}"

# The opt-out, honoured on the PR and on its linked issue: the cases come from the
# issue, so an issue a person has taken over must not be executed through a PR that
# happens to be clean.
MANUAL_LABEL="${QA_MANUAL_LABEL:-Manual}"
# QA Lead approval is the authoritative gate. Matching is EXACT, not a prefix
# match, so a near-miss label ("QA: Test case Approved (v2)", say) does NOT
# satisfy this on its own — pipe-separate APPROVED_LABELS to widen it deliberately
# if this repo ever grows a second approval label.
APPROVED_LABELS="${QA_APPROVED_LABELS:-QA: Test case Approved}"
# Code review can be signalled two ways and either satisfies the gate:
#   - a standing GitHub review approval (the default, always present), or
#   - a "code approved" LABEL, for teams that sign off with labels.
# Either, not both: requiring a label that may not exist in the repo would make
# the whole pipeline inert in a way that looks exactly like a broken webhook.
CODE_APPROVED_LABEL="${QA_CODE_APPROVED_LABEL:-Code Approved}"

SKIP=3
STOP=4

pr="${1:?usage: stage3-gate.sh <pr-number>}"

# Normally the gate short-circuits on the first unmet condition and says nothing
# else. That is right for the twenty no-op builds an hour this job wakes for — but
# it is wrong when someone is deliberately trying to get a round to run, because
# "not eligible" gives no hint which of six conditions bit, and each guess costs a
# build. QA_GATE_EXPLAIN=1 evaluates them all and prints a checklist.
EXPLAIN="${QA_GATE_EXPLAIN:-0}"
# Bypass for condition 4 only. Deliberately loud: it prints on every run that uses
# it, and executeForPr puts it on the PR report, because a round that did not
# confirm CI is green is a weaker result than one that did — a case can pass against
# a build whose unit tests are failing, and nobody reading "First round passed"
# would assume that unless told.
SKIP_CHECKS="${QA_GATE_SKIP_CHECKS:-0}"
gate_fails=0

pass() { [[ "$EXPLAIN" == "1" ]] && printf 'gate: [ OK ] %s\n' "$1" >&2; return 0; }

skip() {
  if [[ "$EXPLAIN" == "1" ]]; then
    gate_fails=$(( gate_fails + 1 ))
    printf 'gate: [FAIL] %s\n' "$1" >&2
    return 0
  fi
  printf 'gate: skip — %s\n' "$1" >&2
  exit $SKIP
}
stop() { printf 'gate: stop — %s\n' "$1" >&2; exit $STOP; }


gh_require_env || stop "GitHub env incomplete"

# 0. the opt-out, checked before anything else. A PR someone has taken over
#    manually gets nothing from this pipeline — no round, no comment, no label — and
#    the check costs one call and stays silent, which is what an opt-out should do.
if gh_has_label "$pr" "$MANUAL_LABEL"; then
  skip "PR #${pr} is labelled '${MANUAL_LABEL}' — a person is handling it"
else
  pass "PR #${pr} is not labelled '${MANUAL_LABEL}'"
fi

# 1. the human trigger
if gh_has_label "$pr" "$TRIGGER_LABEL"; then
  pass "PR #${pr} is labelled '${TRIGGER_LABEL}'"
else
  skip "PR #${pr} is not labelled '${TRIGGER_LABEL}'"
fi

# 2. already reported for this exact commit? Keyed on the head SHA so that a
#    developer pushing a fix gets a fresh run, which a label guard would suppress.
#    A round can now cover several issues and can stop part-way through them, so
#    "already reported" is per issue, not per commit: pr_marker_issues lists the
#    ones a previous round actually finished for THIS sha, and they are filtered
#    out below. Only when nothing is left does the gate skip. A v1 marker carries
#    no issue list — it means the whole round was reported, so it still
#    short-circuits here exactly as before.
head_sha=$(gh_pr "$pr" | jq -r '.head.sha')
mapfile -t issues_done < <(pr_marker_issues "$pr" "$head_sha")
if pr_marker_exists "$pr" "$head_sha" && (( ${#issues_done[@]} == 0 )); then
  skip "PR #${pr} already has a first-round report for ${head_sha:0:8}"
elif (( ${#issues_done[@]} )); then
  pass "first round for ${head_sha:0:8} covered issue(s) ${issues_done[*]} — looking for any not yet run"
else
  pass "no first-round report yet for ${head_sha:0:8}"
fi

# 3. code review — checked before checks because it is one cheap call
if gh_has_label "$pr" "$CODE_APPROVED_LABEL"; then
  printf 'gate: PR #%s carries "%s"\n' "$pr" "$CODE_APPROVED_LABEL" >&2
  pass "code review: '${CODE_APPROVED_LABEL}' label present"
elif gh_pr_is_approved "$pr"; then
  printf 'gate: PR #%s has a standing review approval\n' "$pr" >&2
  pass "code review: standing review approval"
else
  skip "PR #${pr} is neither labelled '${CODE_APPROVED_LABEL}' nor approved by a reviewer (or has outstanding changes requested)"
fi

# 4. CI green
if [[ "$SKIP_CHECKS" == "1" ]]; then
  printf 'gate: [SKIP] PR #%s check status NOT verified — QA_SKIP_CHECKS is on\n' "$pr" >&2
elif gh_pr_checks_green "$pr"; then
  pass "PR #${pr} checks are green"
else
  skip "PR #${pr} checks are not all green"
fi

# 5. a linked issue. Per the pipeline rules: no link means ignore, not fail.
mapfile -t issues < <(gh_pr_linked_issues "$pr")
if (( ${#issues[@]} )); then
  pass "PR #${pr} links issue(s): ${issues[*]}"
else
  skip "PR #${pr} has no linked issue (add 'Fixes #<n>' to the body)"
fi

# EVERY linked issue that carries approved cases is executed, in GitHub's own order
# from closingIssuesReferences — ascending issue NUMBER, not the order they appear in
# the PR body — capped at 100. This used to take the first one and stop, which meant a
# PR closing three issues reported on one of them and passed over the other two.
#
# Every issue NOT selected is still recorded with the reason, and the reasons reach
# the PR: a round that covers four issues out of six is a fine outcome, but a reader
# has to be able to see which two were left and why.
passed_over='[]'
selected='[]'
# Issues whose marker could not be parsed. Kept separately from passed_over because
# the two need different endings: if nothing ran AND a marker was malformed, the gate
# must exit loudly rather than skip quietly, or a broken marker sits there being
# silently ignored on every event for the life of the commit.
malformed=()
note_passed_over() {
  passed_over=$(jq -c --argjson n "$1" --arg r "$2" '. + [{issue:$n, reason:$r}]' <<<"$passed_over")
}
note_selected() {
  selected=$(jq -c --argjson n "$1" --argjson m "$2" --arg ms "$3" \
    '. + [{issue:$n, marker:$m, milestone:$ms}]' <<<"$selected")
}
in_list() {  # in_list <needle> <haystack...>
  local needle="$1"; shift
  local item
  for item in "$@"; do [[ "$item" == "$needle" ]] && return 0; done
  return 1
}

for (( idx = 0; idx < ${#issues[@]}; idx++ )); do
  issue="${issues[idx]}"

  # The opt-out on a linked issue. This USED to abort the whole round on the
  # reasoning that if a person has taken over an issue this PR closes, the answer is
  # to run nothing rather than pick a different issue. That reasoning came from a
  # round that executed exactly one issue. Now that every qualifying issue runs,
  # excluding the opted-out one and testing the rest is what the label actually asks
  # for — and the old behaviour would let a single Manual issue silence a round over
  # thirty others. The PR label remains an unconditional stop; only the per-issue
  # opt-out narrowed.
  if gh_has_label "$issue" "$MANUAL_LABEL"; then
    printf 'gate: issue #%s is labelled '"'"'%s'"'"' — excluded, a person is handling it\n' \
      "$issue" "$MANUAL_LABEL" >&2
    note_passed_over "$issue" "labelled '${MANUAL_LABEL}' — a person is handling it"
    continue
  fi

  # Already finished for this exact commit by an earlier, partial round.
  if in_list "$issue" "${issues_done[@]+"${issues_done[@]}"}"; then
    printf 'gate: issue #%s already ran for %s — skipping it\n' "$issue" "${head_sha:0:8}" >&2
    note_passed_over "$issue" "already executed for ${head_sha:0:8}"
    continue
  fi

  set +e
  marker=$(marker_read "$issue")
  rc=$?
  set -e

  case $rc in
    0) ;;                                   # usable
    "$MARKER_NONE")
      note_passed_over "$issue" "no generated test cases"
      continue
      ;;
    *)
      # A malformed marker excludes ITS issue and nothing else. This used to stop
      # the whole gate, which was right when a round was one issue and is wrong now:
      # a PR linking dozens of issues, several already selected and validated, and a
      # single unparseable marker on one of them threw all of them away and exited 4.
      #
      # Not a softening of "never guess" — the malformed issue is still refused, and
      # loudly. It is only the blast radius that shrinks.
      printf 'gate: issue #%s has a MALFORMED marker — excluded; a person must fix it\n' "$issue" >&2
      note_passed_over "$issue" "malformed qa-touch-cases marker — a person must fix it"
      malformed+=("$issue")
      continue
      ;;
  esac

  approved=false
  while IFS= read -r label; do
    [[ -n "$label" ]] || continue
    if gh_has_label "$issue" "$label"; then approved=true; break; fi
  done < <(tr '|' '\n' <<<"$APPROVED_LABELS")

  # Not a gate failure — a reason this issue was passed over. It used to exit here,
  # which meant a PR linking an unapproved issue and an approved one ran nothing at
  # all: the loop never reached the issue that was ready. The gate only fails when NO
  # linked issue qualifies, which the skip at the bottom already covers.
  if ! $approved; then
    printf 'gate: issue #%s cases are not approved yet (need one of: %s) — trying the next linked issue\n' \
      "$issue" "${APPROVED_LABELS//|/, }" >&2
    note_passed_over "$issue" "cases not approved"
    continue
  fi
  pass "issue #${issue} cases are approved"

  # The milestone, from THIS issue — the one that carries the cases about to be run.
  # Not from the pull request: the release is decided on the issue, and a PR carries
  # at most a copy of that decision and frequently nothing at all.
  #
  # NOT a condition. It was one briefly, and stopping the round over it cost more
  # than it bought: the milestone decides which QA Touch release a run is filed
  # under, and runs are made in the UI (POST /testRun is closed — see
  # qatouch-facts.md), so a missing one changes nothing about what this round can
  # establish. Refusing to test a PR because a field on its issue is blank would
  # withhold the probes and every case result over a bookkeeping gap — and the QA
  # Touch milestone list can trail GitHub's by a release or two, so it would happen
  # on the first PR of every release.
  #
  # It travels in the context either way, empty when absent, and publishReport says
  # so on the PR. A note a person can act on beats a gate that stops the work.
  milestone=$(gh_issue_milestone "$issue")
  if [[ -n "$milestone" ]]; then
    pass "issue #${issue} is filed under milestone '${milestone}'"
  else
    printf 'gate: issue #%s has no milestone — the round runs, the report notes it\n' "$issue" >&2
  fi

  note_selected "$issue" "$marker" "$milestone"
done

# In explain mode an earlier condition may have failed and been recorded rather than
# exited on. Emitting a go context would tell the pipeline to run a round it is not
# entitled to.
if [[ "$EXPLAIN" == "1" ]] && (( gate_fails > 0 )); then
  printf 'gate: %d condition(s) unmet — nothing ran. Fix the [FAIL] lines above.\n' "$gate_fails" >&2
  exit $SKIP
fi

if [[ "$(jq -r 'length' <<<"$selected")" != "0" ]]; then
  jq -r '.[] | "gate: [ OK ] issue #\(.issue) will run"' <<<"$selected" >&2
  jq -cn --argjson pr "$pr" --arg sha "$head_sha" \
    --argjson sel "$selected" --argjson over "$passed_over" \
    '{pr:$pr, sha:$sha, issues:$sel, passed_over:$over,
      issue:$sel[0].issue, marker:$sel[0].marker, milestone:$sel[0].milestone}'
  exit 0
fi

if [[ "$EXPLAIN" == "1" ]]; then
  printf 'gate: every condition met but no linked issue was runnable:\n' >&2
  jq -r '.[] | "  issue #\(.issue): \(.reason)"' <<<"$passed_over" >&2
  (( ${#malformed[@]} )) && exit $STOP
  exit $SKIP
fi

jq -r '.[] | "gate: issue #\(.issue) passed over — \(.reason)"' <<<"$passed_over" >&2

# Nothing ran and at least one marker was broken: that is a misconfiguration someone
# has to fix, not the ordinary "this PR is not ready" case, so it exits loudly. When
# some issues DID run, the malformed one travels to the PR in passed_over instead —
# visible, but not at the cost of the issues that were ready.
if (( ${#malformed[@]} )); then
  stop "no linked issue was runnable, and issue(s) ${malformed[*]} have a malformed marker"
fi

skip "none of the linked issues (${issues[*]}) are runnable — see the reasons above"
