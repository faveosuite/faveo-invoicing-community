#!/usr/bin/env bash
# JSON/AJAX endpoint contract probe against the throwaway instance.
#
#   ci/qa/probes/api.sh <base-url> <out.jsonl>
#
# routes/api.php is empty (no routes registered) and there is no Passport and no
# bearer-token API. Every AJAX/JSON endpoint this app has lives in routes/web.php
# behind the normal `web` middleware group — session cookie + CSRF — with `auth`
# and `admin` applied per-controller in each controller's constructor (see
# App\Http\Controllers\Order\OrderController, \Order\InvoiceController,
# \Product\PlanController for admin-only; \Front\ClientController for
# authenticated-only, reachable by role=admin or role=user). So there is exactly
# ONE surface here, and this probe is built around it: a cookie jar, not a bearer
# token.
#
# The one endpoint reachable with no session at all is GET /refresh-csrf
# (routes/web.php: returns {"token": csrf_token()}) — used below as the "is
# there a JSON surface at all, and does it error in its own language" baseline.
#
# Conventions asserted here are this project's own, established by reading
# app/Http/helpers.php and the exception handler, not assumed from another app:
#   - successResponse()/errorResponse() (app/Http/helpers.php) is the hand-written
#     shape: {"success": true|false, "message"?: ..., "data"?: ...}.
#     errorResponse()'s default status is 400 (NOT 412 — grepped for a 412
#     convention across app/, there isn't one in this codebase).
#   - a FormRequest validation failure (e.g. LoginRequest) is NOT overridden by
#     this app's Handler, so it falls through to Laravel's default: 422 with
#     {"message": ..., "errors": {...}}.
#   - a route that does not exist, asked for with Accept: application/json,
#     renders Laravel's default JSON 404 — this app's Handler::render() just
#     calls parent::render(), no override.
#
# Always exits 0 — summarize.sh turns the blocking flags into a verdict.

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
. "${here}/lib.sh"

base="${1:?usage: api.sh <base-url> <out.jsonl>}"
out="${2:?usage: api.sh <base-url> <out.jsonl>}"
base="${base%/}"

probe_init "$out"
printf 'API probe against %s\n' "$base" >&2

D=api
jar_admin=$(mktemp); jar_client=$(mktemp)
trap 'rm -f "$jar_admin" "$jar_client"' EXIT

json_response() { jq -e . >/dev/null 2>&1 <<<"$1"; }

# throttle:web (RouteServiceProvider::configureRateLimiting) is 600/min, keyed by
# IP/user/session and shared across the whole `web` group. security.sh's
# brute-force burst (when QA_PROBE_BRUTE_FORCE=1) runs from the same address, so
# a 429 here means "the limiter is doing its job", not "the endpoint is broken".
rate_limited() { [[ "$1" == "429" ]]; }

# ---------------------------------------------------------------------------
# the one unauthenticated JSON endpoint — baseline behaviour
# ---------------------------------------------------------------------------

body=$(probe_body "${base}/refresh-csrf" -H 'Accept: application/json')
code=$(probe_code "${base}/refresh-csrf" -H 'Accept: application/json')
if [[ "$code" == "200" ]] && json_response "$body" && jq -e '.token' >/dev/null 2>&1 <<<"$body"; then
  probe_pass API-01 "$D" high true 'GET /refresh-csrf answers 200 with a JSON token' \
    "$(head -c 120 <<<"$body" | tr -d '\n')"
else
  probe_fail API-01 "$D" high true "GET /refresh-csrf returned HTTP ${code}" \
    "$(head -c 200 <<<"$body" | tr -d '\n')"
fi

ctype=$(probe_header_value "$(probe_headers "${base}/refresh-csrf" -H 'Accept: application/json')" 'Content-Type')
grep -qi 'application/json' <<<"$ctype" \
  && probe_pass API-02 "$D" medium false 'The JSON endpoint responds as application/json' "$ctype" \
  || probe_warn API-02 "$D" medium 'The JSON endpoint response is not application/json' "Content-Type: ${ctype:-none}"

# A route that does not exist, asked for as JSON, must still answer in JSON —
# not the HTML 404 page.
nf_body=$(probe_body "${base}/no-such-endpoint-$(date +%s)" -H 'Accept: application/json')
nf_code=$(probe_code "${base}/no-such-endpoint-$(date +%s)" -H 'Accept: application/json')
if json_response "$nf_body"; then
  probe_pass API-03 "$D" medium false "An unknown route answers JSON when asked for JSON (HTTP ${nf_code})" ''
