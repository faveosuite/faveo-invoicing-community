<?php

namespace Devio\Pipedrive\Http;

interface Client
{
    /**
     * Perform a GET request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function get($url, $parameters = []);

    /**
     * Perform a POST request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function post($url, $parameters = []);

    /**
     * Perform a PUT request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function put($url, $parameters = []);

    /**
     * Perform a DELETE request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function delete($url, $parameters = []);

    /**
     * Check if the client is configured for OAuth.
     *
     * @return bool
     */
    public function isOauth();
}
