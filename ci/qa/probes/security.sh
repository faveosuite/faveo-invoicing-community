#!/usr/bin/env bash
# Runtime (DAST) security probe against the throwaway instance.
#
#   ci/qa/probes/security.sh <base-url> <out.jsonl>
#
# This is the runtime half of security testing. The static half already exists in
# the main pipeline (Semgrep, Larastan, SonarQube) and is NOT repeated here —
# what those cannot see is what the running application actually serves: an
# exposed file, an admin endpoint answering an anonymous request, a stack trace
# in an error page, a role boundary that does not hold.
#
# It only ever runs against the per-PR disposable instance on 127.0.0.1. Do not
# point it at a shared or production host: several checks deliberately send bad
# input and hammer the login endpoint.
#
# Always exits 0. Findings are data, not build failures — summarize.sh decides
# the verdict from the blocking flags.

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/lib.sh"

base="${1:?usage: security.sh <base-url> <out.jsonl>}"
out="${2:?usage: security.sh <base-url> <out.jsonl>}"
base="${base%/}"

probe_init "$out"
printf 'security probe against %s\n' "$base" >&2

D=security
jar_admin=$(mktemp); jar_client=$(mktemp)
trap 'rm -f "$jar_admin" "$jar_client"' EXIT

# ---------------------------------------------------------------------------
# exposed files — blocking, because there is no configuration in which serving
# these is acceptable
# ---------------------------------------------------------------------------

check_not_served() {
  local id="$1" path="$2" needle="$3" sev="$4" body code
  code=$(probe_code "${base}${path}")
  body=$(probe_body "${base}${path}")
  if [[ "$code" == "200" ]] && grep -qE "$needle" <<<"$body"; then
    probe_fail "$id" "$D" "$sev" true "${path} is served over HTTP" \
      "HTTP 200, body matched /${needle}/ — first 120 bytes: $(head -c 120 <<<"$body" | tr -d '\n')"
  else
    probe_pass "$id" "$D" "$sev" true "${path} is not served" "HTTP ${code}"
  fi
}

check_not_served SEC-01 '/.env'                     'APP_KEY|DB_PASSWORD|APP_ENV' critical
check_not_served SEC-02 '/.git/config'              '\[core\]|remote "origin"'    critical
check_not_served SEC-03 '/storage/logs/laravel.log' 'local\.|production\.|Stack trace' high
check_not_served SEC-04 '/composer.json'            '"require"|"autoload"'        medium
check_not_served SEC-05 '/.env.example'             'APP_KEY|DB_DATABASE'         low

# ---------------------------------------------------------------------------
# error handling — a stack trace tells an attacker the framework, the paths and
# frequently the query. Blocking.
# ---------------------------------------------------------------------------

body=$(probe_body "${base}/this-route-does-not-exist-$(date +%s)")
if probe_leaks_internals "$body"; then
  probe_fail SEC-06 "$D" critical true "A 404 response leaks framework internals" \
    "matched: $(grep -oEi "$PROBE_LEAK_PATTERN" <<<"$body" | head -3 | tr '\n' ' ')"
else
  probe_pass SEC-06 "$D" critical true "404 responses do not leak internals" ''
fi

# APP_DEBUG on is what turns every error into that stack trace. There is no
# separate token API to ask directly here (routes/api.php is empty), so ask a
# different code path than SEC-06's 404: POST /login with no CSRF token, no
# honeypot fields and a NUL byte in the credential — a validation/CSRF exception
# on a public, unauthenticated route (LoginController's constructor excepts
# 'login' from the 'guest' middleware only, so this is reachable anonymously).
body=$(probe_body "${base}/login" -X POST -H 'Accept: application/json' \
        --data-urlencode $'email_username=qa-probe\x00\xff' --data-urlencode 'password1=x')
if probe_leaks_internals "$body"; then
  probe_fail SEC-07 "$D" critical true "A malformed login request returns a debug stack trace" \
    "APP_DEBUG is almost certainly on — $(head -c 160 <<<"$body" | tr -d '\n')"
else
  probe_pass SEC-07 "$D" critical true "Malformed requests do not return debug output" ''
fi