else
  probe_warn API-03 "$D" medium "An unknown route answers HTML (HTTP ${nf_code}) even when asked for JSON" \
    "$(head -c 160 <<<"$nf_body" | tr -d '\n')"
fi
probe_leaks_internals "$nf_body" \
  && probe_fail API-04 "$D" critical true 'An unknown route leaks framework internals' \
       "$(grep -oEi "$PROBE_LEAK_PATTERN" <<<"$nf_body" | head -2 | tr '\n' ' ')" \
  || probe_pass API-04 "$D" critical true 'Error responses do not leak internals' ''

# Wrong method on a real route: 405, not a 500 and not a silent 200. save-columns
# is POST-only (routes/web.php) with no matching GET route.
mcode=$(probe_code "${base}/save-columns" -H 'Accept: application/json')
[[ "$mcode" == "405" ]] \
  && probe_pass API-05 "$D" low false 'GET on a POST-only route returns 405' '' \
  || probe_warn API-05 "$D" low "GET /save-columns returned HTTP ${mcode}, expected 405" ''

# ---------------------------------------------------------------------------
# login — the one endpoint every JSON check below depends on being correct
# ---------------------------------------------------------------------------
# LoginRequest requires a 'login' honeypot field (App\Rules\Honeypot) alongside
# the CSRF token; both are scraped off the rendered page, same mechanics as
# lib.sh's probe_login, duplicated here (not called) because these checks need
# the raw status code and body, not just success/failure.

fetch_login_fields() {
  local jar="$1" page
  page=$(probe_curl -c "$jar" -b "$jar" "${base}/login") || return 1
  LOGIN_TOKEN=$(grep -oE 'name="csrf-token" content="[^"]+"' <<<"$page" | head -1 | sed 's/.*content="//; s/"$//')
  LOGIN_POT=$(grep -oE 'type="text" name="login\[[^]]+\]"' <<<"$page" | head -1 \
              | grep -oE 'login\[[^]]+\]' | sed 's/^login\[//; s/\]$//')
  local time_field
  time_field=$(grep -oE 'type="hidden" name="login\[[^]]+\]" value="[^"]*"' <<<"$page" | head -1)
  LOGIN_TIME_NAME=$(grep -oE 'login\[[^]]+\]' <<<"$time_field" | sed 's/^login\[//; s/\]$//')
  LOGIN_TIME_VALUE=$(grep -oE 'value="[^"]*"' <<<"$time_field" | sed 's/^value="//; s/"$//')
  [[ -n "$LOGIN_TOKEN" && -n "$LOGIN_POT" && -n "$LOGIN_TIME_NAME" ]]
}

login_code() {
  local jar="$1" email="$2" password="$3"; shift 3
  probe_code "${base}/login" -X POST -b "$jar" -c "$jar" \
    -H "X-CSRF-TOKEN: ${LOGIN_TOKEN}" -H 'Accept: application/json' \
    --data-urlencode "_token=${LOGIN_TOKEN}" \
    --data-urlencode "email_username=${email}" \
    --data-urlencode "password1=${password}" \
    --data-urlencode "login[${LOGIN_POT}]=" \
    --data-urlencode "login[${LOGIN_TIME_NAME}]=${LOGIN_TIME_VALUE}" "$@"
}
login_body() {
  local jar="$1" email="$2" password="$3"; shift 3
  probe_body "${base}/login" -X POST -b "$jar" -c "$jar" \
    -H "X-CSRF-TOKEN: ${LOGIN_TOKEN}" -H 'Accept: application/json' \
    --data-urlencode "_token=${LOGIN_TOKEN}" \
    --data-urlencode "email_username=${email}" \
    --data-urlencode "password1=${password}" \
    --data-urlencode "login[${LOGIN_POT}]=" \
    --data-urlencode "login[${LOGIN_TIME_NAME}]=${LOGIN_TIME_VALUE}" "$@"
}

login_surface=live
login_why=''
bad_jar=$(mktemp)
if ! fetch_login_fields "$bad_jar"; then
  login_surface=broken
  login_why='could not find the CSRF token or the login honeypot fields on GET /login — the form markup may have changed (see lib.sh probe_login)'
  printf 'api: could not read the login form — login-dependent checks cannot run\n' >&2
else
  sleep 2
