<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Model\Github\Github;

class GithubApiController extends Controller
{
    private $username;

    private $password;

    private $github;

    public function __construct()
    {
        $model = new Github();
        $this->github = $model->firstOrFail();

        $this->username = $this->github->username;
        $this->password = $this->github->password;
    }

    public function postCurl($url, $data = '', $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent:$this->username"]);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        $content = curl_exec($ch);
        curl_close($ch);

        return json_decode($content, true);
    }

    public function getCurl($url)
    {
        try {
            if (str_contains($url, ' ')) {
                $url = str_replace(' ', '', $url);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent:$this->username"]);
            curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
            if (curl_exec($ch) === false) {
                echo 'Curl error: '.curl_error($ch);
            }
            $content = curl_exec($ch);
            curl_close($ch);

            return json_decode($content, true);
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getCurl1($url)
    {
        if (str_contains($url, ' ')) {
            $url = str_replace(' ', '', $url);
        }
        // $url = "https://api.github.com/repos/ladybirdweb/faveo-helpdesk/zipball/master";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent:$this->username"]);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
        if (curl_exec($ch) === false) {
            echo 'Curl error: '.curl_error($ch);
        }
        $content = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($content, 0, $header_size);
        $header = $this->convertHeaderToArray($header, $content);

        $body = substr($content, $header_size);
        curl_close($ch);

        return ['body' => json_decode($body, true), 'header' => $header];
    }

    public function convertHeaderToArray($header_text, $response)
    {
        try {
            $headers = [];

            $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
            foreach (explode("\r\n", $header_text) as $i => $line) {
                if ($i === 0) {
                    $headers['http_code'] = $line;
                } else {
                    [$key, $value] = explode(': ', $line);

                    $headers[$key] = $value;
                }
            }

            return $headers;
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
