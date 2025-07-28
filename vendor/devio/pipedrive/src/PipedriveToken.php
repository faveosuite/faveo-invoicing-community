<?php

namespace Devio\Pipedrive;

use GuzzleHttp\Client as GuzzleClient;

class PipedriveToken
{
    /**
     * The access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * The expiry date.
     *
     * @var string
     */
    protected $expiresAt;

    /**
     * The refresh token.
     *
     * @var string
     */
    protected $refreshToken;

    /**
     * PipedriveToken constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Get the access token.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the expiry date.
     *
     * @return string
     */
    public function expiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Get the refresh token.
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Check if the access token exists.
     *
     * @return bool
     */
    public function valid()
    {
        return ! empty($this->accessToken);
    }

    /**
     * Refresh the token only if needed.
     *
     * @param $pipedrive
     */
    public function refreshIfNeeded($pipedrive)
    {
        if (! $this->needsRefresh()) {
            return;
        }

        $client = new GuzzleClient([
            'auth' => [
                $pipedrive->getClientId(),
                $pipedrive->getClientSecret()
            ]
        ]);

        $response = $client->request('POST', 'https://oauth.pipedrive.com/oauth/token', [
            'form_params' => [
                'grant_type'   => 'refresh_token',
                'refresh_token' => $this->refreshToken
            ]
        ]);

        $tokenInstance = json_decode($response->getBody());

        $this->accessToken = $tokenInstance->access_token;
        $this->expiresAt = time() + $tokenInstance->expires_in;
        $this->refreshToken = $tokenInstance->refresh_token;

        $storage = $pipedrive->getStorage();

        $storage->setToken($this);
    }

    /**
     * Check if the token needs to be refreshed.
     *
     * @return bool
     */
    public function needsRefresh()
    {
        return (int) $this->expiresAt - time() < 1;
    }
}