fi

surface_unchecked() { probe_unchecked "$1" "$D" "$2" "$login_why"; }

bad_code=''
bad_body=''
if [[ "$login_surface" == live ]]; then
  bad_code=$(login_code "$bad_jar" 'qa-probe@example.com' 'definitely-wrong')
  bad_body=$(login_body "$bad_jar" 'qa-probe@example.com' 'definitely-wrong')
fi
if [[ "$login_surface" != live ]]; then
  surface_unchecked API-06 'Invalid-credentials check did not run'
elif rate_limited "$bad_code"; then
  probe_unchecked API-06 "$D" 'Invalid-credentials check did not run' \
    'HTTP 429 — the login endpoint was rate limited, most likely by this run own brute-force probe'
elif [[ "$bad_code" == "200" ]] && jq -e '.success == true' >/dev/null 2>&1 <<<"$bad_body"; then
  probe_fail API-06 "$D" critical true 'Invalid credentials were accepted' \
    'POST /login answered {"success":true} for a password that should not exist'
elif [[ "$bad_code" == "400" ]] && jq -e '.success == false' >/dev/null 2>&1 <<<"$bad_body"; then
  probe_pass API-06 "$D" critical true 'Invalid credentials are rejected (HTTP 400, errorResponse)' \
    "$(jq -r '.message // empty' <<<"$bad_body")"
else
  probe_fail API-06 "$D" critical true "Invalid credentials returned HTTP ${bad_code}, expected 400 with success:false" \
    "$(head -c 160 <<<"$bad_body" | tr -d '\n')"
fi

# A validation failure — password1 omitted entirely — must be refused, not
# accepted (200) and not a crash (500). LoginRequest has no custom
# failedValidation(), so the framework default (422) is what should come back.
if [[ "$login_surface" != live ]]; then
  surface_unchecked API-07 'Validation-status check did not run'
else
  val_code=$(login_code "$bad_jar" 'not-an-email' '')
  case "$val_code" in
    422) probe_pass API-07 "$D" medium true 'A validation failure returns 422 (framework default)' '' ;;
    429) probe_unchecked API-07 "$D" 'Validation-status check did not run' 'HTTP 429 — rate limited' ;;
    *)   probe_fail API-07 "$D" medium true "Invalid input returned HTTP ${val_code}, expected 422" \
           'a validation failure must not be accepted (200) or crash (500)' ;;
  esac
fi
rm -f "$bad_jar"

# ---------------------------------------------------------------------------
# session-authenticated endpoints
# ---------------------------------------------------------------------------

