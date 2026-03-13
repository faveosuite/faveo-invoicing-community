<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Model\Github\Github;
use App\Model\Github\GithubRepo;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GithubRepoController extends Controller
{
    private Github $github;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->github = Github::firstOrFail();
    }

    private function githubClient(): Client
    {
        return new Client([
            'base_uri' => 'https://api.github.com',
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer '.$this->github->password,
                'X-GitHub-Api-Version' => '2022-11-28',
                'User-Agent' => $this->github->username,
            ],
        ]);
    }

    // ── Repo CRUD ─────────────────────────────────────────────────────────

    public function index()
    {
        $repos = GithubRepo::orderBy('display_name')->get();

        return view('themes.default1.github.repos.index', compact('repos'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'display_name' => 'required|string|max:100',
                'owner' => 'required|string|max:100',
                'repo' => 'required|string|max:100',
                'workflow_file' => 'nullable|string|max:100',
            ]);

            // Verify repo exists on GitHub
            $client = $this->githubClient();
            try {
                $client->get("/repos/{$request->owner}/{$request->repo}");
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    return errorResponse("Repository {$request->owner}/{$request->repo} not found on GitHub.");
                }
                throw $e;
            }

            GithubRepo::create([
                'display_name' => $request->display_name,
                'owner' => $request->owner,
                'repo' => $request->repo,
                'workflow_file' => $request->workflow_file ?: 'release.yml',
            ]);

            return successResponse(__('message.repo_added'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'display_name' => 'required|string|max:100',
                'owner' => 'required|string|max:100',
                'repo' => 'required|string|max:100',
                'workflow_file' => 'nullable|string|max:100',
            ]);

            $githubRepo = GithubRepo::findOrFail($id);
            $githubRepo->update([
                'display_name' => $request->display_name,
                'owner' => $request->owner,
                'repo' => $request->repo,
                'workflow_file' => $request->workflow_file ?: 'release.yml',
            ]);

            return successResponse(__('message.repo_updated'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            GithubRepo::findOrFail($id)->delete();

            return successResponse(__('message.repo_deleted'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    // ── Releases list for a repo ──────────────────────────────────────────

    public function releases(Request $request)
    {
        $repos = GithubRepo::orderBy('display_name')->get();
        $selectedId = $request->input('repo_id');
        $selected = $selectedId ? GithubRepo::find($selectedId) : $repos->first();

        return view('themes.default1.github.repos.releases', compact('repos', 'selected'));
    }

    public function fetchReleases($id)
    {
        try {
            $githubRepo = GithubRepo::findOrFail($id);
            $client = $this->githubClient();
            $resp = $client->get("/repos/{$githubRepo->owner}/{$githubRepo->repo}/releases?per_page=30");
            $releases = json_decode($resp->getBody(), true);

            $formatted = collect($releases)->map(fn ($r) => [
                'id' => $r['id'],
                'tag_name' => $r['tag_name'],
                'name' => $r['name'],
                'body' => $r['body'],
                'prerelease' => $r['prerelease'],
                'draft' => $r['draft'],
                'html_url' => $r['html_url'],
                'created_at' => $r['created_at'],
            ]);

            return response()->json(['releases' => $formatted]);
        } catch (Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function deleteRelease(Request $request)
    {
        try {
            $request->validate([
                'repo_id' => 'required|integer',
                'release_id' => 'required|integer',
            ]);

            $githubRepo = GithubRepo::findOrFail($request->repo_id);
            $client = $this->githubClient();
            $client->delete("/repos/{$githubRepo->owner}/{$githubRepo->repo}/releases/{$request->release_id}");

            return successResponse(__('message.release_deleted'));
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $body = json_decode($ex->getResponse()->getBody(), true);
            $message = $body['message'] ?? $ex->getMessage();

            return errorResponse($message);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }
}
