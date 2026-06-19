<?php

namespace App\Http\Controllers\Github;

use App\Model\Github\Github;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GithubApiController
{
    private const API_BASE = 'https://api.github.com';

    private PendingRequest $http;

    public function __construct()
    {
        $github = Github::firstOrFail();

        $this->http = Http::baseUrl(self::API_BASE)
            ->withBasicAuth((string) $github->username, (string) $github->password)
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => $github->username ?: 'FaveoBilling',
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->timeout(90);
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
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => $username ?: 'FaveoBilling',
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->timeout(30)
            ->get(self::API_BASE.'/user');

        return $response->successful() && $response->json('login') === $username;
    }

    /**
     * Authorize this app against the configured GitHub OAuth application.
     */
    public function authorizeApp(): ?string
    {
        $github = Github::firstOrFail();

        return $this->http
            ->put('/authorizations/clients/'.$github->client_id, [
                'client_secret' => $github->client_secret,
            ])
            ->json('hashed_token');
    }
}
