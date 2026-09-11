#!/usr/bin/env bash
# Print the qa-first-round marker a previous round left on a PR, for a given commit.
#
#   ci/qa/pr-marker.sh <pr-number> <head-sha>
#
# Prints the marker JSON on stdout, or nothing when there is none. Exit status is 0
# either way: "no previous round" is the normal case, not an error.
#
# Why this exists as a script rather than inline in the pipeline: publishReport has to
# UNION what it is about to write with what is already there. The PR carries one
# marker-keyed comment and publishing PATCHes it in place, so a second build for the
# same commit — the resume path, after a round stopped part-way through its issues —
# would otherwise replace [A,B] with [C] and lose the record that A and B ever ran.
# The next build would then re-run them, and the counts in the banner would describe
# one issue while claiming to cover the round.
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/gh-client.sh"
. "${here}/marker.sh"

pr_marker_json "${1:?usage: pr-marker.sh <pr> <sha>}" "${2:?usage: pr-marker.sh <pr> <sha>}"
