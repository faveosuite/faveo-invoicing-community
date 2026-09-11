#!/usr/bin/env bash
# The fixture cast, checked for drift. Offline: no database, no Laravel boot.
#
#   ci/qa/tests/fixture-cast.sh
#
# This is a billing app with plain admin/user roles, no agent, no department
# system, so there is no in-scope/out-of-scope distinction to seed — just two
# members:
#
#   admin    role admin — configures plans, taxes, gateways, settings
#   client   role user  — a real customer: checkout, invoices, payment methods
#
# Both are REQUIRED. There is no optional extra member in this cast — admin
# and client ARE the whole cast, and a round missing either cannot exercise
# that side of the app at all, so there is no "skipped without failing" case
# left to test.
#
# The cast is defined in two places that must agree, and nothing at runtime
# notices when they stop agreeing:
#
#   seed-qa-users.php   reads the env vars and creates the accounts
#   the two prompts      tell the authoring and executing agents what exists
#
# A member documented in a prompt but absent from the seed is the dangerous
# direction: cases get authored against an account that will not exist, and they
# come back blocked after an instance has been provisioned to find out.
#
# (The pipeline script — see tests/jenkinsfile-parse.sh — is pasted into the
# Jenkins job config and is NOT in the repo, so there is nothing to grep for
# a credentials write there. Department-membership assertions are dropped
# outright: billing has no departments.)
set -uo pipefail
pass=0; fail=0

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
QA_SRC="${QA_SRC:-$(cd "$HERE/.." && pwd)}"

t() { # t <label> <expected> <actual>
  if [[ "$3" == "$2" ]]; then printf 'PASS  %-52s -> %s\n' "$1" "${3:-<empty>}"; pass=$((pass+1))
  else printf 'FAIL  %-52s -> got %q want %q\n' "$1" "$3" "$2"; fail=$((fail+1)); fi
}

MEMBERS="QA_ADMIN QA_CLIENT"
seed="$QA_SRC/seed-qa-users.php"

for m in $MEMBERS; do
  in_seed=$(grep -c "'${m}_EMAIL'" "$seed")
  t "$m is read by the seed" "1" "$in_seed"
done

# Both required — see the header note. There is no optional third member in
# this cast, so the actual seed script has no 'required' flag at all: it just
# calls env_required() directly for each of the two members, which IS the
# "always required" behaviour, expressed the simplest way it can be for a
# two-member cast.
t "admin is required"  "yes" \
  "$(grep -qE "env_required\('QA_ADMIN_EMAIL'\)" "$seed" && echo yes || echo no)"
t "client is required" "yes" \
  "$(grep -qE "env_required\('QA_CLIENT_EMAIL'\)" "$seed" && echo yes || echo no)"

# "At least once", not exactly once: the members are named in the cast table and
# again in the prose that explains which to reach for. Pinning the count would fail
# on an edit that improves the prompt.
for m in $MEMBERS; do
  for prompt in stage1-author-prompt stage3-prompt; do
    n=$(grep -c -- "${m}_" "$QA_SRC/${prompt}.md")
    t "$m documented in ${prompt%%-*}" "yes" "$([[ $n -ge 1 ]] && echo yes || echo no)"
  done
done

if command -v php >/dev/null 2>&1; then
  t "seed-qa-users.php parses" "0" \
    "$(php -l "$seed" >/dev/null 2>&1; echo $?)"
fi

echo
echo "$pass passed, $fail failed"
exit $(( fail > 0 ? 1 : 0 ))
