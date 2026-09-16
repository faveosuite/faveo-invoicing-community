#!/usr/bin/env bash
# Which Dusk tests did this PR touch?
#
#   ci/qa/dusk-changed.sh <pr-number>
#
# Prints one target per line, for ci/qa/probes/dusk.sh:
#   tests/Browser/Admin/FooTest.php   run exactly this file
#   all                               run every test under tests/Browser
#
# Nothing printed means the PR touches no Dusk test, and the round skips the Dusk
# probe entirely.
#
# There is no phpunit.dusk.xml in this repo and no suite-name config to look
# tests up against — laravel/dusk's own `dusk` command just runs every
# *Test.php under tests/Browser by default (see probes/dusk.sh), so there is no
# suite membership to resolve here, only two rules:
#
#   * a changed or added tests/Browser/**/*Test.php runs as itself — fast, and
#     precisely the tests the author just wrote;
#   * a change to the SHARED HARNESS (tests/DuskTestCase.php, or anything under
#     tests/Browser/Pages/ or tests/Browser/Helpers/) runs every test under
#     tests/Browser instead. Those files are inherited by every browser test,
#     so "only run what changed" would be exactly wrong: the risk is in what did
#     not change.
#
# Deleted files are dropped — handing phpunit a path that no longer exists fails
# the run for a reason that is not a defect.

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/gh-client.sh"

pr="${1:?usage: dusk-changed.sh <pr-number>}"

gh_require_env || exit 2

# The checkout to test paths against. git first, because it is right even when this
# script is invoked through a symlink or a copy; the relative fallback keeps it
# working outside a work tree.
root="$(git rev-parse --show-toplevel 2>/dev/null)" || root=""
[[ -n "$root" ]] || root="$(cd "${here}/.." && pwd)"

SHARED_HARNESS=(
  'tests/DuskTestCase.php'
  'tests/Browser/Helpers/'
  'tests/Browser/Pages/'
)

changed=$(gh_pr_files "$pr") || exit 2
[[ -n "$changed" ]] || exit 0

harness_touched=false
paths=()

while IFS=$'\t' read -r status path; do
  [[ -n "${path:-}" ]] || continue
  [[ "$status" == "removed" ]] && continue

  for shared in "${SHARED_HARNESS[@]}"; do
    if [[ "$path" == "$shared" || "$path" == "$shared"* ]]; then
      harness_touched=true
      printf 'dusk-changed: %s is shared by every browser test\n' "$path" >&2
      continue 2
    fi
  done

  [[ "$path" == tests/Browser/*Test.php ]] || continue

  # Must exist in this checkout: the merge ref is what is being tested, and a
  # file renamed away or added on top of a revert may not be here.
  if [[ -f "${root}/${path}" ]]; then
    paths+=("$path")
  else
    printf 'dusk-changed: %s is in the diff but not in this checkout — skipping\n' "$path" >&2
  fi
done <<< "$changed"

if $harness_touched; then
  printf 'all\n'
  exit 0
fi

if (( ${#paths[@]} )); then
  printf '%s\n' "${paths[@]}"
fi
