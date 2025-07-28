<?php

namespace Devio\Pipedrive\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;

class PipedriveClient implements Client
{
    /**
     * The Guzzle client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * Oauth flag
     *
     * @var bool.
     */
    protected $isOauth = false;

    const DEFAULT_BODY_FORMAT = RequestOptions::JSON;

    /**
     * GuzzleClient constructor.
     *
     * @param $url
     * @param $token
     */
    public function __construct($url, $credentials)
    {
        list($headers, $query) = [[], []];

        if (gettype($credentials) == 'object') {
            $this->isOauth = true;
            $headers['Authorization'] = 'Bearer ' . $credentials->getAccessToken();
        } else {
            $query['api_token'] = $credentials;
        }

        $this->client = new GuzzleClient(
            [
                'base_uri'        => $url,
                'allow_redirects' => false,
                'headers'         => $headers,
                'query'           => $query
            ]
        );
    }

    /**
     * Create an OAuth client.
     *
     * @param $url
     * @param $storage
     * @param $pipedrive
     * @return PipedriveClient
     */
    public static function OAuth($url, $storage, $pipedrive)
    {
        $token = $storage->getToken();

        if (! $token || ! $token->valid()) {
            $pipedrive->OAuthRedirect();
        }

        $token->refreshIfNeeded($pipedrive);

        return new self($url, $token);
    }

    /**
     * Perform a GET request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function get($url, $parameters = [])
    {
        $options = $this->getClient()
                        ->getConfig();
        array_set($options, 'query', array_merge($parameters, $options['query']));

        // For this particular case we have to include the parameters into the
        // URL query. Merging the request default query configuration to the
        // request parameters will make the query key contain everything.
        return $this->execute(new GuzzleRequest('GET', $url), $options);
    }

    /**
     * Perform a POST request.
     *
     * @param $url
     * @param array $parameters
     * @return Response
     */
    public function post($url, $parameters = [])
    {
        $request = new GuzzleRequest('POST', $url);
        $form = self::DEFAULT_BODY_FORMAT;

        // If any file key is found, we will assume we have to convert the data
        // into the multipart array structure. Otherwise, we will perform the
        // request as usual using the form_params with the given parameters.
        if (isset($parameters['file'])) {
            $form = 'multipart';
            $parameters = $this->multipart($parameters);
        }

        if (isset($parameters['json'])) {
            $form = RequestOptions::JSON;
            $parameters = array_except($parameters, RequestOptions::JSON);
        }

        return $this->execute($request, [$form => $parameters]);
    }

    /**
     * Convert the parameters into a multipart structure.
     *
     * @param array $parameters
     * @return array
     */
    protected function multipart(array $parameters)
    {
        if (! ($file = $parameters['file']) instanceof \SplFileInfo) {
            throw new \InvalidArgumentException('File must be an instance of \SplFileInfo.');
        }

        $result = [];
        $content = file_get_contents($file->getPathname());

        foreach (array_except($parameters, 'file') as $key => $value) {
            $result[] = ['name' => $key, 'contents' => (string) $value];
        }
        // Will convert every element of the array into a format accepted by the
        // multipart encoding standards. It will also add a special item which
        // includes the file key name, the content of the file and its name.
        $result[] = ['name' => 'file', 'contents' => $content, 'filename' => $file->getFilename()];

        return $result;
    }

    /**
     * Perform a PUT request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function put($url, $parameters = [])
    {
        $request = new GuzzleRequest('PUT', $url);

        return $this->execute($request, [self::DEFAULT_BODY_FORMAT => $parameters]);
    }

    /**
     * Perform a PATCH request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function patch($url, $parameters = [])
    {
        $request = new GuzzleRequest('PATCH', $url);
        $form = self::DEFAULT_BODY_FORMAT;

        // If any file key is found, we will assume we have to convert the data
        // into the multipart array structure. Otherwise, we will perform the
        // request as usual using the form_params with the given parameters.
        if (isset($parameters['file'])) {
            $form = 'multipart';
            $parameters = $this->multipart($parameters);
        }

        if (isset($parameters['json'])) {
            $form = RequestOptions::JSON;
            $parameters = array_except($parameters, RequestOptions::JSON);
        }

        return $this->execute($request, [$form => $parameters]);
    }

    /**
     * Perform a DELETE request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function delete($url, $parameters = [])
    {
        $request = new GuzzleRequest('DELETE', $url);

        return $this->execute($request, [self::DEFAULT_BODY_FORMAT => $parameters]);
    }

    /**
     * Execute the request and returns the Response object.
     *
     * @param GuzzleRequest $request
     * @param array $options
     * @param null $client
     * @return Response
     */
    protected function execute(GuzzleRequest $request, array $options = [], $client = null)
    {
        $client = $client ?: $this->getClient();

        // We will just execute the given request using the default or given client
        // and with the passed options wich may contain the query, body vars, or
        // any other info. Both OK and fail will generate a response object.
        try {
            $response = $client->send($request, $options);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        // As there are a few responses that are supposed to perform the
        // download of a file, we will filter them. If found, we will
        // set the file download URL as the response content data.
        $body = $response->getHeader('location') ?: json_decode($response->getBody());

        return new Response(
            $response->getStatusCode(), $body, $response->getHeaders()
        );
    }

    /**
     * @inheritDoc
     */
    public function isOauth()
    {
        return $this->isOauth;
    }

    /**
     * Return the client.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