# ---------------------------------------------------------------------------
# authentication boundary — blocking
# ---------------------------------------------------------------------------
# These are session-authenticated JSON endpoints (routes/web.php, gated by
# `auth`/`admin` middleware applied in each controller's constructor — see
# App\Http\Controllers\Order\OrderController, \Order\InvoiceController,
# \Product\PlanController, and \Front\ClientController). An anonymous request
# must be turned away; 200 with a JSON body is a data leak.

anon_endpoints=(
  '/get-orders'
  '/get-invoices'
  '/get-plans'
  '/get-my-invoices'
  '/get-my-orders'
)

leaked=()
for path in "${anon_endpoints[@]}"; do
  code=$(probe_code "${base}${path}")
  body=$(probe_body "${base}${path}")
  # A 200 that is the login page (or a redirect target's HTML) is not data —
  # only a 200 that actually parses as JSON counts.
  if [[ "$code" == "200" ]] && jq -e . >/dev/null 2>&1 <<<"$body"; then
    leaked+=("${path} -> 200 JSON")
  fi
done

if (( ${#leaked[@]} )); then
  probe_fail SEC-08 "$D" critical true "Session-authenticated endpoints answer anonymous requests with data" \
    "$(printf '%s; ' "${leaked[@]}")"
else
  probe_pass SEC-08 "$D" critical true "Session-authenticated endpoints reject anonymous requests" \
    "checked ${#anon_endpoints[@]} endpoints"
fi

# The main dashboard (DashboardController; auth+admin on `index`) is the closest
# thing this app has to a "panel" landing page.
code=$(probe_code "${base}/")
if [[ "$code" == "302" || "$code" == "301" || "$code" == "401" || "$code" == "403" ]]; then
  probe_pass SEC-09 "$D" high true "/ is not reachable while logged out" "HTTP ${code}"
else
  probe_warn SEC-09 "$D" medium "/ returned HTTP ${code} while logged out" \
    'expected a redirect to /login'
fi

# ---------------------------------------------------------------------------
# CSRF — blocking
# ---------------------------------------------------------------------------
# App\Http\Middleware\VerifyCsrfToken overrides the framework default: it
# catches TokenMismatchException and always returns a 302 redirect to /login
# with a flash message ("Your session has expired…"), never Laravel's usual 419
# and never a JSON error — so, unlike a generic Laravel app, there is no 422 to
# disambiguate from a validation failure here. A redirect is the only correct
# answer to a CSRF-less POST.

csrf_code=$(probe_code "${base}/login" -X POST -H 'Accept: application/json' \
        --data-urlencode 'email_username=qa-probe@example.com' --data-urlencode 'password1=whatever')
csrf_headers=$(probe_headers "${base}/login" -X POST \
        --data-urlencode 'email_username=qa-probe@example.com' --data-urlencode 'password1=whatever')

if [[ "$csrf_code" == "302" ]] && grep -qi 'login' <<<"$(probe_header_value "$csrf_headers" 'Location')"; then
  probe_pass SEC-10 "$D" high true "POST without a CSRF token is rejected" \
    "HTTP 302 -> $(probe_header_value "$csrf_headers" 'Location')"
elif [[ "$csrf_code" == "419" || "$csrf_code" == "403" ]]; then
  probe_pass SEC-10 "$D" high true "POST without a CSRF token is rejected" "HTTP ${csrf_code}"
else
  probe_fail SEC-10 "$D" high true "POST /login without a CSRF token returned HTTP ${csrf_code}" \
    "expected a 302 redirect to /login (App\\Http\\Middleware\\VerifyCsrfToken)"
fi

# ---------------------------------------------------------------------------
# headers and cookies — reported, not blocking. These are configuration-level
# and a first round should not fail a PR for a pre-existing hardening gap.
# ---------------------------------------------------------------------------

# GET /login renders directly (LoginController::showLoginForm, no redirect), so
# it is a real Illuminate\Http\Response and carries whatever
# App\Http\Middleware\SecurityEnforcer decorates it with.
headers=$(probe_headers "${base}/login")
for pair in 'X-Frame-Options:SEC-11' 'X-Content-Type-Options:SEC-12' 'Strict-Transport-Security:SEC-13'; do
  name="${pair%%:*}"; id="${pair##*:}"
  value=$(probe_header_value "$headers" "$name")
  if [[ -n "$value" ]]; then
    probe_pass "$id" "$D" low false "${name} is set" "$value"
  else
    probe_warn "$id" "$D" medium "${name} is missing" 'set by App\Http\Middleware\SecurityEnforcer'
  fi
done

# X-Frame-Options: ALLOW FROM is obsolete — Chrome and Firefox ignore it entirely,
# which means the clickjacking protection this header is there to provide is not
# actually in force. Worth saying once, in the report, rather than never.
xfo=$(probe_header_value "$headers" 'X-Frame-Options')
if grep -qi 'allow.from' <<<"$xfo"; then
  probe_warn SEC-14 "$D" medium 'X-Frame-Options uses the obsolete ALLOW-FROM form' \
    "value: ${xfo} — no current browser honours ALLOW-FROM; frame-ancestors in a CSP is the replacement"
fi

if [[ -z "$(probe_header_value "$headers" 'Content-Security-Policy')" ]]; then
  probe_warn SEC-15 "$D" medium 'No Content-Security-Policy header' \
    'the main defence-in-depth control against injected script'
fi

cookies=$(probe_headers "${base}/login" | grep -i '^set-cookie:' || true)
if [[ -n "$cookies" ]]; then
  session_cookie=$(grep -iE 'session' <<<"$cookies" | head -1)
  if [[ -n "$session_cookie" ]]; then
    grep -qi 'httponly' <<<"$session_cookie" \
      && probe_pass SEC-16 "$D" high true 'Session cookie is HttpOnly' '' \
      || probe_fail SEC-16 "$D" high true 'Session cookie is not HttpOnly' \
           "$(head -c 160 <<<"$session_cookie")"
    grep -qi 'samesite' <<<"$session_cookie" \
      && probe_pass SEC-17 "$D" low false 'Session cookie sets SameSite' '' \
      || probe_warn SEC-17 "$D" medium 'Session cookie has no SameSite attribute' \
           "$(head -c 160 <<<"$session_cookie")"
  fi
fi

server_header=$(probe_header_value "$headers" 'X-Powered-By')
[[ -n "$server_header" ]] && probe_warn SEC-18 "$D" low 'X-Powered-By discloses the PHP version' "$server_header"

# ---------------------------------------------------------------------------
# injection surfaces — reported. A reflected payload or an SQL error is a real
# finding, but the probe only reaches unauthenticated endpoints, so absence here
# proves very little and must not read as "no injection issues".
# ---------------------------------------------------------------------------

# email_username flows straight into a `User::where('email', ...)->orWhere(...)`
# lookup (LoginController::getLoginRateLimitKey / Auth::attempt) — both are
# Eloquent query-builder calls, parameter-bound by default, but this is the one
# unauthenticated field in the app that reliably reaches a database query.
sqli_body=$(probe_body "${base}/login" -X POST -H 'Accept: application/json' \
        --data-urlencode "email_username=' OR 1=1--" --data-urlencode 'password1=x')
if grep -qEi 'SQLSTATE|SQL syntax|mysqli|PDOException' <<<"$sqli_body"; then
  probe_fail SEC-19 "$D" critical true 'A quote in the login email field produces a database error' \
    "$(grep -oEi 'SQLSTATE.{0,120}' <<<"$sqli_body" | head -1)"
else
  probe_pass SEC-19 "$D" high false 'No database error from a quote in the login email field' ''
fi

# The login page does not read a 'redirect' (or any other) query parameter into
# the page today — Laravel's post-login redirect uses the session
# (redirect()->intended()), not a query string — so this is a regression guard
# for that changing, not evidence of a known reflection point.
xss_marker='qaprobe<svg/onload=1>'
xss_body=$(probe_body "${base}/login?redirect=$(jq -rn --arg s "$xss_marker" '$s|@uri')")
if grep -qF "$xss_marker" <<<"$xss_body"; then
  probe_fail SEC-20 "$D" critical true 'A query parameter is reflected into the page unescaped' \
    'payload came back verbatim in the HTML'
else
  probe_pass SEC-20 "$D" high false 'Query parameters are not reflected unescaped' ''
fi

# ---------------------------------------------------------------------------
# brute force — reported, never blocking
# ---------------------------------------------------------------------------
# OFF BY DEFAULT, and this is the important part: App\Http\Middleware\
# BlockFailedVerifications locks the 'login' identifier (IP + email/username,
# see LoginController::getLoginRateLimitKey) out after 5 failed attempts, for an
# escalating 30/60/180/360 minutes. A burst from CI therefore locks the very
# account the browser probe and the rest of this round are about to log in
# with, and every check after it then fails on a wrongly-locked account — a
# whole round of false failures caused by the round itself.
#
# Set QA_PROBE_BRUTE_FORCE=1 to run it, ideally in a dedicated build that does
# nothing else.
if [[ "${QA_PROBE_BRUTE_FORCE:-0}" != "1" ]]; then
  probe_skip SEC-21 "$D" 'Login rate-limit check not run (off by default)' \
    'a failed-login burst locks the account the rest of the round logs in with — set QA_PROBE_BRUTE_FORCE=1 in a build that does nothing else'
else
  brute_jar=$(mktemp)
  brute_page=$(probe_curl -c "$brute_jar" -b "$brute_jar" "${base}/login")
  brute_token=$(grep -oE 'name="csrf-token" content="[^"]+"' <<<"$brute_page" | head -1 | sed 's/.*content="//; s/"$//')
  brute_pot=$(grep -oE 'type="text" name="login\[[^]]+\]"' <<<"$brute_page" | head -1 \
              | grep -oE 'login\[[^]]+\]' | sed 's/^login\[//; s/\]$//')
  brute_time_field=$(grep -oE 'type="hidden" name="login\[[^]]+\]" value="[^"]*"' <<<"$brute_page" | head -1)
  brute_time_name=$(grep -oE 'login\[[^]]+\]' <<<"$brute_time_field" | sed 's/^login\[//; s/\]$//')
  brute_time_value=$(grep -oE 'value="[^"]*"' <<<"$brute_time_field" | sed 's/^value="//; s/"$//')
  sleep 2

  if [[ -z "$brute_token" || -z "$brute_pot" || -z "$brute_time_name" ]]; then
    probe_unchecked SEC-21 "$D" 'Login rate-limit check did not run' \
      'could not find the CSRF token or honeypot fields on /login'
  else
    throttled=false; attempts=0
    for _ in $(seq 1 8); do
      attempts=$(( attempts + 1 ))
      code=$(probe_code "${base}/login" -X POST -b "$brute_jar" -c "$brute_jar" \
              -H "X-CSRF-TOKEN: ${brute_token}" -H 'Accept: application/json' \
              --data-urlencode "email_username=qa-probe@example.com" \
              --data-urlencode 'password1=definitely-wrong' \
              --data-urlencode "login[${brute_pot}]=" \
              --data-urlencode "login[${brute_time_name}]=${brute_time_value}")
      [[ "$code" == "429" ]] && { throttled=true; break; }
    done

    if $throttled; then
      probe_pass SEC-21 "$D" medium false 'Failed logins are rate limited' \
        "HTTP 429 after ${attempts} attempts"
    else
      probe_warn SEC-21 "$D" medium "${attempts} failed logins in a row were not rate limited" \
        'expected HTTP 429 within 5 attempts — App\Http\Middleware\BlockFailedVerifications'
    fi
  fi
  rm -f "$brute_jar"
fi

# ---------------------------------------------------------------------------
# Dusk's test-login route — reported, never blocking
# ---------------------------------------------------------------------------
# GET /_dusk/login/{userId} logs in as any user with no password. laravel/dusk
# registers it on every environment EXCEPT production, so it is present and
# expected on this disposable instance — the probes and the browser suite use
# it (see lib.sh's probe_login). It is still worth stating on every round,
# because the same code on a host with APP_ENV set to anything but production
# is a complete authentication bypass.
dusk_route_code=$(probe_code "${base}/_dusk/login/1")
if [[ "$dusk_route_code" == "404" ]]; then
  probe_pass SEC-24 "$D" high false "Dusk's test-login route is not registered" \
    'the app is running as production, or Dusk is not installed'
else
  probe_warn SEC-24 "$D" high "Dusk's password-free login route answers HTTP ${dusk_route_code}" \
    'expected on a testing instance. On any real deployment APP_ENV MUST be production, or this is a full authentication bypass.'
fi

# ---------------------------------------------------------------------------
# authorisation — blocking. The one class of finding that is unambiguous and
# that only a running instance can establish: a client (role=user) session
# reaching admin-only data.
# ---------------------------------------------------------------------------

# All three require the `admin` middleware, which App\Http\Middleware\Admin
# applies by role: role=='admin' passes through, role=='user' is redirected (to
# a session URL or the default page — never a 200 with the requested JSON), and
# anything else is logged out. There is no permission table to fall out of sync
# with — role is a plain column on users — so a 200 with real data here is
# unambiguous: the endpoint's own admin middleware did not run or did not hold.
admin_only=(
  '/get-orders'
  '/get-invoices'
  '/get-plans'
)

if [[ -n "${QA_CLIENT_EMAIL:-}" && -n "${QA_CLIENT_PASSWORD:-}" ]] \
   && probe_login "$base" "$QA_CLIENT_EMAIL" "$QA_CLIENT_PASSWORD" "$jar_client"; then
  escalations=()
  for path in "${admin_only[@]}"; do
    code=$(probe_code "${base}${path}" -b "$jar_client")
    body=$(probe_body "${base}${path}" -b "$jar_client")
    if [[ "$code" == "200" ]] && jq -e '(.data? // .) | (type == "array" and length > 0) or (type == "object" and length > 0)' \
         >/dev/null 2>&1 <<<"$body"; then
      escalations+=("${path} -> 200 with data")
    fi
  done
  if (( ${#escalations[@]} )); then
    probe_fail SEC-22 "$D" critical true 'A client (role=user) session reached admin-only endpoints' \
      "$(printf '%s; ' "${escalations[@]}")"
  else
    probe_pass SEC-22 "$D" critical true 'Admin-only endpoints reject a client (role=user) session' \
      "checked ${#admin_only[@]} endpoints"
  fi
else
  probe_unchecked SEC-22 "$D" 'Role-boundary check did not run' \
    'no client credentials, or the client login failed — set QA_CLIENT_EMAIL / QA_CLIENT_PASSWORD'
fi

# A session must not survive logout: the one session-management failure that is
# both common and unambiguous. Logout here is GET /auth/logout (LoginController
# inherits AuthenticatesUsers::logout; the constructor excepts 'logout' from the
# 'guest' middleware, and it is a GET route so — unlike a POST — no CSRF token
# is needed to exercise it).
if [[ -n "${QA_ADMIN_EMAIL:-}" && -n "${QA_ADMIN_PASSWORD:-}" ]] \
   && probe_login "$base" "$QA_ADMIN_EMAIL" "$QA_ADMIN_PASSWORD" "$jar_admin"; then
  before=$(probe_code "${base}${PROBE_AUTH_CHECK_PATH}" -b "$jar_admin")
  probe_curl -o /dev/null -b "$jar_admin" -c "$jar_admin" "${base}/auth/logout" >/dev/null 2>&1 || true
  after=$(probe_code "${base}${PROBE_AUTH_CHECK_PATH}" -b "$jar_admin")

  if [[ "$before" != "200" ]]; then
    probe_unchecked SEC-23 "$D" 'Logout check did not run' \
      "the authenticated probe endpoint returned ${before} while logged in, so the check proves nothing"
  elif [[ "$after" == "200" ]]; then
    probe_fail SEC-23 "$D" high true 'The session still authenticates after logout' \
      "GET ${PROBE_AUTH_CHECK_PATH} was ${before} before logout, and still ${after} after GET /auth/logout"
  else
    probe_pass SEC-23 "$D" high true 'Logout invalidates the session' \
      "${before} before, ${after} after"
  fi
else
  probe_unchecked SEC-23 "$D" 'Logout check did not run' 'no admin credentials, or the admin login failed'
fi

printf 'security probe: %s checks\n' "$(wc -l < "$out")" >&2
exit 0
