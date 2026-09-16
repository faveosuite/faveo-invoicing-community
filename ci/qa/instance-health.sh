#!/usr/bin/env bash
# Is the per-build instance actually usable before we test against it?
#
#   ci/qa/instance-health.sh <base-url>
#
# Exit codes:
#   0  healthy — serving, and the login page renders real form content
#   4  unusable — no answer, not installed, or the page came back blank
#
# There is no license gate anywhere in this app (grepped for it — there isn't
# one), so unlike a from-scratch reachability check, the only thing worth
# distinguishing from "serving" is:
#
# * A fresh database from `php artisan testing-setup` has DB_INSTALL unset, and
#   App\Http\Middleware\Install (aliased 'installAgora', wrapping nearly all of
#   routes/web.php) then redirects every one of those requests to /probe.php —
#   with a 302, which a naive reachability check reads as healthy. The round
#   would proceed and every other check would fail against an uninstalled app,
#   in a way that reads as "the PR broke everything" rather than "provisioning
#   did not finish".
#
# * This app's front-end (themes/default1) is server-rendered Blade with
#   checked-in static assets — there is no versioned JS bundle directory
#   anywhere under public/ (no public/build, no manifest, no package.json in
#   this repo) for a "did the front-end get built" check to key off of. So the
#   equivalent risk here is a page that renders but without its form content —
#   a partial/broken template render reads exactly like a blank page in a
#   browser — checked for directly, below.

set -uo pipefail

base="${1:?usage: instance-health.sh <base-url>}"
base="${base%/}"
curl_opts=(-sS --noproxy '*' --max-time 25 -A 'billing-qa-first-round/1.0')
[[ "${PROBE_INSECURE:-0}" == "1" ]] && curl_opts+=(-k)

# One request, both answers: the status after following redirects and where it
# ended up. `|| true` and a separator rather than a fallback string, because curl
# prints "000" itself on a connection failure and a second fallback would
# concatenate into "000000".
read -r root_code final_url < <(
  curl "${curl_opts[@]}" -o /dev/null -L -w '%{http_code} %{url_effective}\n' "${base}/" 2>/dev/null || true
)
root_code="${root_code:-000}"

case "$root_code" in
  2*|3*) ;;
  *) printf 'health: %s did not answer (HTTP %s)\n' "$base" "$root_code" >&2; exit 4 ;;
esac

# Where did / actually go? Report it, because the answer is the diagnosis.
if [[ "${final_url:-}" == *probe.php* ]]; then
  printf 'health: %s redirects to probe.php — the instance reports itself as NOT INSTALLED.\n' "$base" >&2
  printf 'health: App\\Http\\Middleware\\Install sends every request there while DB_INSTALL != 1 in .env.\n' >&2
  exit 4
fi

# The login page: GET /login on the SAME base URL. Route::auth() registers it
# (routes/web.php), served by LoginController::showLoginForm — a direct 200
# render, not a redirect, so following / to wherever it lands is not enough.
login_url="${base}/login"

login_page=$(mktemp)
# No `|| printf '000'`: curl already prints 000 through -w on a connection failure, and
# a second fallback concatenates into "000000" — which is exactly what this reported.
login_code=$(curl "${curl_opts[@]}" -L -o "$login_page" -w '%{http_code}' "$login_url" 2>/dev/null)
login_code="${login_code:-000}"
login_body=$(cat "$login_page" 2>/dev/null || printf '')
rm -f "$login_page"

if [[ -z "$login_body" ]]; then
  printf 'health: GET %s answered HTTP %s with an empty body (/ had redirected to %s)\n' \
    "$login_url" "$login_code" "${final_url:-?}" >&2
  exit 4
fi

if [[ "${final_url:-}" == *probe.php* || "$login_body" == *'action="probe.php"'* ]]; then
  printf 'health: the login page redirects to / references probe.php — not installed\n' >&2
  exit 4
fi

# Real content vs. a blank or half-rendered page: the login form's own fields
# (LoginRequest: email_username, password1 — see
# resources/views/themes/default1/front/auth/login-register.blade.php) and the
# CSRF meta tag every page in this layout carries. Missing either means the
# view broke rendering partway through, which looks identical to "blank page"
# in a browser even though curl got a 200.
if ! grep -q 'name="email_username"' <<<"$login_body" \
   || ! grep -q 'name="password1"' <<<"$login_body" \
   || ! grep -qE 'name="csrf-token" content="[^"]+"' <<<"$login_body"; then
  printf 'health: the login page answered HTTP %s but is missing its form fields or CSRF token (%s bytes of HTML) — the view rendered blank or broke partway through\n' \
    "$login_code" "${#login_body}" >&2
  exit 4
fi

printf 'health: %s is serving and the login page renders its form (%s bytes of HTML)\n' "$base" "${#login_body}"
exit 0
