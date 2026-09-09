<?php

namespace Tests\Unit\Backend\Console\Commands;

use App\Http\Controllers\Github\GithubApiController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Tests\DBTestCase;

class SentryReleaseTest extends DBTestCase
{
    private function fakeGitCommands(): void
    {
        Process::fake([
            '*remote*origin*' => Process::result(output: "https://github.com/faveosuite/faveo-invoicing-community.git\n"),
            '*rev-parse*HEAD*' => Process::result(output: "deadbeefdeadbeefdeadbeefdeadbeefdeadbeef\n"),
        ]);
    }

    public function test_fails_when_no_auth_token_is_available(): void
    {
        config(['services.sentry.auth_token' => null]);

        $this->artisan('sentry:release')
            ->expectsOutputToContain('Sentry auth token is required')
            ->assertExitCode(1);
    }

    public function test_fails_when_app_version_is_not_set(): void
    {
        config(['app.version' => null]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token'])
            ->expectsOutputToContain('app.version is not set')
            ->assertExitCode(1);
    }

    public function test_creates_release_and_deploy_without_base(): void
    {
        config(['app.version' => '1.2.3']);
        Http::fake([
            '*/releases/*/deploys/*' => Http::response(['id' => 'deploy-1']),
            '*/releases/*' => Http::response(['commitCount' => 0, 'version' => '1.2.3']),
        ]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--environment' => 'staging'])
            ->expectsOutputToContain('No --base given')
            ->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/releases/')
            && ! str_contains($request->url(), '/deploys/')
            && $request['version'] === '1.2.3'
            && $request['projects'] === ['faveo-invoicing']
            && ! isset($request['commits'])
            && $request->hasHeader('Authorization', 'Bearer test-token'));

        Http::assertSent(fn ($request) => str_contains($request->url(), '/deploys/')
            && $request['environment'] === 'staging');
    }

    public function test_stops_and_fails_when_release_creation_fails(): void
    {
        config(['app.version' => '1.2.3']);
        Http::fake(['*' => Http::response(['detail' => 'boom'], 500)]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token'])
            ->expectsOutputToContain('Failed to create release')
            ->assertExitCode(1);

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/deploys/'));
    }

    public function test_stops_and_fails_when_deploy_creation_fails(): void
    {
        config(['app.version' => '1.2.3']);
        Http::fake([
            '*/releases/*/deploys/*' => Http::response(['detail' => 'boom'], 500),
            '*/releases/*' => Http::response(['commitCount' => 0, 'version' => '1.2.3']),
        ]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token'])
            ->expectsOutputToContain('Failed to create deploy')
            ->assertExitCode(1);
    }

    public function test_creates_release_with_commits_and_patch_set_from_base(): void
    {
        config(['app.version' => '1.2.3']);
        $this->fakeGitCommands();

        Http::fake([
            'api.github.com/repos/*/compare/*' => Http::response([
                'total_commits' => 1,
                'commits' => [[
                    'sha' => 'abc123',
                    'commit' => [
                        'message' => 'a test commit',
                        'author' => ['name' => 'Tester', 'email' => 'tester@example.com', 'date' => '2026-01-01T00:00:00Z'],
                    ],
                ]],
            ]),
            'api.github.com/repos/*/commits/*' => Http::response([
                'files' => [
                    ['filename' => 'app/Foo.php', 'status' => 'modified'],
                    ['filename' => 'app/New.php', 'status' => 'added'],
                    ['filename' => 'app/Old.php', 'status' => 'removed'],
                ],
            ]),
            '*/releases/*/deploys/*' => Http::response(['id' => 'deploy-1']),
            '*/releases/*' => Http::response(['commitCount' => 1, 'version' => '1.2.3']),
        ]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--base' => 'master'])
            ->expectsOutputToContain('Found 1 commits from master to HEAD')
            ->assertExitCode(0);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/releases/') || str_contains($request->url(), '/deploys/')) {
                return false;
            }

            $commits = $request['commits'] ?? null;

            if (! is_array($commits) || count($commits) !== 1) {
                return false;
            }

            $commit = $commits[0];

            return $commit['id'] === 'abc123'
                && $commit['repository'] === 'faveosuite/faveo-invoicing-community'
                && $commit['author_email'] === 'tester@example.com'
                && $commit['patch_set'] === [
                    ['path' => 'app/Foo.php', 'type' => 'M'],
                    ['path' => 'app/New.php', 'type' => 'A'],
                    ['path' => 'app/Old.php', 'type' => 'D'],
                ];
        });
    }

    public function test_falls_back_to_unauthenticated_github_when_no_integration_is_configured(): void
    {
        config(['app.version' => '1.2.3']);
        $this->fakeGitCommands();
        $this->app->bind(GithubApiController::class, function (): never {
            throw new ModelNotFoundException;
        });

        Http::fake([
            'api.github.com/repos/*/compare/*' => Http::response([
                'total_commits' => 0,
                'commits' => [],
            ]),
            '*/releases/*/deploys/*' => Http::response(['id' => 'deploy-1']),
            '*/releases/*' => Http::response(['commitCount' => 0, 'version' => '1.2.3']),
        ]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--base' => 'master'])
            ->expectsOutputToContain('Found 0 commits from master to HEAD')
            ->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.github.com/repos/faveosuite/faveo-invoicing-community/compare/master'));
    }

    public function test_fails_when_repo_slug_cannot_be_resolved(): void
    {
        config(['app.version' => '1.2.3']);
        Process::fake(['*' => Process::result(exitCode: 1, errorOutput: 'not a git repository')]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--base' => 'master'])
            ->expectsOutputToContain('Could not determine the repository slug')
            ->assertExitCode(1);
    }

    public function test_fails_when_github_compare_request_fails(): void
    {
        config(['app.version' => '1.2.3']);
        $this->fakeGitCommands();

        Http::fake([
            'api.github.com/repos/*/compare/*' => Http::response(['message' => 'Not Found'], 404),
        ]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--base' => 'master'])
            ->expectsOutputToContain('GitHub compare request failed')
            ->assertExitCode(1);
    }

    public function test_delete_removes_an_existing_release(): void
    {
        config(['app.version' => '1.2.3']);
        Http::fake(['*' => Http::response(null, 204)]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--delete' => true])
            ->expectsOutputToContain('Deleted release 1.2.3')
            ->assertExitCode(0);

        Http::assertSent(fn ($request) => $request->method() === 'DELETE' && str_contains($request->url(), '/releases/1.2.3/'));
    }

    public function test_delete_is_a_graceful_no_op_when_release_does_not_exist(): void
    {
        config(['app.version' => '1.2.3']);
        Http::fake(['*' => Http::response(['detail' => 'not found'], 404)]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--delete' => true])
            ->expectsOutputToContain('Did nothing. Release with this version (1.2.3) does not exist.')
            ->assertExitCode(0);
    }

    public function test_delete_fails_with_scope_hint_when_token_lacks_permission(): void
    {
        config(['app.version' => '1.2.3']);
        Http::fake(['*' => Http::response(['detail' => 'You do not have permission to perform this action.'], 403)]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--delete' => true])
            ->expectsOutputToContain('Failed to delete release')
            ->expectsOutputToContain('project:releases/project:admin scope')
            ->assertExitCode(1);
    }

    public function test_delete_uses_release_version_override_instead_of_app_version(): void
    {
        config(['app.version' => '1.2.3']);
        Http::fake(['*' => Http::response(null, 204)]);

        $this->artisan('sentry:release', ['--auth-token' => 'test-token', '--delete' => true, '--release-version' => '9.9.9'])
            ->expectsOutputToContain('Deleted release 9.9.9')
            ->assertExitCode(0);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/releases/9.9.9/'));
    }
}
