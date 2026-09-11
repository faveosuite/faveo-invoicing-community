#!/usr/bin/env bash
# Build a navigation map of the application for the authoring agent.
#
#   ci/qa/code-map.sh <out-dir>
#
# Without this the agent greps a several-hundred-route billing app blind. Worse,
# blind grep answers some questions badly:
#
#   * it reads a controller and guesses the URL, instead of reading the route
#   * it reads a Vue template and invents the button label, when the label is a
#     lang() key that resolves to something else entirely — a step that says
#     "click Save" against a button that says "Update" wastes a whole run
#   * it cannot see which middleware guards a route, so a "log in as a client and
#     try to open X" case ends up pointed at an admin-only page, or vice versa
#   * it guesses at a FormRequest's validation rules instead of reading them, so
#     a "reject invalid input" case asserts a message the field never produces
#
# Four indexes, all GENERATED from the tree being tested, so they cannot go stale:
#
#   routes.txt       METHOD  path  target  guards        — from `route:list`
#   permissions.txt  which guard (admin / auth / guest-only / public) covers
#                    each controller's routes — derived from the same dump
#   requests.txt     FormRequest class -> its validation rules
#   labels.txt       lang key -> the exact user-facing string
#
# billing has no permission/role sub-system the way a helpdesk has agent/
# department permissions — `users.role` is just 'admin' or 'user' — so
# permissions.txt is NOT a list of permission keys (there are none to list). It is
# a short derived summary of route-level access, because that IS the thing this
# app gates on.
#
# routes.txt is built from `php artisan route:list --json`, not a regex over
# routes/*.php: several panel controllers (Order\OrderController,
# User\ClientController, Product\ProductController, ...) apply auth/admin from
# their OWN constructor via $this->middleware(...) rather than on the route
# itself, and route:list is the only thing that resolves that correctly — a
# regex over routes/web.php would report those routes as unguarded.

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
root="$(git -C "$here" rev-parse --show-toplevel 2>/dev/null)" || root="$(cd "${here}/../.." && pwd)"
out_dir="${1:?usage: code-map.sh <out-dir>}"
mkdir -p "$out_dir"
cd "$root" || exit 0

# ---------------------------------------------------------------------------
# routes.txt + permissions.txt — one `route:list` dump feeds both, so the two
# files can never disagree with each other about what guards what.
# ---------------------------------------------------------------------------
route_json="$(php artisan route:list --json 2>/dev/null)"

if [[ -n "$route_json" ]]; then
  work="$(mktemp -d)"
  trap 'rm -rf "$work"' EXIT
  # Written to a temp file, not passed to `php -r '...'`: the PHP below reads
  # array keys like $r['method'], and PHP's single quotes inside a bash
  # single-quoted -r string close the bash string early. A heredoc file has no
  # such conflict.
  cat > "${work}/route-map.php" <<'PHPEOF'
<?php
    $routes = json_decode(file_get_contents("php://stdin"), true) ?: [];

    // Route::middleware() controller calls are reported unresolved (the literal
    // alias string, e.g. "admin"); framework-applied ones come back as the fully
    // qualified class. Map the ones worth naming; anything else prints as-is.
    $alias = [
      "Illuminate\\Auth\\Middleware\\Authenticate"        => "auth",
      "App\\Http\\Middleware\\Admin"                      => "admin",
      "App\\Http\\Middleware\\Install"                    => "installAgora",
      "App\\Http\\Middleware\\IsInstalled"                => "isInstalled",
      "App\\Http\\Middleware\\RedirectIfAuthenticated"    => "guest",
      "App\\Http\\Middleware\\VerifyThirdPartyApps"       => "validateThirdParty",
      "Illuminate\\Routing\\Middleware\\ValidateSignature" => "signed",
    ];

    $routesFh = fopen($argv[1], "w");
    $permsFh  = fopen($argv[2], "w");
    fwrite($routesFh, "# METHOD\tpath\ttarget\tguards\n");

    $buckets = []; // controller/closure -> [guard => route count]

    foreach ($routes as $r) {
      // "web" is the blanket group every non-API route carries; every route
      // here has it, so naming it on every line is noise, not signal.
      $mw = array_values(array_filter($r["middleware"] ?? [], fn ($m) => $m !== "web"));
      $mw = array_map(fn ($m) => $alias[$m] ?? $m, $mw);
      $guards = $mw ? implode(",", $mw) : "-";
      $target = ($r["action"] ?? "") === "Closure" ? "closure" : $r["action"];

      fwrite($routesFh, "{$r['method']}\t{$r['uri']}\t{$target}\t{$guards}\n");

      if (in_array("admin", $mw, true)) {
        $bucket = "admin";
      } elseif (in_array("auth", $mw, true)) {
        $bucket = "auth";
      } elseif (in_array("guest", $mw, true)) {
        $bucket = "guest-only";        // e.g. login/register — blocked once logged in
      } else {
        $bucket = "public";
      }

      $group = $target === "closure" ? "closure:" . $r["uri"] : $target;
      $buckets[$group][$bucket] = ($buckets[$group][$bucket] ?? 0) + 1;
    }

    fwrite($permsFh, "# billing has no permission-key sub-system — users.role is just admin|user.\n");
    fwrite($permsFh, "# this is a derived index of which guard covers each controller's routes.\n");
    fwrite($permsFh, "# admin = requires an admin user; auth = any logged-in user; guest-only = only\n");
    fwrite($permsFh, "# reachable when NOT logged in (login/register); public = no auth required.\n");
    fwrite($permsFh, "# controller/action target\tguard\troute count\n");
    ksort($buckets);
    foreach ($buckets as $group => $g) {
      foreach ($g as $bucket => $count) {
        fwrite($permsFh, "{$group}\t{$bucket}\t{$count}\n");
      }
    }
