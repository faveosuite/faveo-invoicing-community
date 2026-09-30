<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\Github\GithubApiController;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

class SentryRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentry:release {--environment=production} {--auth-token=} {--base=} {--delete} {--release-version=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create (or, with --delete, remove) a Sentry release for the current app.version';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $token = $this->option('auth-token') ?: config('services.sentry.auth_token');

        if (! $token) {
            $this->error('Sentry auth token is required: pass --auth-token= or set SENTRY_AUTH_TOKEN env var (Sentry -> Settings -> Auth Tokens).');

            return self::FAILURE;
        }

        $token = (string) $token;

        $versionOption = $this->option('release-version');
        $version = is_string($versionOption) && $versionOption !== '' ? $versionOption : config('app.version');

        if (! $version) {
            $this->error('app.version is not set in config/app.php. Pass --release-version= to specify one explicitly.');

            return self::FAILURE;
        }

        $version = (string) $version;
        $org = (string) config('services.sentry.org');
        $project = (string) config('services.sentry.project');
        $apiBase = $this->resolveApiBase();

        if ($this->option('delete')) {
            return $this->deleteRelease($token, $org, $apiBase, $version);
        }

        $environmentOption = $this->option('environment');
        $environment = is_string($environmentOption) ? $environmentOption : 'production';

        $this->info("Creating Sentry release {$version} for {$org}/{$project}...");

        $commits = [];

        $baseOption = $this->option('base');
        $base = is_string($baseOption) ? $baseOption : null;

        if ($base) {
            $commits = $this->fetchCommits($base);

            if ($commits === null) {
                return self::FAILURE;
            }

            $this->info('Found '.count($commits)." commits from {$base} to HEAD.");
        } else {
            $this->warn('No --base given; release will be created without commit association.');
        }

        $now = gmdate('Y-m-d\TH:i:s\Z');

        $payload = [
            'version' => $version,
            'projects' => [$project],
            'dateStarted' => $now,
            'dateReleased' => $now,
        ];

        if ($commits) {
            $payload['commits'] = $commits;
        }

        $sentry = Http::withToken($token)->baseUrl("{$apiBase}/api/0/organizations/{$org}");

        $release = $sentry->post('/releases/', $payload);

        if ($release->failed()) {
            $this->error("Failed to create release: {$release->status()} {$release->body()}");

            return self::FAILURE;
        }

        $commitCount = $release->json('commitCount') ?? count($commits);
        $this->info("Created and finalized release {$version} (commitCount: {$commitCount}).");

        $deploy = $sentry->post("/releases/{$version}/deploys/", [
            'environment' => $environment,
        ]);

        if ($deploy->failed()) {
            $this->error("Failed to create deploy: {$deploy->status()} {$deploy->body()}");

            return self::FAILURE;
        }

        $this->info("Done: release {$version} created, commits associated, deploy marked as {$environment}.");

