<?php

namespace App\Plugins\Zoho\Controllers;

use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use DB;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ZohoOAuthController extends Controller
{
    /**
     * Return the list of Zoho integrations as JSON for the Vue settings page.
     */
    public function getIntegrations(): \Illuminate\Http\JsonResponse
    {
        $integrations = ZohoIntegration::with('token')
            ->select('id', 'platform', 'description', 'is_active')
            ->get()
            ->map(fn ($i): array => [
                'id' => $i->id,
                'platform' => $i->platform,
                'description' => $i->description,
                'is_active' => $i->is_active,
                'has_token' => $i->token !== null,
            ]);

        return successResponse('', $integrations);
    }

    public function getOauthClientKeys(mixed $integration): \Illuminate\Http\JsonResponse
    {
        $client = ZohoOAuthClient::where('integration_id', $integration)->first();

        return successResponse('', $client);
    }

    public function saveOAuthClientKeys(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'integration_id' => ['required', 'exists:zoho_integrations,id'],
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'region' => ['required', 'in:in,us,eu,au,jp,cn'],
        ]);

        $integration = ZohoIntegration::findOrFail($validated['integration_id']);

        // There is exactly one callback route, so the redirect URI is fixed —
        // derive it instead of trusting client input (a mismatch breaks OAuth).
        ZohoOAuthClient::updateOrCreate(
            ['integration_id' => $integration->id],
            [
                'client_id' => $validated['client_id'],
                'client_secret' => $validated['client_secret'],
                'redirect_uri' => url('zoho/oauth/callback'),
                'region' => $validated['region'],
            ]
        );

        return successResponse('', [
            'redirect_url' => $this->getAuthorizationUrlByPlatform(
                $integration->platform,
            ),
        ]);
    }

    public function getAuthorizationUrlByPlatform(string $platform): string
    {
        $integration = ZohoIntegration::with('client')
            ->where('platform', $platform)
            ->firstOrFail();

        /** @var \App\Plugins\Zoho\Models\ZohoOAuthClient $client */
        $client = $integration->client;

        if (! $client) {
            throw new Exception('OAuth client not configured');
        }

        return $this->authorizationUrl(
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
        );
    }

    /**
     * Platform → scopes.
     */
    protected function getScopesByPlatform(string $platform): string
    {
        $scopes = config(sprintf('zoho.platforms.%s.scope', $platform));

        if (empty($scopes)) {
            throw new InvalidArgumentException(sprintf('Scopes not configured for [%s]', $platform));
        }

        return implode(',', $scopes);
    }

    public function handleZohoCallback(Request $request): RedirectResponse
    {
        $platform = $request->input('state');

        if (! $request->filled('code')) {
            return $this->redirectWithMessage(
                success: false,
                platform: $platform,
                message: $request->input('error')
            );
        }

        $integration = ZohoIntegration::with('client')
            ->where('platform', $platform)
            ->firstOrFail();

        /** @var \App\Plugins\Zoho\Models\ZohoOAuthClient $client */
        $client = $integration->client;

        // A Zoho authorization code is data-center-specific: it must be redeemed
        // at the same DC where the user authorized. Zoho reports the actual DC in
        // the callback's `location` param, which can differ from the region the
        // admin picked. Honor `location` (falling back to the stored region) and
        // persist it so later API calls hit the correct DC too.
        $region = $request->input('location', $client->region);

        if ($region !== $client->region) {
            $client->update(['region' => $region]);
        }

        $response = Http::asForm()->post(
            $this->tokenUrl($region),
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
                success: false,
                platform: $platform,
                message: $data['error_description'] ?? 'OAuth failed'
            );
        }

        $this->storeTokenForPlatform($integration, $data);

        return $this->redirectWithMessage(success: true, platform: $platform);
    }

    /**
     * Store tokens per platform.
     *
     * @param  array<mixed>  $data
     */
    protected function storeTokenForPlatform(
        ZohoIntegration $integration,
        array $data
    ): void {
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

        $integration->update([
            'is_active' => true,
        ]);
    }

    /**
     * Redirect with query parameter message.
     */
    private function redirectWithMessage(
        bool $success,
        string $platform,
        ?string $message = null
    ): RedirectResponse {
        $path = config(sprintf('zoho.platforms.%s.settings_url', $platform));
        $url = (string) url($path);

        return redirect()->to(
            $url.'?'.http_build_query([
                'zoho_status' => $success ? 'success' : 'error',
                'zoho_platform' => $platform,
                'message' => $message
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

    /**
     * @param  array<mixed>  $queryParams
     */
    public function authorizationUrl(
        string $region,
        array $queryParams
    ): string {
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

    public function toggleIntegration(Request $request, mixed $id): \Illuminate\Http\JsonResponse
    {
        $newValue = $request->boolean('is_active') ? 1 : 0;

        DB::table('zoho_integrations')
            ->where('id', (int) $id)
            ->update(['is_active' => $newValue]);

        return successResponse(__('message.zoho_status'), ['is_active' => (bool) $newValue]);
    }
}
