<?php

namespace App\Http\Controllers\Github;

use App\Model\Github\Github;
use Exception;
use Illuminate\Http\Client\PendingRequest;
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
     * body is an error object, not a release list.
     *
     * @return array<mixed>
     */
    public function releases(string $owner, string $repo): array
    {
        return $this->http->get(sprintf('/repos/%s/%s/releases', $owner, $repo), ['per_page' => 100])->throw()->json() ?? [];
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