        return self::SUCCESS;
    }

    /**
     * Permanently delete the release matching the current app.version.
     *
     * Requires a token with the `project:releases` or `project:admin` scope — the
     * default org:ci-scoped SENTRY_AUTH_TOKEN cannot do this, so pass --auth-token=
     * with a personal auth token that has one of those scopes.
     */
    private function deleteRelease(string $token, string $org, string $apiBase, string $version): int
    {
        $this->info("Deleting Sentry release {$version} for {$org}...");

        $response = Http::withToken($token)
            ->delete("{$apiBase}/api/0/organizations/{$org}/releases/{$version}/");

        if ($response->status() === 404) {
            $this->info("Did nothing. Release with this version ({$version}) does not exist.");

            return self::SUCCESS;
        }

        if ($response->failed()) {
            $this->error("Failed to delete release: {$response->status()} {$response->body()}");

            if ($response->status() === 403) {
                $this->error('This likely means the token lacks the project:releases/project:admin scope needed to delete releases. Pass --auth-token= with a personal auth token that has one of those scopes.');
            }

            return self::FAILURE;
        }

        $this->info("Deleted release {$version}.");

        return self::SUCCESS;
    }

    /**
     * Fetch the commit list (with per-commit patch_set, matching sentry-cli's own
     * GitCommit shape) between $base and HEAD from GitHub, for Sentry's release
     * "commits" field. Bypasses Sentry's own GitHub-integration backfill (sentry-cli's
     * `-c BASE..HEAD`, which calls `set_release_refs` and silently truncates large
     * ranges) by sending the full commit array directly, same as sentry-cli's own
     * local-git fallback does via `update_release`.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function fetchCommits(string $base): ?array
    {
        $repoSlug = $this->resolveRepoSlug();

        if (! $repoSlug) {
            $this->error('Could not determine the repository slug from the "origin" git remote.');

            return null;
        }

        if (! str_contains($repoSlug, '/')) {
            $this->error("Could not parse owner/repo from \"{$repoSlug}\".");

            return null;
        }

        [$owner, $repo] = explode('/', $repoSlug, 2);

        $head = Process::run(['git', 'rev-parse', 'HEAD']);

        if ($head->failed()) {
            $this->error('Could not resolve HEAD: '.$head->errorOutput());

            return null;
        }

        $headSha = trim($head->output());

        try {
            $github = resolve(GithubApiController::class);
        } catch (ModelNotFoundException) {
            $github = null;
        }

        $compare = $github
            ? $github->compareCommits($owner, $repo, $base, $headSha)
            : Http::get("https://api.github.com/repos/{$repoSlug}/compare/{$base}...{$headSha}")->json();

        $commits = $compare['commits'] ?? null;

        if (! is_array($commits)) {
            $this->error('GitHub compare request failed: '.($compare['message'] ?? 'unknown error'));

            return null;
        }

        $totalCommits = $compare['total_commits'] ?? count($commits);

        if ($totalCommits > count($commits)) {
            $this->warn("GitHub reports {$totalCommits} total commits but only returned ".count($commits).'; some may be missing from this release.');
        }

        $shas = array_column($commits, 'sha');

        $patchSets = [];

        if ($github) {
            $this->info('Fetching per-commit file changes for '.count($shas).' commits...');
            $patchSets = $github->commitFiles($owner, $repo, $shas);
        }

        return collect($commits)->map(fn (array $commit): array => [
            'id' => $commit['sha'],
            'repository' => $repoSlug,
            'message' => $commit['commit']['message'] ?? '',
            'author_name' => $commit['commit']['author']['name'] ?? null,
            'author_email' => $commit['commit']['author']['email'] ?? null,
            'timestamp' => $commit['commit']['author']['date'] ?? null,
            'patch_set' => $this->mapPatchSet($patchSets[$commit['sha']] ?? []),
        ])->all();
    }

    /**
     * Map GitHub's per-file "status" to the patch_set type codes Sentry's API accepts.
     * Sentry only supports A(dded)/M(odified)/D(eleted) — unlike git's own
     * --name-status, which also has R(enamed)/C(opied) — so renames/copies fold into M.
     *
     * @param  array<int, array<string, mixed>>  $files
     * @return array<int, array<string, string>>
     */
    private function mapPatchSet(array $files): array
    {
        return collect($files)->map(fn (array $file): array => [
            'path' => $file['filename'],
            'type' => match ($file['status'] ?? '') {
                'added' => 'A',
                'removed' => 'D',
                default => 'M',
            },
        ])->all();
    }

    private function resolveRepoSlug(): ?string
    {
        $result = Process::run(['git', 'remote', 'get-url', 'origin']);

        if ($result->failed()) {
            return null;
        }

        $url = trim($result->output());

        if (preg_match('#github\.com[:/](.+?)(\.git)?$#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Sentry's SaaS API is region-sharded (e.g. https://de.sentry.io). Derive the API
     * host from the configured DSN's ingest host rather than hardcoding it.
     */
    private function resolveApiBase(): string
    {
        $dsnHost = parse_url((string) config('sentry.dsn'), PHP_URL_HOST) ?: '';

        if (preg_match('#^o\d+\.ingest\.(.+)$#', $dsnHost, $matches)) {
            return 'https://'.$matches[1];
        }

        return 'https://sentry.io';
    }
}
