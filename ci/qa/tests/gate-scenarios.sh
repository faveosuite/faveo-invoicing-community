#!/usr/bin/env bash
# Scenario runner for stage3-gate.sh, offline: no GitHub token, no network.
#
# The gate sources gh-client.sh and marker.sh from its OWN directory, so the suite
# assembles a directory holding the real gate beside the stubs in stubs/ and runs
# that. Labels encode spaces as ~ — the label text itself matches this repo's real
# GitHub labels, which carry no emoji shortcodes.
#
#   ci/qa/tests/gate-scenarios.sh
TRIG='Requires~Functionality~Review'
CODE='Code~Approved'
APPR='QA:~Test~case~Approved'
pass=0; fail=0

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
QA_SRC="${QA_SRC:-$(cd "$HERE/.." && pwd)}"
STAGE_DIR="$(mktemp -d)"
trap 'rm -rf "$STAGE_DIR"' EXIT
cp "$QA_SRC"/stage3-gate.sh "$HERE"/stubs/gh-client.sh "$HERE"/stubs/marker.sh "$STAGE_DIR"/

run() { env "$@" bash "$STAGE_DIR"/stage3-gate.sh 42 2>/tmp/g.err; }

check() { # check <label> <expected-exit> <expected-issues-csv> <env...>
  local label="$1" wantrc="$2" wantissues="$3"; shift 3
  local out rc got
  out=$(run "$@"); rc=$?
  got=$(jq -r '[.issues[].issue]|join(",")' <<<"$out" 2>/dev/null || echo '')
  if [[ "$rc" == "$wantrc" && "$got" == "$wantissues" ]]; then
    printf 'PASS  %-44s rc=%s issues=[%s]\n' "$label" "$rc" "$got"; pass=$((pass+1))
  else
    printf 'FAIL  %-44s rc=%s (want %s) issues=[%s] (want [%s])\n' "$label" "$rc" "$wantrc" "$got" "$wantissues"
    sed 's/^/        /' /tmp/g.err | tail -6; fail=$((fail+1))
  fi
}

BASE=(T_GREEN=1 T_REVIEW=0)
LB="42:${TRIG}|${CODE}"

check "all three issues approved"        0 "11,22,33" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}"

check "one unapproved, one has no cases" 0 "33" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 33:${APPR}" T_NOMARKER="22"

check "Manual issue excluded, rest run"  0 "11,33" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 11:${APPR} 22:${APPR}|Manual 33:${APPR}"

check "issue already done for this sha"  0 "22,33" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}" T_DONE="11"

check "all done for this sha -> skip"    3 "" "${BASE[@]}" T_LINKED="11 22" \
  T_LABELS="$LB 11:${APPR} 22:${APPR}" T_DONE="11 22"

check "v1 marker still short-circuits"   3 "" "${BASE[@]}" T_LINKED="11" \
  T_LABELS="$LB 11:${APPR}" T_MARKER_SHA=1

check "nothing approved -> skip"         3 "" "${BASE[@]}" T_LINKED="11 22" T_LABELS="$LB"

check "PR Manual still stops everything" 3 "" "${BASE[@]}" T_LINKED="11" \
  T_LABELS="42:${TRIG}|${CODE}|Manual 11:${APPR}"

check "checks red -> skip"               3 "" T_GREEN=0 T_REVIEW=0 T_LINKED="11" \
  T_LABELS="$LB 11:${APPR}"

check "explain + red checks emits none"  3 "" T_GREEN=0 T_REVIEW=0 QA_GATE_EXPLAIN=1 \
  T_LINKED="11" T_LABELS="$LB 11:${APPR}"

check "milestone travels per issue"      0 "11,22" "${BASE[@]}" T_LINKED="11 22" \
  T_LABELS="$LB 11:${APPR} 22:${APPR}" T_MS="11=Billing_v9.4.3.7.RC.1"

# One unparseable marker among many good issues must not discard six issues that
# had already been selected and validated — it should exclude only itself.
check "malformed marker excludes only itself" 0 "11,33" "${BASE[@]}" T_LINKED="11 22 33" \
  T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}" T_BADMARKER="22"

# ...but when it is the ONLY reason nothing ran, staying quiet would leave a broken
# marker ignored on every event for the life of the commit.
check "malformed marker alone stops loudly"   4 "" "${BASE[@]}" T_LINKED="22" \
  T_LABELS="$LB 22:${APPR}" T_BADMARKER="22"

check "malformed + nothing approved: stops"   4 "" "${BASE[@]}" T_LINKED="11 22" \
  T_LABELS="$LB" T_BADMARKER="22"

check "explain mode stops on a bad marker"    4 "" "${BASE[@]}" QA_GATE_EXPLAIN=1 \
  T_LINKED="22" T_LABELS="$LB 22:${APPR}" T_BADMARKER="22"

# No malformed marker anywhere: nothing-runnable stays a QUIET skip, as before.
check "no marker at all is still a quiet skip" 3 "" "${BASE[@]}" T_LINKED="11" \
  T_LABELS="$LB 11:${APPR}" T_NOMARKER="11"

echo "--- malformed marker travels to the PR (scenario above):"
run "${BASE[@]}" T_LINKED="11 22 33" T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}" T_BADMARKER="22" \
  | jq -r '.passed_over[] | "  #\(.issue): \(.reason)"'

echo "--- passed_over reasons (scenario 3):"
run "${BASE[@]}" T_LINKED="11 22 33" T_LABELS="$LB 11:${APPR} 22:${APPR}|Manual 33:${APPR}" \
  | jq -r '.passed_over[] | "  #\(.issue): \(.reason)"'
echo "--- compat shim (scenario 1):"
run "${BASE[@]}" T_LINKED="11 22 33" T_LABELS="$LB 11:${APPR} 22:${APPR} 33:${APPR}" \
  | jq -c '{issue, milestone, marker_present:(.marker!=null), sha}'
echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