PHPEOF
  php "${work}/route-map.php" "${out_dir}/routes.txt" "${out_dir}/permissions.txt" <<< "$route_json"
  rm -rf "$work"
  trap - EXIT
else
  printf '# `php artisan route:list --json` produced no output — see code-map.sh stderr\n' > "${out_dir}/routes.txt"
  printf '# skipped — routes.txt could not be generated\n' > "${out_dir}/permissions.txt"
  printf 'code-map: WARNING php artisan route:list failed; routes.txt/permissions.txt are empty\n' >&2
fi

# ---------------------------------------------------------------------------
# requests.txt — every FormRequest's rules(), for every case that exercises
# validation ("submit the order form with no client selected", etc).
#
# Extracted with PHP's own tokenizer rather than a brace-counting regex: a rule
# string can legitimately contain '{' or '}' (e.g. ":min}" style Laravel
# placeholders don't, but a custom message might), and the tokenizer already
# knows the difference between a string literal and real code structure.
# ---------------------------------------------------------------------------
{
  printf '# FormRequest file\trule line\n'
  find app -name '*Request.php' 2>/dev/null | sort | while IFS= read -r f; do
    php -r '
      $src = file_get_contents($argv[1]);
      $tokens = token_get_all($src);
      $n = count($tokens);
      for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];
        if (!is_array($t) || $t[0] !== T_STRING || $t[1] !== "rules") continue;
        // only the FUNCTION named rules(), not some unrelated use of the word
        $k = $i - 1;
        while ($k >= 0 && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) $k--;
        if ($k < 0 || !is_array($tokens[$k]) || $tokens[$k][0] !== T_FUNCTION) continue;

        $j = $i;
        while ($j < $n && $tokens[$j] !== "{") $j++;
        if ($j >= $n) break;

        $depth = 0; $body = "";
        for (; $j < $n; $j++) {
          $tok = $tokens[$j];
          $text = is_array($tok) ? $tok[1] : $tok;
          if ($text === "{") $depth++;
          if ($text === "}") { $depth--; if ($depth === 0) break; }
          if ($depth >= 1) $body .= $text;
        }
        foreach (explode("\n", $body) as $line) {
          $line = trim($line);
          if ($line !== "" && strpos($line, "=>") !== false) {
            echo $argv[1] . "\t" . $line . "\n";
          }
        }
        break; // one rules() method per class
      }
    ' "$f"
  done
} > "${out_dir}/requests.txt" 2>/dev/null

# ---------------------------------------------------------------------------
# labels.txt — lang key to the string a person actually sees. lang/en/ has
# several files; scan the flat ones directly under lang/en/ — the app only
# ever resolves lang/{locale}/{file}.php, so the stray lang/en/en/ and
# lang/en-gb/en/ nested copies are dead weight, skipped.
#
# Single-line `'key' => 'value'` entries only — a multi-line value (long HTML
# strings, nested arrays like some of validation.php's size-rule variants) is
# skipped rather than mis-captured.
# ---------------------------------------------------------------------------
{
  printf '# lang key\tuser-facing text\n'
  for f in lang/en/*.php; do
    [[ -f "$f" ]] || continue
    grep -oP "'[a-zA-Z0-9_.-]+'\s*=>\s*'(?:[^'\\\\]|\\\\.)*'" "$f" \
      | sed -E "s/^'([^']+)'[[:space:]]*=>[[:space:]]*'(.*)'\$/\1\t\2/"
  done
} > "${out_dir}/labels.txt" 2>/dev/null

printf 'code-map: routes=%s permissions=%s rules=%s labels=%s (in %s)\n' \
  "$(( $(wc -l < "${out_dir}/routes.txt") - 1 ))" \
  "$(( $(wc -l < "${out_dir}/permissions.txt") - 1 ))" \
  "$(( $(wc -l < "${out_dir}/requests.txt") - 1 ))" \
  "$(( $(wc -l < "${out_dir}/labels.txt") - 1 ))" \
  "$out_dir" >&2
