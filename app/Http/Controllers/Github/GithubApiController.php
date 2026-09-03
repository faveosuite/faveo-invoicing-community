<?php

namespace App\Http\Controllers\Github;

use App\Model\Github\Github;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GithubApiController
{
    private const API_BASE = 'https://api.github.com';

    private Github $github;

    private PendingRequest $http;

    public function __construct()
    {
        $this->github = Github::firstOrFail();

        $this->http = Http::baseUrl(self::API_BASE)
            ->withBasicAuth((string) $this->github->username, (string) $this->github->password)
            ->withHeaders(self::headers((string) $this->github->username))
            ->timeout(90);
    }

    /**
     * @return array<string, string>
     */
    private static function headers(string $username): array
    {
        return [
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => $username ?: 'FaveoBilling',
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
    }

    /**
     * All releases for a repository, newest first.
     *
     * @return array<mixed>
     */
    public function releases(string $owner, string $repo): array
    {
        return $this->http->get(sprintf('/repos/%s/%s/releases', $owner, $repo))->json() ?? [];
    }

    /**
     * The latest stable release metadata for a repository.
     *
     * @return array<mixed>
     */
    public function latestRelease(string $owner, string $repo): array
    {
        return $this->http->get(sprintf('/repos/%s/%s/releases/latest', $owner, $repo))->json() ?? [];
    }

    /**
     * The tag name of the latest stable release.
     */
    public function latestTag(string $owner, string $repo): ?string
    {
        return $this->latestRelease($owner, $repo)['tag_name'] ?? null;
    }

    /**
     * Compare two refs (branch names, tags, or SHAs) and return the commits between them.
     *
     * @return array<mixed>
     */
    public function compareCommits(string $owner, string $repo, string $base, string $head): array
    {
        return $this->http->get(sprintf('/repos/%s/%s/compare/%s...%s', $owner, $repo, $base, $head))->json() ?? [];
    }

    /**
     * Each commit's changed-files list, keyed by SHA — used to build Sentry's
     * per-commit patch_set (powers suspect-commit/blame). Fetched in small
     * concurrent batches (not all at once) to stay clear of GitHub's secondary
     * rate limit on large commit ranges.
     *
     * @param  array<int, string>  $shas
     * @return array<string, array<mixed>>
     */
    public function commitFiles(string $owner, string $repo, array $shas): array
    {
        $files = [];

        foreach (array_chunk($shas, 15) as $chunk) {
            $responses = Http::pool(fn (Pool $pool) => collect($chunk)->map(
                fn (string $sha) => $pool->as($sha)
                    ->baseUrl(self::API_BASE)
                    ->withBasicAuth((string) $this->github->username, (string) $this->github->password)
                    ->withHeaders(self::headers((string) $this->github->username))
                    ->timeout(90)
                    ->get(sprintf('/repos/%s/%s/commits/%s', $owner, $repo, $sha))
            )->all());

            foreach ($chunk as $sha) {
                $response = $responses[$sha];
                $files[$sha] = $response instanceof Response ? ($response->json('files') ?? []) : [];
            }
        }

        return $files;
    }

    /**
     * Build a zipball URL for a given ref (tag or branch).
     */
    public function zipballUrl(string $owner, string $repo, string $ref = 'master'): string
    {
        return self::API_BASE.sprintf('/repos/%s/%s/zipball/%s', $owner, $repo, $ref);
    }

    /**
     * Resolve a GitHub zipball URL to the actual S3 presigned download URL.
     *
     * GitHub responds to zipball requests with a 302 redirect to a short-lived S3 URL.
     * On rate-limit or auth errors it returns 403 with the URL embedded in the message body.
     */
    public function resolveDownloadUrl(string $zipballUrl): string
    {
        $response = $this->http
            ->withoutRedirecting()
            ->withOptions(['http_errors' => false])
            ->get($zipballUrl);

        // Normal case: GitHub redirects to a presigned S3 URL.
        if ($response->redirect()) {
            return $response->header('Location');
        }

        // 403 only: GitHub embeds the actual download URL inside the rate-limit/auth message.
        // Any other status (404 tag not found, 500, etc.) goes straight to the exception.
        if ($response->status() === 403) {
            $message = $response->json('message') ?? '';
            if (preg_match('#https://[^\s,"]+#', $message, $matches)) {
                return $matches[0];
            }
        }

        throw new Exception(trans('message.file_not_exist'));
    }

    /**
     * Validate a username + personal access token against the GitHub API.
     * Used when saving GitHub settings — does NOT use the stored credentials.
     */
    public static function validateCredentials(string $username, string $token): bool
    {
        $response = Http::withBasicAuth($username, $token)
            ->withHeaders(self::headers($username))
            ->timeout(30)
            ->get(self::API_BASE.'/user');

        return $response->successful() && $response->json('login') === $username;
    }
}
