#!/usr/bin/env php
<?php

/**
 * Seed the two accounts the first-round pipeline tests with, on the disposable
 * per-PR instance.
 *
 *   QA_ADMIN_EMAIL=... QA_ADMIN_PASSWORD=... \
 *   QA_CLIENT_EMAIL=... QA_CLIENT_PASSWORD=... \
 *   php ci/qa/seed-qa-users.php
 *
 * Why only two accounts: billing has no ticket-routing concept, so there is
 * no "in scope of a department" identity to distinguish from an "out of
 * scope" one, and no first-response-time trap that needs a second responder
 * distinct from the requester. `users.role` is just `'admin'` or `'user'`
 * (checked directly by app/Http/Middleware/Admin.php) — there is no
 * `role_associations` pivot and no department table to populate, so this
 * script is a plain `User::updateOrCreate`, not a multi-table write.
 *
 * Why a script rather than reusing the seeded demo admin
 * (database/seeders/v2_0_0/DatabaseSeeder.php's `demo@admin.com` / `password`):
 * that password is hardcoded in the repo and shared by every environment ever
 * provisioned with `testing-setup` — not something a round that deliberately
 * raises limits and logs in repeatedly should depend on. This creates its own
 * admin (and the client login billing has no seeded equivalent of at all)
 * from whatever credentials the pipeline generated for this build alone.
 *
 * Run against a freshly migrated, DISPOSABLE database only — it writes users.
 * The pipeline calls it on the per-build instance it just created and drops
 * afterwards.
 */

/*
 * QA_APP_ROOT, when set, is the application being tested — which is NOT this
 * script's own directory once the pipeline runs from a snapshot of ci/qa taken
 * before the workspace was switched to the pull request's code (see
 * executeForPr's tools-snapshot step in the pipeline script).
 */
$appRoot = getenv('QA_APP_ROOT') ?: __DIR__.'/../..';

require $appRoot.'/vendor/autoload.php';

$app = require_once $appRoot.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\User;
use Illuminate\Support\Facades\Hash;

function env_required(string $key): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        fwrite(STDERR, "seed-qa-users: {$key} is not set\n");
        exit(1);
    }

    return $value;
}

/*
 * Every column the users table has NO NULL default for (see
 * database/migrations/2017_06_10_062630_create_users_table.php), filled with the
 * same placeholder values database/seeders/v2_0_0/DatabaseSeeder.php's own admin
 * row uses — not because those values matter to any case, but because a NULL in
 * one of them fails the insert outright and a wrong-but-present value never does.
 *
 * mobile_verified/email_verified are set to 1 defensively even though
 * LoginController::userNeedVerified only actually enforces them when the
 * `emailverification_status` / `msg91_status` settings are on (both seeded off) —
 * a screen elsewhere in the app may still branch on them, and there is no cost to
 * having them already satisfied. is_2fa_enabled=0 so login never stops at the
 * verify-2fa screen the executor has no code for.
 */
function ensure_user(string $email, string $password, string $role, string $userName): int
{
    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'user_name' => $userName,
            'first_name' => 'QA',
            'last_name' => ucfirst($role),
            'password' => Hash::make($password),
            'company' => 'QA Instance',
            'mobile' => '',
            'mobile_code' => '',
            'address' => '',
            'town' => '',
            'country' => 'IN',
            'state' => 'IN-KA',
            'zip' => '',
            'currency' => 'INR',
            'role' => $role,
            'active' => 1,
            'mobile_verified' => 1,
            'email_verified' => 1,
            'is_2fa_enabled' => 0,
        ]
    );

    printf("seed-qa-users: %s ready as '%s' (id %d)\n", $email, $role, $user->id);

    return (int) $user->id;
}

$cast = [
    'admin' => ['email' => env_required('QA_ADMIN_EMAIL'),  'password' => env_required('QA_ADMIN_PASSWORD'),  'role' => 'admin', 'user_name' => 'qa_admin'],
    'client' => ['email' => env_required('QA_CLIENT_EMAIL'), 'password' => env_required('QA_CLIENT_PASSWORD'), 'role' => 'user',  'user_name' => 'qa_client'],
];

$ids = [];
foreach ($cast as $name => $member) {
    $ids[$name] = ensure_user($member['email'], $member['password'], $member['role'], $member['user_name']);
}

/*
 * Publish the ids so the probes and the browser suite can use Dusk's
 * session-bypass route (GET /_dusk/login/{userId}, registered by
 * DuskServiceProvider on any non-production environment) instead of posting the
 * login form — one GET, one cookie, nothing that depends on CSRF or a form field
 * name matching what the probe guessed.
 */
$usersFile = getenv('QA_USERS_FILE');

if ($usersFile) {
    $published = [];
    foreach ($ids as $name => $id) {
        $published[$name] = [
            'email' => $cast[$name]['email'],
            'id' => $id,
            'role' => $cast[$name]['role'],
        ];
    }

    file_put_contents($usersFile, json_encode($published, JSON_PRETTY_PRINT));
    printf("seed-qa-users: ids written to %s\n", $usersFile);
}
