#!/usr/bin/env bash
# Shared plumbing for the deterministic probes (security, API).
# Source this, don't execute it.
#
# Every probe emits one JSON object per check to a JSONL file:
#
#   {"id":"SEC-01","discipline":"security","title":"…","status":"pass|fail|warn|skip",
#    "severity":"critical|high|medium|low|info","blocking":true|false,"evidence":"…"}
#
# Two separate axes, and conflating them is the mistake to avoid:
#
#   severity  — how bad it would be if real. Reporting only.
#   blocking  — whether THIS pipeline turns it into a "needs correction" verdict.
#
# Only a small, unambiguous core is blocking: things that are wrong on any build
# of this application, in any configuration, with no judgement call
# (a served .env, a stack trace in a response, an admin endpoint answering an
# anonymous request). Everything else is reported and left to a person, because a
# first-round gate that fails on a pre-existing hardening gap fails every PR
# equally and teaches the team to ignore it.

set -uo pipefail

PROBE_OUT="${PROBE_OUT:-probe-results.jsonl}"
PROBE_UA='billing-qa-first-round/1.0'
PROBE_TIMEOUT="${PROBE_TIMEOUT:-20}"

probe_init() {
  PROBE_OUT="$1"
  : > "$PROBE_OUT"
}

# probe_record <id> <discipline> <severity> <blocking> <status> <title> <evidence>
probe_record() {
  jq -cn --arg id "$1" --arg d "$2" --arg sev "$3" --argjson blocking "$4" \
        --arg st "$5" --arg t "$6" --arg e "$7" \
    '{id:$id, discipline:$d, severity:$sev, blocking:$blocking, status:$st, title:$t, evidence:$e}' \
    >> "$PROBE_OUT"
  printf '  %-8s %-5s %s\n' "$1" "$5" "$6" >&2
}

# pass/fail take the blocking flag; warn never blocks.
#   probe_pass      <id> <discipline> <severity> <blocking> <title> [evidence]
#   probe_warn      <id> <discipline> <severity> <title> [evidence]
#   probe_skip      <id> <discipline> <title> [evidence]     a non-blocking check
#   probe_unchecked <id> <discipline> <title> [evidence]     a BLOCKING check that
#                                                            did not run
#
# The distinction matters more than it looks. A skipped blocking check is not a
# pass — it is the absence of evidence, and summarize.sh turns any of them into an
# "unknown" verdict so no approval label is applied. Without that, a runner where
# a login never succeeds emits one skip, finds zero blocking failures, and the
# round signs off having never actually exercised the auth boundary.
probe_pass()      { probe_record "$1" "$2" "$3" "$4" pass "$5" "${6:-}"; }

# Findings that reproduce on the base branch predate every PR the pipeline will
# ever see. Charging one to whichever change happens to be under test blocks an
# author on a defect they never touched, and a gate that fails every PR equally
# is one the team learns to ignore. Those ids are listed in
# known-pre-existing.txt and reported as warnings, carrying the reason, so they
# stay visible without blocking. Delete an entry the moment its defect is fixed:
# a stale one would silence a genuine regression in the same check.
PROBE_KNOWN_FILE="${PROBE_KNOWN_FILE:-${BASH_SOURCE[0]%/*}/known-pre-existing.txt}"

