<?php

namespace Devio\Pipedrive;

use Devio\Pipedrive\Http\PipedriveClient4;
use Devio\Pipedrive\Resources\Activities;
use Devio\Pipedrive\Resources\ActivityFields;
use Devio\Pipedrive\Resources\ActivityTypes;
use Devio\Pipedrive\Resources\Authorizations;
use Devio\Pipedrive\Resources\CallLogs;
use Devio\Pipedrive\Resources\Currencies;
use Devio\Pipedrive\Resources\DealFields;
use Devio\Pipedrive\Resources\Deals;
use Devio\Pipedrive\Resources\EmailMessages;
use Devio\Pipedrive\Resources\EmailThreads;
use Devio\Pipedrive\Resources\Files;
use Devio\Pipedrive\Resources\Filters;
use Devio\Pipedrive\Resources\GlobalMessages;
use Devio\Pipedrive\Resources\Goals;
use Devio\Pipedrive\Resources\ItemSearch;
use Devio\Pipedrive\Resources\Leads;
use Devio\Pipedrive\Resources\NoteFields;
use Devio\Pipedrive\Resources\Notes;
use Devio\Pipedrive\Resources\OrganizationFields;
use Devio\Pipedrive\Resources\OrganizationRelationships;
use Devio\Pipedrive\Resources\Organizations;
use Devio\Pipedrive\Resources\PermissionSets;
use Devio\Pipedrive\Resources\PersonFields;
use Devio\Pipedrive\Resources\Persons;
use Devio\Pipedrive\Resources\Pipelines;
use Devio\Pipedrive\Resources\ProductFields;
use Devio\Pipedrive\Resources\Products;
use Devio\Pipedrive\Resources\PushNotifications;
use Devio\Pipedrive\Resources\Recents;
use Devio\Pipedrive\Resources\Roles;
use Devio\Pipedrive\Resources\SearchResults;
use Devio\Pipedrive\Resources\Stages;
use Devio\Pipedrive\Resources\UserConnections;
use Devio\Pipedrive\Resources\Users;
use Devio\Pipedrive\Resources\UserSettings;
use Devio\Pipedrive\Resources\Webhooks;
use Illuminate\Support\Str;
use Devio\Pipedrive\Http\Request;
use Devio\Pipedrive\Http\PipedriveClient;
use GuzzleHttp\Client as GuzzleClient;

/**
 * @method Activities activities()
 * @method ActivityFields activityFields()
 * @method ActivityTypes activityTypes()
 * @method Authorizations authorizations()
 * @method CallLogs callLogs()
 * @method Currencies currencies()
 * @method DealFields dealFields()
 * @method Deals deals()
 * @method EmailMessages emailMessages()
 * @method EmailThreads emailThreads()
 * @method Files files()
 * @method Filters filters()
 * @method GlobalMessages globalMessages()
 * @method Goals goals()
 * @method ItemSearch itemSearch()
 * @method Leads leads()
 * @method NoteFields noteFields()
 * @method Notes notes()
 * @method OrganizationFields organizationFields()
 * @method OrganizationRelationships organizationRelationships()
 * @method Organizations organizations()
 * @method PermissionSets permissionSets()
 * @method PersonFields personFields()
 * @method Persons persons()
 * @method Pipelines pipelines()
 * @method ProductFields productFields()
 * @method Products products()
 * @method PushNotifications pushNotifications()
 * @method Recents recents()
 * @method Roles roles()
 * @method SearchResults searchResults()
 * @method Stages stages()
 * @method UserConnections userConnections()
 * @method Users users()
 * @method UserSettings userSettings()
 * @method Webhooks webhooks()
 * @property-read Activities $activities
 * @property-read ActivityFields $activityFields
 * @property-read ActivityTypes $activityTypes
 * @property-read Authorizations $authorizations
 * @property-read CallLogs $callLogs
 * @property-read Currencies $currencies
 * @property-read DealFields $dealFields
 * @property-read Deals $deals
 * @property-read EmailMessages $emailMessages
 * @property-read EmailThreads $emailThreads
 * @property-read Files $files
 * @property-read Filters $filters
 * @property-read GlobalMessages $globalMessages
 * @property-read Goals $goals
 * @property-read ItemSearch $itemSearch
 * @property-read Leads $leads
 * @property-read NoteFields $noteFields
 * @property-read Notes $notes
 * @property-read OrganizationFields $organizationFields
 * @property-read OrganizationRelationships $organizationRelationships
 * @property-read Organizations $organizations
 * @property-read PermissionSets $permissionSets
 * @property-read PersonFields $personFields
 * @property-read Persons $persons
 * @property-read Pipelines $pipelines
 * @property-read ProductFields $productFields
 * @property-read Products $products
 * @property-read PushNotifications $pushNotifications
 * @property-read Recents $recents
 * @property-read Roles $roles
 * @property-read SearchResults $searchResults
 * @property-read Stages $stages
 * @property-read UserConnections $userConnections
 * @property-read Users $users
 * @property-read UserSettings $userSettings
 * @property-read Webhooks $webhooks
 */