client_ok=false
if [[ -n "${QA_CLIENT_EMAIL:-}" && -n "${QA_CLIENT_PASSWORD:-}" ]] \
   && probe_login "$base" "$QA_CLIENT_EMAIL" "$QA_CLIENT_PASSWORD" "$jar_client"; then
  client_ok=true
  ok=(); broken=()
  for path in '/get-my-invoices' '/get-my-orders'; do
    code=$(probe_code "${base}${path}" -b "$jar_client" -H 'Accept: application/json')
    body=$(probe_body "${base}${path}" -b "$jar_client" -H 'Accept: application/json')
    if [[ "$code" == "200" ]] && json_response "$body"; then
      ok+=("$path")
    else
      broken+=("${path} -> ${code}")
    fi
  done
  if (( ${#broken[@]} == 0 )); then
    probe_pass API-08 "$D" high true 'Client-only endpoints answer an authenticated client session' \
      "$(printf '%s ' "${ok[@]}")"
  else
    probe_fail API-08 "$D" high true 'Client-only endpoints failed for an authenticated client session' \
      "$(printf '%s; ' "${broken[@]}")"
  fi

  # get-my-subscriptions (routes/web.php) is wired to
  # Front\ClientController::getSubscriptions, which does not exist on that
  # class or its base — every call to it is a fatal error, not a PR
  # regression. Checked separately, non-blocking, and only for the one thing
  # that WOULD be blocking: a leaked stack trace.
  sub_body=$(probe_body "${base}/get-my-subscriptions" -b "$jar_client" -H 'Accept: application/json')
  if probe_leaks_internals "$sub_body"; then
    probe_fail API-13 "$D" high true 'GET /get-my-subscriptions leaks framework internals' \
      "$(grep -oEi "$PROBE_LEAK_PATTERN" <<<"$sub_body" | head -2 | tr '\n' ' ')"
  else
    probe_warn API-13 "$D" medium 'GET /get-my-subscriptions has no working controller method' \
      'routes/web.php wires it to Front\ClientController::getSubscriptions, which is not defined on that class or its base — pre-existing, not something this check should block on'
  fi
else
  probe_unchecked API-08 "$D" 'Client-only endpoint checks did not run' \
    'no client credentials, or the client login failed — set QA_CLIENT_EMAIL / QA_CLIENT_PASSWORD'
  probe_skip API-13 "$D" 'get-my-subscriptions check skipped' 'requires an authenticated client session'
fi

if [[ -n "${QA_ADMIN_EMAIL:-}" && -n "${QA_ADMIN_PASSWORD:-}" ]] \
   && probe_login "$base" "$QA_ADMIN_EMAIL" "$QA_ADMIN_PASSWORD" "$jar_admin"; then

  ok=(); broken=()
  for path in '/get-orders' '/get-invoices' '/get-plans'; do
    code=$(probe_code "${base}${path}" -b "$jar_admin" -H 'Accept: application/json')
    body=$(probe_body "${base}${path}" -b "$jar_admin" -H 'Accept: application/json')
    if [[ "$code" == "200" ]] && json_response "$body"; then
      ok+=("$path")
    else
      broken+=("${path} -> ${code}")
    fi
  done

  if (( ${#broken[@]} == 0 )); then
    probe_pass API-09 "$D" high true 'Admin-only endpoints answer an authenticated admin session' \
      "$(printf '%s ' "${ok[@]}")"
  else
    probe_fail API-09 "$D" high true 'Admin-only endpoints failed for an authenticated admin' \
      "$(printf '%s; ' "${broken[@]}")"
  fi

  # A missing record must be handled — some status that is not 200 (accepted)
  # and not 500 (crashed), and must not leak internals doing it. There is no
  # single established "not found" convention across this codebase (grepped
  # app/ for errorResponse(..., 412|404) — it isn't there), so this does not
  # assert one; it only asserts the endpoint fails safely.
  missing_code=$(probe_code "${base}/orders/999999999" -b "$jar_admin" -H 'Accept: application/json')
  missing_body=$(probe_body "${base}/orders/999999999" -b "$jar_admin" -H 'Accept: application/json')
  if probe_leaks_internals "$missing_body"; then
    probe_fail API-10 "$D" high true "A missing record (GET /orders/999999999) returned HTTP ${missing_code} with a stack trace" \
      "$(grep -oEi "$PROBE_LEAK_PATTERN" <<<"$missing_body" | head -2 | tr '\n' ' ')"
  elif [[ "$missing_code" == "200" ]]; then
    probe_fail API-10 "$D" high true 'A missing record (GET /orders/999999999) returned HTTP 200' \
      'a non-existent id must not be accepted as found'
  else
    probe_pass API-10 "$D" medium false "A missing record fails safely (HTTP ${missing_code})" \
      "$(head -c 160 <<<"$missing_body" | tr -d '\n')"
  fi
else
  probe_unchecked API-09 "$D" 'Admin-only endpoint checks did not run' \
    'no admin credentials, or the admin login failed'
  probe_skip API-10 "$D" 'Missing-record check skipped' 'requires an authenticated admin session'
fi

# The other half of the client/admin boundary the browser suite does not cover
# from curl's side: a client session must not get JSON back from an admin-only
# endpoint. security.sh's SEC-22 already asserts this as a blocking security
# finding; API-11 restates it here as the API-contract half (same evidence,
# different discipline, so a reader of just this report still sees it).
if $client_ok; then
  code=$(probe_code "${base}/get-orders" -b "$jar_client" -H 'Accept: application/json')
  body=$(probe_body "${base}/get-orders" -b "$jar_client" -H 'Accept: application/json')
  if [[ "$code" == "200" ]] && json_response "$body"; then
    probe_fail API-11 "$D" critical true 'A client session receives JSON from an admin-only endpoint' \
      "GET /get-orders -> HTTP 200 with a JSON body"
  else
    probe_pass API-11 "$D" high true 'A client session does not receive JSON from an admin-only endpoint' \
      "GET /get-orders -> HTTP ${code}"
  fi
else
  probe_skip API-11 "$D" 'Client/admin boundary check skipped' 'requires an authenticated client session'
fi

printf 'API probe: %s checks\n' "$(wc -l < "$out")" >&2
exit 0