probe_known_reason() {
  [[ -f "$PROBE_KNOWN_FILE" ]] || return 1
  awk -v want="$1" '
    /^[[:space:]]*(#|$)/ { next }
    $1 == want { $1 = ""; sub(/^[[:space:]]+/, ""); print; found = 1; exit }
    END { exit(found ? 0 : 1) }
  ' "$PROBE_KNOWN_FILE"
}

probe_fail() {
  local reason
  if reason=$(probe_known_reason "$1") && [[ -n "$reason" ]]; then
    probe_record "$1" "$2" "$3" false warn "$5" \
      "pre-existing, not introduced by this change — ${reason}${6:+ | }${6:-}"
    return
  fi
  probe_record "$1" "$2" "$3" "$4" fail "$5" "${6:-}"
}
probe_warn()      { probe_record "$1" "$2" "$3" false warn "$4" "${5:-}"; }
probe_skip()      { probe_record "$1" "$2" info false skip "$3" "${4:-}"; }
probe_unchecked() { probe_record "$1" "$2" high true skip "$3" "${4:-}"; }

# ---------------------------------------------------------------------------
# HTTP
# ---------------------------------------------------------------------------

# curl with the settings every check wants: no redirect following (a 302 is
# frequently the answer being tested), a timeout, and no proxy inheritance.
probe_curl() {
  local extra=()
  # This instance is never served over TLS (no forced-HTTPS, no self-signed
  # cert to worry about) — PROBE_INSECURE is accepted only so a stray -k in a
  # caller's args doesn't break anything; it is not load-bearing here.
  [[ "${PROBE_INSECURE:-0}" == "1" ]] && extra+=(-k)
  curl -sS --noproxy '*' --max-time "$PROBE_TIMEOUT" -A "$PROBE_UA" "${extra[@]}" "$@"
}

# probe_code <url> [curl args...] -> HTTP status code
probe_code() {
  local url="$1"; shift
  probe_curl -o /dev/null -w '%{http_code}' "$@" "$url" 2>/dev/null || printf '000'
}

# probe_body <url> [curl args...] -> response body on stdout
probe_body() {
  local url="$1"; shift
  probe_curl "$@" "$url" 2>/dev/null || true
}

# probe_headers <url> [curl args...] -> response headers on stdout
probe_headers() {
  local url="$1"; shift
  probe_curl -sI "$@" "$url" 2>/dev/null || true
}

# probe_header_value <headers-blob> <name> -> value, case-insensitive
probe_header_value() {
  printf '%s\n' "$1" | awk -v want="$(printf '%s' "$2" | tr '[:upper:]' '[:lower:]')" '
    { line = $0; sub(/\r$/, "", line)
      split(line, kv, ":")
      k = tolower(kv[1])
      if (k == want) { sub(/^[^:]*:[ \t]*/, "", line); print line } }'
}

# Laravel and PHP leak their internals in recognisable ways. One list, used by
# every check that asks "did this response spill the stack?".
PROBE_LEAK_PATTERN='Whoops|Stack trace|vendor/laravel/framework|Illuminate\\\\|SQLSTATE|Fatal error|Uncaught .*Exception|/var/www/'

probe_leaks_internals() {
  grep -qEi "$PROBE_LEAK_PATTERN" <<<"$1"
}

# ---------------------------------------------------------------------------
# session login — needed by every authorisation check
# ---------------------------------------------------------------------------
# routes/api.php is empty — there is no token API. Every AJAX/JSON endpoint in
# this app lives in routes/web.php behind the `web` group (session cookie +
# CSRF), so an authorisation probe has to hold a real session: a cookie jar and
# the CSRF token off the login page, same as a browser would.
#
# A route that requires only `auth` (reachable by role=admin OR role=user) and
# answers 200 while authenticated / redirects otherwise, with no JSON parsing
# needed to tell the difference. Front\ClientController's constructor applies
# only `$this->middleware('auth')`, so any of its plain HTML routes works for
# both roles; my-invoices is the smallest.
PROBE_AUTH_CHECK_PATH='/my-invoices'

# probe_login <base> <email> <password> <cookie-jar> -> 0 on success
#
# Reuses a jar that still authenticates. Logging in is not free here:
# App\Http\Middleware\BlockFailedVerifications locks the identifier (IP +
# email/username, see LoginController::getLoginRateLimitKey) out after 5 failed
# attempts in the 'login' context, for an escalating 30/60/180/360 minutes. Two
# probes each logging in twice is most of the budget spent before the browser
# suite starts. Point QA_SESSION_JAR_DIR at a directory shared between probe
# runs to make one login serve all of them.
probe_login() {
  local base="$1" email="$2" password="$3" jar="$4" page token

  # A shared jar, when one is configured and still valid.
  if [[ -n "${QA_SESSION_JAR_DIR:-}" ]]; then
    local shared="${QA_SESSION_JAR_DIR}/$(printf '%s' "$email" | md5sum | cut -c1-12).jar"
    mkdir -p "$QA_SESSION_JAR_DIR" 2>/dev/null || true
    if [[ -s "$shared" ]] \
       && [[ "$(probe_code "${base}${PROBE_AUTH_CHECK_PATH}" -b "$shared")" == "200" ]]; then
      cp -f "$shared" "$jar"
      return 0
    fi
    # Log in below, then publish the jar for the next probe.
    QT_PUBLISH_JAR="$shared"
  fi

  # Dusk's session-bypass route, when the ids are published. One GET, one cookie —
  # no CSRF token, no honeypot, and nothing counted against the 5-attempt lockout
  # that otherwise blocks the rest of the round. laravel/dusk auto-registers
  # GET /_dusk/login/{userId} on any non-production environment, which the
  # disposable per-PR instance is.
  if [[ -s "${QA_USERS_FILE:-}" ]]; then
    local uid
    uid=$(jq -r --arg e "$email" 'to_entries[] | select(.value.email == $e) | .value.id' "$QA_USERS_FILE" 2>/dev/null | head -1)
    if [[ -n "$uid" && "$uid" != "null" ]]; then
      rm -f "$jar"
      probe_curl -L -c "$jar" -b "$jar" -o /dev/null "${base}/_dusk/login/${uid}" 2>/dev/null || true
      if [[ "$(probe_code "${base}${PROBE_AUTH_CHECK_PATH}" -b "$jar")" == "200" ]]; then
        [[ -n "${QT_PUBLISH_JAR:-}" ]] && cp -f "$jar" "$QT_PUBLISH_JAR" 2>/dev/null || true
        return 0
      fi
      printf 'probe: /_dusk/login/%s did not authenticate — falling back to the login form\n' "$uid" >&2
    fi
  fi

  rm -f "$jar"
  page=$(probe_curl -c "$jar" -b "$jar" "${base}/login") || return 1

  # The CSRF token: a meta tag on every page that extends the front layout
  # (resources/views/themes/default1/layouts/front/master.blade.php).
  token=$(grep -oE 'name="csrf-token" content="[^"]+"' <<<"$page" | head -1 | sed 's/.*content="//; s/"$//')

  # LoginRequest validates a 'login' field with App\Rules\Honeypot: it must be an
  # array of exactly two entries — one key starting with 'p' (a decoy text input
  # that must stay empty) and one starting with 't' (a hidden input holding
  # Crypt::encrypt(time()), which the rule re-decrypts and requires to be at
  # least 1 second old). Both field names are randomised per page load
  # (helpers.php: honeypotField()), so they have to be scraped off this same
  # page, not hard-coded — and skipping them entirely fails LoginRequest's
  # validation on every attempt, which is exactly what happened until this was
  # traced back from LoginRequest -> App\Rules\Honeypot -> helpers.php.
  local pot_name time_field time_name time_value
  pot_name=$(grep -oE 'type="text" name="login\[[^]]+\]"' <<<"$page" | head -1 \
             | grep -oE 'login\[[^]]+\]' | sed 's/^login\[//; s/\]$//')
  time_field=$(grep -oE 'type="hidden" name="login\[[^]]+\]" value="[^"]*"' <<<"$page" | head -1)
  time_name=$(grep -oE 'login\[[^]]+\]' <<<"$time_field" | sed 's/^login\[//; s/\]$//')
  time_value=$(grep -oE 'value="[^"]*"' <<<"$time_field" | sed 's/^value="//; s/"$//')

  if [[ -z "$token" || -z "$pot_name" || -z "$time_name" ]]; then
    printf 'probe: could not find the CSRF token or the login honeypot fields on /login — did the form markup change?\n' >&2
    return 2
  fi

  # The honeypot's encrypted timestamp must be at least 1 second old by the time
  # the server re-checks it (App\Rules\Honeypot, $minTime = 1). Wait it out
  # rather than racing the clock on a fast run.
  sleep 2

  local code
  code=$(probe_curl -o /dev/null -w '%{http_code}' -c "$jar" -b "$jar" \
           -X POST "${base}/login" \
           -H "X-CSRF-TOKEN: ${token}" \
           -H 'Accept: application/json' \
           -H 'X-Requested-With: XMLHttpRequest' \
           --data-urlencode "_token=${token}" \
           --data-urlencode "email_username=${email}" \
           --data-urlencode "password1=${password}" \
           --data-urlencode "login[${pot_name}]=" \
           --data-urlencode "login[${time_name}]=${time_value}")

  # LoginController::login() always answers JSON (successResponse/errorResponse),
  # never a redirect — 200 {"success":true,...} on success, 400
  # {"success":false,...} on bad credentials (errorResponse's default status).
  # A 200 does not by itself prove the session is USABLE, though: an unverified
  # account or one with 2FA enabled also gets 200 (handleUnverifiedUser /
  # handleTwoFactorAuthentication), but both call Auth::logout() first and hand
  # back a redirect to /verify or /verify-2fa instead of a live session. So the
  # 200 is confirmed against the auth-check path before this counts as success.
  case "$code" in
    200)
      if [[ "$(probe_code "${base}${PROBE_AUTH_CHECK_PATH}" -b "$jar")" == "200" ]]; then
        [[ -n "${QT_PUBLISH_JAR:-}" ]] && cp -f "$jar" "$QT_PUBLISH_JAR" 2>/dev/null || true
        return 0
      fi
      printf 'probe: login as %s answered 200 but the session does not authenticate — email/mobile verification or 2FA pending on this account?\n' "$email" >&2
      return 1 ;;
    422) printf 'probe: login as %s was rejected as a validation failure (HTTP 422) — the CSRF token or honeypot fields were likely stale or misread\n' "$email" >&2; return 2 ;;
    429) printf 'probe: login as %s was rate limited (HTTP 429) — 5 failed attempts locks the account out for 30+ minutes (BlockFailedVerifications)\n' "$email" >&2; return 1 ;;
    *)   printf 'probe: login as %s failed with HTTP %s\n' "$email" "$code" >&2; return 1 ;;
  esac
}