class Pipedrive
{
    /**
     * The base URI.
     *
     * @var string
     */
    protected $baseURI;

    /**
     * The API token.
     *
     * @var string
     */
    protected $token;

    /**
     * The guzzle version
     *
     * @var int
     */
    protected $guzzleVersion;

    protected $isOauth;

    /**
     * The OAuth client id.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret string.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The OAuth storage.
     *
     * @var mixed
     */
    protected $storage;

    public function isOauth()
    {
        return $this->isOauth;
    }

    /**
     * Pipedrive constructor.
     *
     * @param $token
     */
    public function __construct($token = '', $uri = 'https://api.pipedrive.com/v1/', $guzzleVersion = 6)
    {
        $this->token = $token;
        $this->baseURI = $uri;
        $this->guzzleVersion = $guzzleVersion;

        $this->isOauth = false;
    }

    /**
     * Prepare for OAuth.
     *
     * @param $config
     * @return Pipedrive
     */
    public static function OAuth($config)
    {
        $guzzleVersion = isset($config['guzzleVersion']) ? $config['guzzleVersion'] : 6;

        $new = new self('oauth', 'https://api.pipedrive.com/', $guzzleVersion);

        $new->isOauth = true;

        $new->clientId = $config['clientId'];
        $new->clientSecret = $config['clientSecret'];
        $new->redirectUrl = $config['redirectUrl'];

        $new->storage = $config['storage'];

        return $new;
    }

    /**
     * Get the client ID.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Get the client secret.
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Get the redirect URL.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Get the storage instance.
     *
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Redirect to OAuth.
     */
    public function OAuthRedirect()
    {
        $params = [
            'client_id'    => $this->clientId,
            'state'        => '',
            'redirect_uri' => $this->redirectUrl,
        ];
        $query = http_build_query($params);
        $url = 'https://oauth.pipedrive.com/oauth/authorize?' . $query;
        header('Location: ' . $url);
        exit;
    }

    /**
     * Get current OAuth access token object (which includes refreshToken and expiresAt)
     */
    public function getAccessToken()
    {
        return $this->storage->getToken();
    }

    /**
     * OAuth authorization.
     *
     * @param $code
     */
    public function authorize($code)
    {
        $client = new GuzzleClient([
            'auth' => [
                $this->getClientId(),
                $this->getClientSecret()
            ]
        ]);
        $response = $client->request('POST', 'https://oauth.pipedrive.com/oauth/token', [
            'form_params' => [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $this->redirectUrl,
            ]
        ]);
        $resBody = json_decode($response->getBody());

        $token = new PipedriveToken([
            'accessToken'  => $resBody->access_token,
            'expiresAt'    => time() + $resBody->expires_in,
            'refreshToken' => $resBody->refresh_token,
        ]);

        $this->storage->setToken($token);
    }

    /**
     * Get the resource instance.
     *
     * @param $resource
     * @return mixed
     */
    public function make($resource)
    {
        $class = $this->resolveClassPath($resource);

        return new $class($this->getRequest());
    }

    /**
     * Get the resource path.
     *
     * @param $resource
     * @return string
     */
    protected function resolveClassPath($resource)
    {
        return 'Devio\\Pipedrive\\Resources\\' . Str::studly($resource);
    }

    /**
     * Get the request instance.
     *
     * @return Request
     */
    public function getRequest()
    {
        return new Request($this->getClient());
    }

    /**
     * Get the HTTP client instance.
     *
     * @return Client
     */
    protected function getClient()
    {
        if ($this->guzzleVersion >= 6) {
            return $this->isOauth()
                ? PipedriveClient::OAuth($this->getBaseURI(), $this->storage, $this)
                : new PipedriveClient($this->getBaseURI(), $this->token);
        } else {
            return new PipedriveClient4($this->getBaseURI(), $this->token);
        }
    }

    /**
     * Get the base URI.
     *
     * @return string
     */
    public function getBaseURI()
    {
        return $this->baseURI;
    }

    /**
     * Set the base URI.
     *
     * @param string $baseURI
     */
    public function setBaseURI($baseURI)
    {
        $this->baseURI = $baseURI;
    }

    /**
     * Set the token.
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Any reading will return a resource.
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->make($name);
    }

    /**
     * Methods will also return a resource.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (! in_array($name, get_class_methods(get_class($this)))) {
            return $this->{$name};
        }
    }
}
