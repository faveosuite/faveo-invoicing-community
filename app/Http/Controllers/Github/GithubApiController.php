<?php

namespace App\Http\Controllers\Github;

use App\Model\Github\Github;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
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
     * A repository's releases, newest first — the latest 100 (GitHub's maximum per call;
     * its default is only 30). Throws on a GitHub error (bad repo/token, rate limit), whose
     * body is an error object, not a release list. Notes come as GitHub-rendered `body_html`,
     * whose private-repo images carry short-lived signed URLs (the raw `body` links need a login).
     *
     * Cached briefly: the versions list and every GitHub download ask for this. Errors aren't
     * cached (they throw before anything is stored).
     *
     * @return array<mixed>
     */
    public function releases(string $owner, string $repo): array
    {
        // ponytail: 2 min, not longer — body_html image URLs expire ~5 min after GitHub signs them.
        return Cache::remember("github_releases:{$owner}/{$repo}", now()->addMinutes(2), fn (): array => $this->http->accept('application/vnd.github.html+json')
            ->get(sprintf('/repos/%s/%s/releases', $owner, $repo), ['per_page' => 100])->throw()->json() ?? []);
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
     * Whether the stored credentials can see this repository (false for a typo or no access).
     */
    public function repositoryExists(string $owner, string $repo): bool
    {
        return $this->http->get(sprintf('/repos/%s/%s', $owner, $repo))->successful();
    }

    /**
     * The tag name of the latest stable release.
     */
    public function latestTag(string $owner, string $repo): ?string
    {
        return $this->latestRelease($owner, $repo)['tag_name'] ?? null;
    }

    /**
     * Build a zipball URL for a given ref (tag or branch).
     */
    public function zipballUrl(string $owner, string $repo, string $ref = 'master'): string
    {
        return self::API_BASE.sprintf('/repos/%s/%s/zipball/%s', $owner, $repo, $ref);
    }

    /**
     * Download a ref's zipball to a local temp file and return its path (the caller owns it).
     * Done server-side so the zip can be stamped and no GitHub link ever reaches the browser;
     * GitHub's redirect to its signed codeload URL is followed here.
     */
    public function downloadZipball(string $owner, string $repo, string $ref): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'github_zipball_');

        if ($tempPath === false) {
            throw new Exception(trans('message.file_not_exist'));
        }

        try {
            $response = (clone $this->http)->timeout(600)->sink($tempPath)->get($this->zipballUrl($owner, $repo, $ref));
        } catch (Exception $exception) {
            File::delete($tempPath);

            throw $exception;
        }

        if (! $response->successful()) {
            File::delete($tempPath);

            throw new Exception(trans('message.file_not_exist'));
        }

        return $tempPath;
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
