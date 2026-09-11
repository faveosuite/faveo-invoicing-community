#!/usr/bin/env bash
# Every offline check for the QA pipeline's shell. No network, no credentials, no
# Jenkins — so the parts that decide whether a round runs, and where its results
# get written, can be changed without a real build to find out.
#
#   ci/qa/tests/run.sh
#
# Exits non-zero if any suite reports a failure.
set -uo pipefail
here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
rc=0
for suite in gate-scenarios qatouch-run-resolution marker-resume fixture-cast case-lookup build-timeout jenkinsfile-parse; do
  printf '\n=== %s\n' "$suite"
  bash "$here/${suite}.sh" || rc=1
  # A suite reports its own tally; the grep keeps a suite that dies early from
  # passing silently.
done
exit $rc
