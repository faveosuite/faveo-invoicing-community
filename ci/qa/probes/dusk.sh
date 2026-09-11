#!/usr/bin/env bash
# Run the project's own Dusk suite against the per-build instance and convert its
# results into probe findings.
#
#   ci/qa/probes/dusk.sh <target|paths> <out.jsonl> [artifact-dir]
#
# This is the browser harness the team already maintains: tests/DuskTestCase.php,
# tests/Browser/**, the page objects in tests/Browser/Pages, and the environment
# `php artisan testing-setup` writes (.env.dusk.testing). Nothing here is a second
# opinion about how to drive a browser — it runs what the repo has, on the CI
# node, against the instance this build provisioned.
#
# There is no phpunit.dusk.xml in this repo and no suite-name config to match —
# laravel/dusk's own `dusk` artisan command already handles that: when neither
# phpunit.dusk.xml nor phpunit.dusk.xml.dist exists, it copies its own stub
# (vendor/laravel/dusk/stubs/phpunit.xml — one testsuite, `./tests/Browser`,
# suffix Test.php) to phpunit.dusk.xml for the run and deletes it again
# afterwards (Laravel\Dusk\Console\DuskCommand::writeConfiguration /
# removeConfiguration). So <target> is either:
#
#   * "all", meaning every test under tests/Browser — no arguments, so
#     the auto-written default config's own testsuite decides;
#   * one or more FILE PATHS, space separated, to run exactly those tests —
#     the form ci/qa/dusk-changed.sh emits, and also what it emits (as the
#     single path "tests/Browser") when a shared harness file changed.
#
# `php artisan dusk` forwards anything it does not recognise straight to
# phpunit (it calls ignoreValidationErrors() and passes the rest through), so a
# file or directory path works exactly like it would with phpunit directly.
#
# Findings are NON-blocking by default (QA_DUSK_BLOCKING=1 to change that): the
# existing suite's pass rate on a fresh per-build database is not established,
# and failing a PR on that before it is known would be a gate nobody trusts. The
# pipeline DOES pass QA_DUSK_BLOCKING=1 when the targets are tests the PR itself
# added or changed — an author's own new browser test failing is not a
# judgement call.
#
# Always exits 0.

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/lib.sh"

target="${1:?usage: dusk.sh <target|paths> <out.jsonl> [artifact-dir]}"
out="${2:?usage: dusk.sh <target|paths> <out.jsonl> [artifact-dir]}"
# A RELATIVE default writes into whatever directory the caller happens to be in:
# running this by hand from the repo root is what put an empty qa-evidence/ in the
# application tree. The pipeline always passes an explicit path; this is for the
# standalone case, and it stays outside the tree under test.
artifacts="${3:-${QA_EVIDENCE_DIR:-${TMPDIR:-/tmp}/qa-evidence}}"
mkdir -p "$artifacts" 2>/dev/null || true
if ! artifacts="$(cd "$artifacts" 2>/dev/null && pwd)" || [[ ! -w "$artifacts" ]]; then
  printf 'dusk: artifact directory %s is not writable (as user %s) — nothing was run\n' \
    "$artifacts" "$(id -un)" >&2
  exit 0
fi

# Absolute before the cd below: findings must land where the caller globs for them,
# not next to the project root this script has to run from.
out_dir="$(dirname "$out")"
mkdir -p "$out_dir" 2>/dev/null || true
if ! out_dir="$(cd "$out_dir" 2>/dev/null && pwd)" || [[ ! -w "$out_dir" ]]; then
  printf 'dusk: cannot write findings to %s (as user %s) — nothing was run\n' \
    "$(dirname "$out")" "$(id -un)" >&2
  exit 0
fi
out="${out_dir}/$(basename "$out")"
probe_init "$out"

# The application under test — which is NOT this script's own ancestry once the
# pipeline runs from a snapshot of ci/qa copied to a sibling directory. Honour
# QA_APP_ROOT first, then $WORKSPACE, then the caller's directory, then the
# relative guess for a plain in-repo run — and pick the first candidate that
# actually looks like the application (an `artisan` file — there is no
# phpunit.dusk.xml to look for here) rather than assuming.
root=''
for candidate in "${QA_APP_ROOT:-}" "${WORKSPACE:-}" "$PWD" "${here}/../../.."; do
  [[ -n "$candidate" && -d "$candidate" ]] || continue
  if [[ -f "${candidate}/artisan" ]]; then
    root="$(cd "$candidate" && pwd)"
    break
  fi
done

if [[ -z "$root" ]]; then
  probe_unchecked DUSK-00 browser 'Could not locate the application to test' \
    "none of QA_APP_ROOT=${QA_APP_ROOT:-unset}, WORKSPACE=${WORKSPACE:-unset}, ${PWD} or ${here}/../../.. holds an artisan file"
  exit 0
fi

cd "$root" || exit 0
printf 'dusk: application root %s\n' "$root" >&2

blocking=false
[[ "${QA_DUSK_BLOCKING:-0}" == "1" ]] && blocking=true

# ---------------------------------------------------------------------------
# preconditions — each one an explicit "not run", never a pass
# ---------------------------------------------------------------------------

