#!/usr/bin/env bash
# Compile the pipeline script with Groovy. Catches what lint-jenkinsfile.sh cannot —
# an unbalanced brace, a broken GString, a malformed closure — without a Jenkins.
#
#   ci/qa/tests/jenkinsfile-parse.sh [pipeline-script-path]
#
# @NonCPS is stripped first: the annotation lives in the Jenkins plugin, not on a
# plain Groovy classpath, and an unresolved annotation is a compile error that says
# nothing about the code under it.
#
# The pipeline script — JENKINS-PIPELINE-SCRIPT.groovy — is pasted directly into
# the Jenkins job's "Pipeline script" field and is deliberately NOT committed; it
# lives only wherever someone saved a local copy of it. So this takes the path
# as $1, or falls back to QA_PIPELINE_SCRIPT_PATH. With neither set, there is
# nothing in-repo to check by default: it explains that and skips (exit 0)
# rather than failing — a local developer convenience check, not something CI
# can run unattended any more.
#
# Also skips (exit 0) when groovy is not installed, so a node without it is not a
# failure either.
set -uo pipefail

f="${1:-${QA_PIPELINE_SCRIPT_PATH:-}}"

if [[ -z "$f" ]]; then
  cat >&2 <<'MSG'
jenkinsfile-parse: no pipeline script path given.

JENKINS-PIPELINE-SCRIPT.groovy lives outside this repo now — it's pasted directly
into the Jenkins job's "Pipeline script" field, not committed. Point this check at
a local copy of it (e.g. saved from the Jenkins UI) to run it:

  bash ci/qa/tests/jenkinsfile-parse.sh /path/to/JENKINS-PIPELINE-SCRIPT.groovy
  QA_PIPELINE_SCRIPT_PATH=/path/to/it bash ci/qa/tests/jenkinsfile-parse.sh

Skipping — not a failure.
MSG
  exit 0
fi

[[ -f "$f" ]] || { printf 'parse: %s does not exist\n' "$f" >&2; exit 1; }

if ! command -v groovy >/dev/null 2>&1; then
  echo "parse: groovy not installed — skipped"
  exit 0
fi

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT
sed 's/^@NonCPS$//' "$f" > "$tmp/candidate.groovy"

cat > "$tmp/parse.groovy" <<'EOF'
try {
  new groovy.lang.GroovyShell().parse(new File(args[0]))
  println 'parse: OK'
} catch (Throwable t) {
  println 'parse: FAILED'
  println t.message.take(3000)
  System.exit(1)
}
EOF

groovy "$tmp/parse.groovy" "$tmp/candidate.groovy"
