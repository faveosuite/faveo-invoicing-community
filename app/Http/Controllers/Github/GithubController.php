<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Model\Github\Github;
use App\Model\Github\GithubRepo;
use App\Model\Product\Subscription;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GithubController extends Controller
{
    public $github_api;

    public $client_id;

    public $client_secret;

    public $github;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $github_controller = new GithubApiController();
        $this->github_api = $github_controller;

        $model = new Github();
        $this->github = $model->firstOrFail();

        $this->client_id = $this->github->client_id;
        $this->client_secret = $this->github->client_secret;
    }

    public function createNewAuth($note)
    {
        try {
            $url = 'https://api.github.com/authorizations';
            $data = ['note' => $note];
            $data_string = json_encode($data);
            //dd($data_string);
            $auth = $this->github_api->postCurl($url, $data_string);

            return $auth;
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    public function getAllAuth()
    {
        try {
            $url = 'https://api.github.com/authorizations';
            $all = $this->github_api->getCurl($url);

            return $all;
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    public function getAuthById($id)
    {
        try {
            $url = "https://api.github.com/authorizations/$id";
            $auth = $this->github_api->getCurl($url);

            return $auth;
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * Authenticate a user for a particular application.
     *
     * @return type
     */
    public function authForSpecificApp()
    {
        try {
            $url = "https://api.github.com/authorizations/clients/$this->client_id";
            $data = ['client_secret' => "$this->client_secret"];
            $data_string = json_encode($data);
            $method = 'PUT';
            $auth = $this->github_api->postCurl($url, $data_string, $method);

            //dd($auth['hashed_token']);
            return $auth['hashed_token'];
            //dd($auth);
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * List all release.
     *
     * @return type
     */
    public function listRepositories($owner, $repo, $order_id)
    {
        try {
            $releases = $this->downloadLink($owner, $repo, $order_id);
            if (array_key_exists('Location', $releases)) {
                $release = $releases['Location'];
            } else {
                $release = $this->latestRelese($owner, $repo);
            }

            return $release;
            //echo "Your download will begin in a moment. If it doesn't, <a href=$release>Click here to download</a>";
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    public function listRepositoriesAdmin($owner, $repo)
    {
        try {
            $releases = $this->downloadLinkAdmin($owner, $repo);
            if (array_key_exists('Location', $releases)) {
                $release = $releases['Location'];
            } else {
                $release = $this->latestRelese($owner, $repo);
                //dd($release);
            }

            //            dd($release);
            return $release;

            //echo "Your download will begin in a moment. If it doesn't, <a href=$release>Click here to download</a>";
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    public function latestRelese($owner, $repo)
    {
        try {
            $url = "https://api.github.com/repos/$owner/$repo/releases/latest";
            $release = $this->github_api->getCurl($url);

            return $release;
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * List only one release by id.
     *
     * @param  type  $id
     * @return type
     */
    public function getReleaseById($id)
    {
        try {
            $url = "https://api.github.com/repos/ladybirdweb/faveo-helpdesk/releases/$id";
            $releaseid = $this->github_api->getCurl($url);

            return $releaseid;
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get the count of download of the release.
     *
     * @return array
     */
    public function getDownloadCount()
    {
        try {
            $url = 'https://api.github.com/repos/ladybirdweb/faveo-helpdesk/downloads';
            $downloads = $this->github_api->getCurl($url);

            return $downloads;
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * @param  type  $release
     * @return type .zip file
     */
    public function download($release)
    {
        echo "<form action=$release method=get name=download>";
        echo '</form>';
        echo"<script language='javascript'>document.download.submit();</script>";

        //return "success";
    }

    /**
     * get the settings page for github.
     *
     * @return \view
     */
    public function getSettings()
    {
        try {
            $model = $this->github;
            $githubStatus = StatusSetting::first()->github_status;
            $githubFileds = $model->select('client_id', 'client_secret', 'username', 'password')->first();

            return view('themes.default1.github.settings', compact('model', 'githubStatus', 'githubFileds'));
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    public function postSettings(Request $request)
    {
        try {
            $status = $request->input('status');
            try {
                $client = new Client();
                $username = $request->input('git_username');
                $token = $request->input('git_password');
                $response = $client->get('https://api.github.com/user', [
                    'auth' => [$username, $token],
                    'headers' => [
                        'Accept' => 'application/vnd.github+json',
                        'User-Agent' => 'MyApp',
                    ],
                ]);

                $data = json_decode($response->getBody(), true);
                if ($data['login'] !== $username) {
                    return errorResponse(\Lang::get('message.github_invalid'));
                }
            } catch(\Exception $ex) {
                return errorResponse(\Lang::get('message.github_invalid'));
            }
            StatusSetting::find(1)->update(['github_status' => $status]);
            Github::find(1)->update(['username' => $request->input('git_username'),
                'password' => $request->input('git_password'), ]);

            return successResponse(\Lang::get('message.github_valid'));
        } catch (Exception $ex) {
            return errorResponse(\Lang::get('message.github_invalid'));
        }
    }

    /**
     * Github Downoload for Clients.
     *
     * @param  type  $owner
     * @param  type  $repo
     * @param  type  $order_id
     * @return type
     */
    public function downloadLink($owner, $repo, $order_id)
    {
        try {
            // $url = "https://api.github.com/repos/$owner/$repo/releases";
            $url = "https://api.github.com/repos/$owner/$repo/zipball/master";
            //For helpdesk-community
            if ($repo == 'faveo-helpdesk') {
                return $array = ['Location' => $url];
            }
            //For servicedesk-community
            if ($repo == 'faveo-servicedesk-community') {
                return $array = ['Location' => $url];
            }

            $order_end_date = Subscription::where('order_id', '=', $order_id)->select('ends_at')->first();
            $url = "https://api.github.com/repos/$owner/$repo/releases";

            $link = $this->github_api->getCurl1($url);
            foreach ($link['body'] as $key => $value) {
                if (strtotime($value['created_at']) < strtotime($order_end_date->ends_at)) {
                    $ver[] = $value['tag_name'];
                }
            }
            $url = $this->getUrl($repo, $ver);
            $link = $this->github_api->getCurl1($url);

            return $link['header'];
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getUrl($repo, $ver)
    {
        //For Satellite Helpdesk
        if ($repo == 'faveo-satellite-helpdesk-advance') {
            $url = 'https://api.github.com/repos/ladybirdweb/faveo-satellite-helpdesk-advance/zipball/'.$ver[0];
        }

        //For Helpdesk Advanced
        if ($repo == 'Faveo-Helpdesk-Pro') {
            $url = 'https://api.github.com/repos/ladybirdweb/Faveo-Helpdesk-Pro/zipball/'.$ver[0];
        }
        //For Service Desk Advance
        if ($repo == 'faveo-service-desk-pro') {
            $url = 'https://api.github.com/repos/ladybirdweb/faveo-service-desk-pro/zipball/'.$ver[0];
        }

        return $url;
    }

    //Github Download for Admin
    public function downloadLinkAdmin($owner, $repo)
    {
        try {
            $url = "https://api.github.com/repos/$owner/$repo/zipball/master";
            if ($repo == 'faveo-helpdesk') {
                return $array = ['Location' => $url];
            }
            $link = $this->github_api->getCurl1($url);

            return $link['header'];
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function findVersion($owner, $repo)
    {
        try {
            $release = $this->latestRelese($owner, $repo);
            if (array_key_exists('tag_name', $release)) {
                return $release['tag_name'];
            }
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Build a Guzzle client pre-configured for GitHub API v3.
     */
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

    /**
     * Resolve owner/repo from repo_id (GithubRepo) or fall back to config.
     */
    private function resolveRepo($repoId): array
    {
        if ($repoId) {
            $r = GithubRepo::findOrFail($repoId);

            return [$r->owner, $r->repo, $r->workflow_file, $r->dispatch_branch ?? 'development'];
        }

        return [config('github.owner'), config('github.repo'), config('github.workflow_file', 'release.yml'), 'development'];
    }

    /**
     * Show the create release form.
     */
    public function createRelease()
    {
        try {
            $repos = GithubRepo::orderBy('display_name')->get();

            return view('themes.default1.github.release', compact('repos'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * AJAX: fetch latest tag for a selected repo.
     */
    public function latestTag(Request $request)
    {
        try {
            [$owner, $repo] = $this->resolveRepo($request->input('repo_id'));
            $tags = $this->github_api->getCurl("https://api.github.com/repos/{$owner}/{$repo}/tags");
            $latestTag = (is_array($tags) && count($tags) > 0) ? $tags[0]['name'] : null;

            return response()->json(['latest_tag' => $latestTag]);
        } catch (Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * AJAX: check whether a tag and/or release already exist on GitHub.
     */
    public function checkTag(Request $request)
    {
        try {
            $tag = trim($request->input('tag'));
            [$owner, $repo] = $this->resolveRepo($request->input('repo_id'));
            $client = $this->githubClient();

            try {
                $client->get("/repos/{$owner}/{$repo}/git/ref/tags/{$tag}");
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    return response()->json(['tag_exists' => false, 'release_exists' => false]);
                }
                throw $e;
            }

            try {
                $resp = $client->get("/repos/{$owner}/{$repo}/releases/tags/{$tag}");
                $release = json_decode($resp->getBody(), true);

                return response()->json([
                    'tag_exists' => true,
                    'release_exists' => true,
                    'release' => [
                        'id' => $release['id'],
                        'name' => $release['name'],
                        'body' => $release['body'],
                        'prerelease' => $release['prerelease'],
                        'draft' => $release['draft'],
                        'html_url' => $release['html_url'],
                    ],
                ]);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    return response()->json(['tag_exists' => true, 'release_exists' => false]);
                }
                throw $e;
            }
        } catch (Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Trigger GitHub Actions workflow_dispatch (new tag — full pipeline).
     */
    public function triggerWorkflow(Request $request)
    {
        try {
            $request->validate([
                'repo_id' => 'required|integer',
                'tag_name' => 'required|string|max:100',
                'release_title' => 'required|string|max:255',
                'release_notes' => 'required|string',
            ]);

            [$owner, $repo, $workflow, $branch] = $this->resolveRepo($request->input('repo_id'));
            $client = $this->githubClient();

            $client->post("/repos/{$owner}/{$repo}/actions/workflows/{$workflow}/dispatches", [
                'json' => [
                    'ref' => $branch,
                    'inputs' => [
                        'tag_name' => $request->input('tag_name'),
                        'release_title' => $request->input('release_title'),
                        'release_notes' => $request->input('release_notes'),
                        'prerelease' => $request->input('prerelease', '0') == '1' ? 'true' : 'false',
                        'draft' => $request->input('draft', '0') == '1' ? 'true' : 'false',
                    ],
                ],
            ]);

            return successResponse(__('message.workflow_triggered'));
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $body = json_decode($ex->getResponse()->getBody(), true);
            $status = $ex->getResponse()->getStatusCode();
            $detail = isset($body['errors']) ? json_encode($body['errors']) : '';

            return errorResponse("GitHub {$status}: ".($body['message'] ?? $ex->getMessage()).($detail ? " | {$detail}" : ''));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Create a release for an existing tag (tag exists, no release yet).
     */
    public function postCreateRelease(Request $request)
    {
        try {
            $request->validate([
                'repo_id' => 'required|integer',
                'tag_name' => 'required|string|max:100',
                'release_title' => 'required|string|max:255',
                'release_notes' => 'required|string',
            ]);

            [$owner, $repo] = $this->resolveRepo($request->input('repo_id'));
            $client = $this->githubClient();

            $resp = $client->post("/repos/{$owner}/{$repo}/releases", [
                'json' => [
                    'tag_name' => $request->input('tag_name'),
                    'name' => $request->input('release_title'),
                    'body' => $request->input('release_notes'),
                    'draft' => $request->input('draft', '0') == '1',
                    'prerelease' => $request->input('prerelease', '0') == '1',
                ],
            ]);
            $release = json_decode($resp->getBody(), true);

            return successResponse(__('message.release_created'), ['html_url' => $release['html_url']]);
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $body = json_decode($ex->getResponse()->getBody(), true);

            return errorResponse($body['message'] ?? $ex->getMessage());
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Update an existing release (notes, title, pre-release toggle, draft).
     */
    public function updateRelease(Request $request)
    {
        try {
            $request->validate([
                'repo_id' => 'required|integer',
                'release_id' => 'required|integer',
                'release_title' => 'required|string|max:255',
                'release_notes' => 'required|string',
            ]);

            [$owner, $repo] = $this->resolveRepo($request->input('repo_id'));
            $client = $this->githubClient();

            $resp = $client->patch("/repos/{$owner}/{$repo}/releases/{$request->input('release_id')}", [
                'json' => [
                    'name' => $request->input('release_title'),
                    'body' => $request->input('release_notes'),
                    'draft' => $request->input('draft', '0') == '1',
                    'prerelease' => $request->input('prerelease', '0') == '1',
                ],
            ]);
            $release = json_decode($resp->getBody(), true);

            return successResponse(__('message.release_updated'), ['html_url' => $release['html_url']]);
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $body = json_decode($ex->getResponse()->getBody(), true);

            return errorResponse($body['message'] ?? $ex->getMessage());
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Promote a pre-release to an official release.
     */
    public function promoteRelease(Request $request)
    {
        try {
            $request->validate(['repo_id' => 'required|integer', 'release_id' => 'required|integer']);

            [$owner, $repo] = $this->resolveRepo($request->input('repo_id'));
            $client = $this->githubClient();

            $resp = $client->patch("/repos/{$owner}/{$repo}/releases/{$request->input('release_id')}", [
                'json' => ['prerelease' => false, 'draft' => false],
            ]);
            $release = json_decode($resp->getBody(), true);

            return successResponse(__('message.release_promoted'), ['html_url' => $release['html_url']]);
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $body = json_decode($ex->getResponse()->getBody(), true);
            $message = $body['message'] ?? $ex->getMessage();

            return errorResponse($message);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }
}