if [[ ! -f .env.dusk.testing ]]; then
  probe_unchecked DUSK-00 browser 'No .env.dusk.testing' \
    'php artisan testing-setup writes this file — provisioning did not run, or ran in a different workspace'
  exit 0
fi

# Dusk needs a browser AND a matching driver. `dusk:chrome-driver --detect` reads
# the installed Chrome's version and fetches the driver to match; a mismatched
# pair is the classic silent Dusk failure ("session not created: This version of
# ChromeDriver only supports Chrome version N").
browser_bin=''
for candidate in google-chrome google-chrome-stable chromium chromium-browser; do
  if command -v "$candidate" >/dev/null 2>&1; then browser_bin="$candidate"; break; fi
done

if [[ -z "$browser_bin" ]]; then
  probe_unchecked DUSK-00 browser 'No Chrome or Chromium on this node' \
    'install google-chrome-stable (or chromium) on the CI agent — Dusk drives a real browser'
  exit 0
fi

if ! php artisan dusk:chrome-driver --detect >&2; then
  probe_unchecked DUSK-00 browser 'Could not install a matching chromedriver' \
    "dusk:chrome-driver --detect failed for ${browser_bin} — check the agent can reach the driver download, or pre-install one"
  exit 0
fi

# ---------------------------------------------------------------------------
# run
# ---------------------------------------------------------------------------

# Split the target into path tokens. "all" means no explicit paths at all — the
# auto-written default config (every test under tests/Browser) decides.
paths=()
for token in $target; do
  [[ "$token" == "all" ]] && continue
  paths+=("$token")
done

junit="${artifacts}/dusk-results.xml"
run_args=()
label="$target"

if (( ${#paths[@]} )); then
  missing=()
  for path in "${paths[@]}"; do
    [[ -e "$path" ]] || missing+=("$path")
  done
  if (( ${#missing[@]} )); then
    probe_unchecked DUSK-00 browser 'Some target test files are not in this checkout' \
      "$(printf '%s ' "${missing[@]}")"
    exit 0
  fi
  run_args=("${paths[@]}")
  label="${#paths[@]} target(s)"
elif [[ "$target" == "all" ]]; then
  run_args=()
  label='every test under tests/Browser'
else
  probe_unchecked DUSK-00 browser 'No Dusk target resolved' "target was \"${target}\""
  exit 0
fi

printf 'dusk: running %s against %s\n' "$label" "${APP_URL:-the APP_URL in .env}" >&2

# --env=testing selects .env.dusk.testing. Dusk swaps it over .env for the run and
# restores afterwards, which is why provisioning writes the same content into .env:
# the served instance and the suite must agree on which database they are using.
php artisan dusk --env=testing "${run_args[@]}" \
  --log-junit "$junit" >&2
rc=$?

if [[ ! -s "$junit" ]]; then
  probe_unchecked DUSK-01 browser "The Dusk run produced no JUnit report (exit ${rc})" \
    'see the build log — no browser result was recorded, so nothing here is evidence either way'
  exit 0
fi

# ---------------------------------------------------------------------------
# convert
# ---------------------------------------------------------------------------
# One finding per test case. PHP rather than a shell XML parse: PHP is guaranteed
# present (it just ran the suite) and simplexml gets the nesting right, which
# grep does not.
php -r '
$file = $argv[1];
$xml = @simplexml_load_file($file);
if (! $xml) { exit(0); }
foreach ($xml->xpath("//testcase") as $tc) {
    $class = (string) $tc["classname"];
    $name  = (string) $tc["name"];
    // chr(92), not a quoted backslash: this program is embedded in a
    // single-quoted shell string, so neither \\ nor an escaped quote survives
    // intact. Getting it wrong silently shaved the first letter off every class.
    // PHPUnit writes the classname with backslashes in some versions and dots in
    // others, so split on both rather than shipping "Tests.Browser.FooTest" as a
    // test name.
    $parts = preg_split("/[" . chr(92) . ".]/", $class);
    $short = $class === "" ? $name : end($parts) . "::" . $name;
    $status = "pass";
    $detail = "";
    if (isset($tc->failure)) { $status = "fail"; $detail = (string) $tc->failure[0]; }
    elseif (isset($tc->error)) { $status = "fail"; $detail = (string) $tc->error[0]; }
    elseif (isset($tc->skipped)) { $status = "skip"; $detail = "skipped by the suite"; }
    $detail = preg_replace("/\s+/", " ", trim($detail));
    echo json_encode([$status, $short, substr($detail, 0, 500)]) . "\n";
}
' "$junit" \
| while IFS= read -r row; do
    status=$(jq -r '.[0]' <<<"$row")
    title=$(jq -r '.[1]' <<<"$row")
    detail=$(jq -r '.[2]' <<<"$row")
    id="DUSK-$(printf '%.4s' "$(printf '%s' "$title" | md5sum | cut -c1-4)")"

    case "$status" in
      pass) probe_pass "$id" browser medium "$blocking" "$title" '' ;;
      fail) probe_fail "$id" browser high    "$blocking" "$title" "$detail" ;;
      skip) probe_skip "$id" browser "$title" "$detail" ;;
    esac
  done

printf 'dusk: %s finding(s) from %s (runner exit %s)\n' "$(wc -l < "$out")" "$label" "$rc" >&2
exit 0
