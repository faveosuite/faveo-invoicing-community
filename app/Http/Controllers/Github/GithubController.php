<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Model\Github\Github;
use Exception;
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

        $github_controller = new GithubApiController();
        $this->github_api = $github_controller;

        $model = new Github();
        $this->github = $model->firstOrFail();

        $this->client_id = $this->github->client_id;
        $this->client_secret = $this->github->client_secret;
    }

    /**
     * Authenticate a user entirly.
     *
     * @return type
     */
    public function authenticate()
    {
        try {
            $url = 'https://api.github.com/user';
            $data = ['bio' => 'This is my bio'];
            $data_string = json_encode($data);
            $auth = $this->github_api->postCurl($url, $data_string);

            return $auth;
//            if($auth!='true'){
//                throw new Exception('can not authenticate with github', 401);
//            }
            //$authenticated = json_decode($auth);
            //dd($authenticated);
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * Authenticate a user for a perticular application.
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

            return $auth;
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
    public function listRepositories($owner, $repo)
    {
        try {
            $url = "https://api.github.com/repos/$owner/$repo/releases";
            $releases = $this->github_api->getCurl($url);
            //dd($releases);
            return $releases;
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * List only one release by tag.
     *
     * @param Request $request
     *
     * @return type
     */
    public function getReleaseByTag($owner, $repo)
    {
        try {
            $tag = \Input::get('tag');
            $all_releases = $this->listRepositories($owner, $repo);
            //dd($all_releases);
            if ($tag) {
                foreach ($all_releases as $key => $release) {
                    //dd($release);
                    if (in_array($tag, $release)) {
                        $version[$tag] = $this->getReleaseById($release['id']);
                    }
                }
            } else {
                $version[0] = $all_releases[0];
            }
            //dd($version);
            //execute download

            if ($this->download($version) == 'success') {
                return 'success';
            }
            //return redirect()->back()->with('success', \Lang::get('message.downloaded-successfully'));
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * List only one release by id.
     *
     * @param type $id
     *
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
     * @return array||redirect
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
     * @param type $release
     *
     * @return type .zip file
     */
    public function download($release)
    {
        try {
            foreach ($release as $url) {
                $download_link = $url['zipball_url'];
            }
            echo "<form action=$download_link method=get name=download>";
            echo '</form>';
            echo"<script language='javascript'>document.download.submit();</script>";

            //return "success";
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    /**
     * get the settings page for github.
     *
     * @return view
     */
    public function getSettings()
    {
        try {
            $model = $this->github;

            return view('themes.default1.github.settings', compact('model'));
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }

    public function postSettings(Request $request)
    {
        $this->validate($request, [
                'username' => 'required',
                'password' => 'required',
            ]);
        try {
            $this->github->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
