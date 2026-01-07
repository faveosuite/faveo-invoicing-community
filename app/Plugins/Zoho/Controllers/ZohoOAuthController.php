<?php

namespace App\Plugins\Zoho\Controllers;

use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ZohoOAuthController extends Controller
{
    public function connectPage()
    {
        return view('zoho::connect');
    }


    public function saveOAuthClientKeys(Request $request)
    {
        $validated = $request->validate([
            'integration_id' => 'required|exists:zoho_integrations,id',
            'client_id'      => 'required|string',
            'client_secret'  => 'required|string',
            'redirect_uri'   => 'required|url',
            'region'         => 'required|in:in,us,eu,au,jp,cn',
        ]);

        ZohoOAuthClient::updateOrCreate(
            ['integration_id' => $validated['integration_id']],
            [
                'client_id'     => $validated['client_id'],
                'client_secret' => $validated['client_secret'],
                'redirect_uri'  => $validated['redirect_uri'],
                'region'        => $validated['region'],
            ]
        );

        return successResponse('OAuth client keys saved successfully');
    }

    public function getAuthorizationUrl(Request $request)
    {
        try {
            $platform = $request->validate([
                'platform' => 'required|in:crm,campaigns',
            ])['platform'];

            $integration = ZohoIntegration::with('client')
                ->where('platform', $platform)
                ->where('is_active', true)
                ->firstOrFail();

            $client = $integration->client;

            return successResponse('',
                ['redirect_url' => $this->authorizationUrl(
                    $client->region,
                    [
                        'client_id' => $client->client_id,
                        'response_type' => 'code',
                        'redirect_uri' => $client->redirect_uri,
                        'scope' => $this->getScopesByPlatform($platform),
                        'access_type' => 'offline',
                        'prompt' => 'consent',
                        'state' => $platform,
                    ]
                )
                ]
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }


    /**
     * Platform → scopes
     */
    protected function getScopesByPlatform(string $platform): string
    {
        $scopes = config("zoho.platforms.$platform.scope");

        if (empty($scopes)) {
            throw new InvalidArgumentException("Scopes not configured for [$platform]");
        }

        return implode(',', $scopes);
    }

    public function handleZohoCallback(Request $request): RedirectResponse
    {
        $platform = $request->input('state');

        if (! $request->filled('code')) {
            return $this->redirectWithMessage(
                false,
                $platform,
                $request->input('error')
            );
        }

        $integration = ZohoIntegration::with('client')
            ->where('platform', $platform)
            ->where('is_active', true)
            ->firstOrFail();

        $client = $integration->client;

        $response = Http::asForm()->post(
            $this->tokenUrl($client->region),
            [
                'client_id' => $client->client_id,
                'client_secret' => $client->client_secret,
                'grant_type' => 'authorization_code',
                'code' => $request->input('code'),
                'redirect_uri' => $client->redirect_uri,
            ]
        );

        $data = $response->json();

        if (empty($data['access_token'])) {
            return $this->redirectWithMessage(
                false,
                $platform,
                $data['error_description'] ?? 'OAuth failed'
            );
        }

        $this->storeTokenForPlatform($integration, $data);

        return $this->redirectWithMessage(true, $platform);
    }


    /**
     * Store tokens per platform
     */
    protected function storeTokenForPlatform(
        ZohoIntegration $integration,
        array           $data
    ): void
    {
        ZohoOAuthToken::updateOrCreate(
            ['integration_id' => $integration->id],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_at' => now()->addSeconds($data['expires_in']),
                'scope' => $data['scope'] ?? null,
                'api_domain' => $data['api_domain'] ?? null,
            ]
        );
    }

    /**
     * Redirect with query parameter message
     */
    private function redirectWithMessage(
        bool $success,
        string $platform,
        ?string $message = null
    ): RedirectResponse {

        $path = config("zoho.platforms.$platform.settings_url");
        $url  = url($path);

        return redirect()->to(
            $url . '?' . http_build_query([
                'zoho_status'   => $success ? 'success' : 'error',
                'zoho_platform' => $platform,
                'message'       => $message
                    ?? ($success
                        ? 'Zoho connected successfully'
                        : 'Zoho connection failed'),
            ])
        );
    }

    public function accountsBaseUrl(string $region): string
    {
        return sprintf('https://%s', getZohoRegion($region)->accountsDomain());
    }

    public function authorizationUrl(
        string $region,
        array  $queryParams
    ): string
    {
        return sprintf(
            '%s/oauth/v2/auth?%s',
            $this->accountsBaseUrl($region),
            http_build_query($queryParams)
        );
    }

    public function tokenUrl(string $region): string
    {
        return sprintf(
            '%s/oauth/v2/token',
            $this->accountsBaseUrl($region)
        );
    }
}