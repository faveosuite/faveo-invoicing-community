# Pipedrive client for API v1

## Getting started

Please follow the [installation procedure](#installation--usage) and then run the following:

### With a pre-set API token

```php
<?php

use Pipedrive\versions\v1\Configuration;

require_once('/path/to/client/vendor/autoload.php');

// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');

$apiInstance = new Pipedrive\versions\v1\Api\ActivitiesApi(
// If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
// This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getActivities();
    echo '<pre>';
    print_r($result);
    echo '</pre>';
} catch (Exception $e) {
    echo 'Exception when calling ActivitiesApi->getActivities: ', $e->getMessage(), PHP_EOL;
}

?>
```

### With OAuth 2.0

In order to setup authentication in the API client, you need the following information:

| Parameter | Description |
|-----------|-------------|
| oAuthClientId | OAuth 2.0 Client ID |
| oAuthClientSecret | OAuth 2.0 Client Secret |
| oAuthRedirectUri | OAuth 2.0 Redirection endpoint or Callback Uri |


The API client can be initialized as following:

```php
$oAuthClientId = 'oAuthClientId'; // OAuth 2 Client ID
$oAuthClientSecret = 'oAuthClientSecret'; // OAuth 2 Client Secret
$oAuthRedirectUri = 'https://example.com/oauth/callback'; // OAuth 2 Redirection endpoint or Callback Uri

$config = (new Pipedrive\versions\v1\Configuration());
$config->setClientId($oAuthClientId);
$config->setClientSecret($oAuthClientSecret);
$config->setOauthRedirectUri($oAuthRedirectUri);

$dealsApiInstance = new DealsApi(null, $config);
```

You must now authorize the client.

### Authorizing your client

Your application must obtain user authorization before it can execute an endpoint call.
The SDK uses *OAuth 2.0 authorization* to obtain a user's consent to perform an API request on the user's behalf.

#### 1. Obtain user consent

To obtain user consent, you must redirect the user to the authorization page. The `getAuthorizationPageUrl()` method creates the URL to the authorization page when you have clientID and OAuthRedirect set in $config
```php
$authUrl = $config->getAuthorizationPageUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
```

#### 2. Handle the OAuth server response

Once the user responds to the consent request, the OAuth 2.0 server responds to your application's access request by redirecting the user to the redirect URI specified in `Configuration`.

If the user approves the request, the authorization code will be sent as the `code` query string:

```
https://example.com/oauth/callback?code=XXXXXXXXXXXXXXXXXXXXXXXXX
```

If the user does not approve the request, the response contains an `error` query string:

```
https://example.com/oauth/callback?error=access_denied
```

#### 3. Authorize the client using the code

After the server receives the code, it can exchange this for an *access token*. The access token is an object containing information for authorizing client requests and refreshing the token itself.

```php
try {
    $config->authorize($_GET['code']);
} catch (Exception $ex) {
    // handle exception
}
```

### Refreshing the token

An access token may expire after sometime. To extend its lifetime, you must refresh the token.

```php
if ($configuration->getExpiresAt() < time()) {
    try {
        $config->refreshToken();
    } catch (Exception $ex) {
        // handle exception
    }
}
```

If a token expires, the SDK will attempt to automatically refresh the token before the next endpoint call requiring authentication.

### Storing an access token for reuse

It is recommended that you store the access token for reuse.

You can store the access token in the `$_SESSION` global or any other persistent storage:

```php
// store token
$_SESSION['access_token'] = $config->getAccessToken();
```

However, since the SDK will attempt to automatically refresh the token when it expires, it is recommended that you register a *token update callback* to detect any change to the access token.

```php
$config->setOAuthTokenUpdateCallback(function ($token) {
    $_SESSION['token'] = $token;
});
```

The token update callback will be fired upon authorization as well as token refresh.

### Creating a client from a stored token

To authorize a client from a stored access token, just set the access token in `Configuration` along with the other configuration parameters before creating the client:

```php
// load token later...
$config->setAccessToken($_SESSION['token']->access_token);

// If you want to set all of the OAuth2 related fields at once from the token gotten from Pipedrive OAuth server
// you can use the updateOAuthRelatedFields() function
$config->updateOAuthRelatedFields($_SESSION['token']);
// This will set the access token, expiresIn, expiresAt, scope and host attributes in the Configuration class
// In order to get automatic access token refreshing, you will still need the client ID, client secret and redirectURI

// Set other configuration, then instantiate client
$activitiesApiInstance = new ActivitiesApi(null, $config);
```

### Revoke token

When there is a need for an application to indicate to the authorization server that user token should be invalidated, use the `revokeToken` method with provided `hint` argument value:

```php
$config = (new Pipedrive\versions\v1\Configuration());
$config->updateOAuthRelatedFields(/* ... */);
```

When configuration is set, just call the method:

```php
// this will revoke all user tokens
$config->revokeToken('refresh_token');

/* OR */

// this will revoke only access token
$config->revokeToken('access_token');
```

### Complete example with OAuth

In this example, the `index.php` will first check if an access token is available for the user. If one is not set, it redirects the user to `authcallback.php` which will obtain an access token and redirect the user back to the `index.php` page.
Now that an access token is set, `index.php` can use the client to make authorized calls to the server.

#### `index.php`

```php
<?php

use Pipedrive\versions\v1\Api\DealsApi;
use Pipedrive\versions\v1\Configuration;

require_once('../../sdks/php/vendor/autoload.php');

session_start();

$config = (new Pipedrive\versions\v1\Configuration());
$config->setOauthRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/authcallback.php');
$config->setClientSecret('YOUR_CLIENT_SECRET');
$config->setClientId('YOUR_CLIENT_ID');

//$usersApiInstance = new UsersApi(null, $config);
$dealsApiInstance = new DealsApi(null, $config);

// check if a token is available
if (isset($_SESSION['token']) && $_SESSION['token']) {
    // set access token in configuration
    $config->updateOAuthRelatedFields($_SESSION['token']);

    try {
        $dealsResponse = $dealsApiInstance->getDeals();
        echo '<pre>';
        print_r($dealsResponse);
        echo '</pre>';
    } catch (Exception $e) {
        echo 'Exception when trying to get deals data', $e, PHP_EOL;
    }
} else {
    header('Location: ' . filter_var($config->getAuthorizationPageUrl(), FILTER_SANITIZE_URL));
}
```

#### `authcallback.php`

```php
<?php
require_once('../../sdks/php/vendor/autoload.php');

use Pipedrive\versions\v1\Configuration;

session_start();

$config = (new Pipedrive\versions\v1\Configuration());

$config->setOauthRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/authcallback.php');
$config->setClientSecret('YOUR_CLIENT_SECRET');
$config->setClientId('YOUR_CLIENT_ID');
$config->setAuthorizationPageUrl('https://oauth.pipedrive.com/oauth/authorize?client_id=YOUR_CLIENT_ID&redirect_uri=http%3A%2F%2Flocalhost%3A8081%2Fauthcallback.php');

$config->setOAuthTokenUpdateCallback(function ($token) {
    $_SESSION['token'] = $token;
});

// if authorization code is absent, redirect to authorization page
if (!isset($_GET['code'])) {
    header('Location: ' . filter_var($config->getAuthorizationPageUrl(), FILTER_SANITIZE_URL));
} else {
    try {
        $config->authorize($_GET['code']);

        // resume user activity
        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    } catch (Exception $ex) {
        print_r($ex);
    }
}
```
## Documentation for API endpoints

All URIs are relative to *https://api.pipedrive.com/v1*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*ActivitiesApi* | [**addActivity**](Api/ActivitiesApi.md#addactivity) | **POST** /activities | Add an activity
*ActivitiesApi* | [**deleteActivity**](Api/ActivitiesApi.md#deleteactivity) | **DELETE** /activities/{id} | Delete an activity
*ActivitiesApi* | [**getActivities**](Api/ActivitiesApi.md#getactivities) | **GET** /activities | Get all activities assigned to a particular user
*ActivitiesApi* | [**getActivitiesCollection**](Api/ActivitiesApi.md#getactivitiescollection) | **GET** /activities/collection | Get all activities collection
*ActivitiesApi* | [**getActivity**](Api/ActivitiesApi.md#getactivity) | **GET** /activities/{id} | Get details of an activity
*ActivitiesApi* | [**updateActivity**](Api/ActivitiesApi.md#updateactivity) | **PUT** /activities/{id} | Update an activity
*ActivityFieldsApi* | [**getActivityFields**](Api/ActivityFieldsApi.md#getactivityfields) | **GET** /activityFields | Get all activity fields
*ActivityTypesApi* | [**addActivityType**](Api/ActivityTypesApi.md#addactivitytype) | **POST** /activityTypes | Add new activity type
*ActivityTypesApi* | [**deleteActivityType**](Api/ActivityTypesApi.md#deleteactivitytype) | **DELETE** /activityTypes/{id} | Delete an activity type
*ActivityTypesApi* | [**getActivityTypes**](Api/ActivityTypesApi.md#getactivitytypes) | **GET** /activityTypes | Get all activity types
*ActivityTypesApi* | [**updateActivityType**](Api/ActivityTypesApi.md#updateactivitytype) | **PUT** /activityTypes/{id} | Update an activity type
*BillingApi* | [**getCompanyAddons**](Api/BillingApi.md#getcompanyaddons) | **GET** /billing/subscriptions/addons | Get all add-ons for a single company
*CallLogsApi* | [**addCallLog**](Api/CallLogsApi.md#addcalllog) | **POST** /callLogs | Add a call log
*CallLogsApi* | [**addCallLogAudioFile**](Api/CallLogsApi.md#addcalllogaudiofile) | **POST** /callLogs/{id}/recordings | Attach an audio file to the call log
*CallLogsApi* | [**deleteCallLog**](Api/CallLogsApi.md#deletecalllog) | **DELETE** /callLogs/{id} | Delete a call log
*CallLogsApi* | [**getCallLog**](Api/CallLogsApi.md#getcalllog) | **GET** /callLogs/{id} | Get details of a call log
*CallLogsApi* | [**getUserCallLogs**](Api/CallLogsApi.md#getusercalllogs) | **GET** /callLogs | Get all call logs assigned to a particular user
*ChannelsApi* | [**addChannel**](Api/ChannelsApi.md#addchannel) | **POST** /channels | Add a channel
*ChannelsApi* | [**deleteChannel**](Api/ChannelsApi.md#deletechannel) | **DELETE** /channels/{id} | Delete a channel
*ChannelsApi* | [**deleteConversation**](Api/ChannelsApi.md#deleteconversation) | **DELETE** /channels/{channel-id}/conversations/{conversation-id} | Delete a conversation
*ChannelsApi* | [**receiveMessage**](Api/ChannelsApi.md#receivemessage) | **POST** /channels/messages/receive | Receives an incoming message
*CurrenciesApi* | [**getCurrencies**](Api/CurrenciesApi.md#getcurrencies) | **GET** /currencies | Get all supported currencies
*DealFieldsApi* | [**addDealField**](Api/DealFieldsApi.md#adddealfield) | **POST** /dealFields | Add a new deal field
*DealFieldsApi* | [**deleteDealField**](Api/DealFieldsApi.md#deletedealfield) | **DELETE** /dealFields/{id} | Delete a deal field
*DealFieldsApi* | [**deleteDealFields**](Api/DealFieldsApi.md#deletedealfields) | **DELETE** /dealFields | Delete multiple deal fields in bulk
*DealFieldsApi* | [**getDealField**](Api/DealFieldsApi.md#getdealfield) | **GET** /dealFields/{id} | Get one deal field
*DealFieldsApi* | [**getDealFields**](Api/DealFieldsApi.md#getdealfields) | **GET** /dealFields | Get all deal fields
*DealFieldsApi* | [**updateDealField**](Api/DealFieldsApi.md#updatedealfield) | **PUT** /dealFields/{id} | Update a deal field
*DealsApi* | [**addDeal**](Api/DealsApi.md#adddeal) | **POST** /deals | Add a deal
*DealsApi* | [**addDealFollower**](Api/DealsApi.md#adddealfollower) | **POST** /deals/{id}/followers | Add a follower to a deal
*DealsApi* | [**addDealParticipant**](Api/DealsApi.md#adddealparticipant) | **POST** /deals/{id}/participants | Add a participant to a deal
*DealsApi* | [**addDealProduct**](Api/DealsApi.md#adddealproduct) | **POST** /deals/{id}/products | Add a product to a deal
*DealsApi* | [**deleteDeal**](Api/DealsApi.md#deletedeal) | **DELETE** /deals/{id} | Delete a deal
*DealsApi* | [**deleteDealFollower**](Api/DealsApi.md#deletedealfollower) | **DELETE** /deals/{id}/followers/{follower_id} | Delete a follower from a deal
*DealsApi* | [**deleteDealParticipant**](Api/DealsApi.md#deletedealparticipant) | **DELETE** /deals/{id}/participants/{deal_participant_id} | Delete a participant from a deal
*DealsApi* | [**deleteDealProduct**](Api/DealsApi.md#deletedealproduct) | **DELETE** /deals/{id}/products/{product_attachment_id} | Delete an attached product from a deal
*DealsApi* | [**duplicateDeal**](Api/DealsApi.md#duplicatedeal) | **POST** /deals/{id}/duplicate | Duplicate deal
*DealsApi* | [**getArchivedDeals**](Api/DealsApi.md#getarchiveddeals) | **GET** /deals/archived | Get all archived deals
*DealsApi* | [**getArchivedDealsSummary**](Api/DealsApi.md#getarchiveddealssummary) | **GET** /deals/summary/archived | Get archived deals summary
*DealsApi* | [**getArchivedDealsTimeline**](Api/DealsApi.md#getarchiveddealstimeline) | **GET** /deals/timeline/archived | Get archived deals timeline
*DealsApi* | [**getDeal**](Api/DealsApi.md#getdeal) | **GET** /deals/{id} | Get details of a deal
*DealsApi* | [**getDealActivities**](Api/DealsApi.md#getdealactivities) | **GET** /deals/{id}/activities | List activities associated with a deal
*DealsApi* | [**getDealChangelog**](Api/DealsApi.md#getdealchangelog) | **GET** /deals/{id}/changelog | List updates about deal field values
*DealsApi* | [**getDealFiles**](Api/DealsApi.md#getdealfiles) | **GET** /deals/{id}/files | List files attached to a deal
*DealsApi* | [**getDealFollowers**](Api/DealsApi.md#getdealfollowers) | **GET** /deals/{id}/followers | List followers of a deal
*DealsApi* | [**getDealMailMessages**](Api/DealsApi.md#getdealmailmessages) | **GET** /deals/{id}/mailMessages | List mail messages associated with a deal
*DealsApi* | [**getDealParticipants**](Api/DealsApi.md#getdealparticipants) | **GET** /deals/{id}/participants | List participants of a deal
*DealsApi* | [**getDealParticipantsChangelog**](Api/DealsApi.md#getdealparticipantschangelog) | **GET** /deals/{id}/participantsChangelog | List updates about participants of a deal
*DealsApi* | [**getDealPersons**](Api/DealsApi.md#getdealpersons) | **GET** /deals/{id}/persons | List all persons associated with a deal
*DealsApi* | [**getDealProducts**](Api/DealsApi.md#getdealproducts) | **GET** /deals/{id}/products | List products attached to a deal
*DealsApi* | [**getDealUpdates**](Api/DealsApi.md#getdealupdates) | **GET** /deals/{id}/flow | List updates about a deal
*DealsApi* | [**getDealUsers**](Api/DealsApi.md#getdealusers) | **GET** /deals/{id}/permittedUsers | List permitted users
*DealsApi* | [**getDeals**](Api/DealsApi.md#getdeals) | **GET** /deals | Get all deals
*DealsApi* | [**getDealsCollection**](Api/DealsApi.md#getdealscollection) | **GET** /deals/collection | Get all deals collection
*DealsApi* | [**getDealsSummary**](Api/DealsApi.md#getdealssummary) | **GET** /deals/summary | Get deals summary
*DealsApi* | [**getDealsTimeline**](Api/DealsApi.md#getdealstimeline) | **GET** /deals/timeline | Get deals timeline
*DealsApi* | [**mergeDeals**](Api/DealsApi.md#mergedeals) | **PUT** /deals/{id}/merge | Merge two deals
*DealsApi* | [**searchDeals**](Api/DealsApi.md#searchdeals) | **GET** /deals/search | Search deals
*DealsApi* | [**updateDeal**](Api/DealsApi.md#updatedeal) | **PUT** /deals/{id} | Update a deal
*DealsApi* | [**updateDealProduct**](Api/DealsApi.md#updatedealproduct) | **PUT** /deals/{id}/products/{product_attachment_id} | Update the product attached to a deal
*FilesApi* | [**addFile**](Api/FilesApi.md#addfile) | **POST** /files | Add file
*FilesApi* | [**addFileAndLinkIt**](Api/FilesApi.md#addfileandlinkit) | **POST** /files/remote | Create a remote file and link it to an item
*FilesApi* | [**deleteFile**](Api/FilesApi.md#deletefile) | **DELETE** /files/{id} | Delete a file
*FilesApi* | [**downloadFile**](Api/FilesApi.md#downloadfile) | **GET** /files/{id}/download | Download one file
*FilesApi* | [**getFile**](Api/FilesApi.md#getfile) | **GET** /files/{id} | Get one file
*FilesApi* | [**getFiles**](Api/FilesApi.md#getfiles) | **GET** /files | Get all files
*FilesApi* | [**linkFileToItem**](Api/FilesApi.md#linkfiletoitem) | **POST** /files/remoteLink | Link a remote file to an item
*FilesApi* | [**updateFile**](Api/FilesApi.md#updatefile) | **PUT** /files/{id} | Update file details
*FiltersApi* | [**addFilter**](Api/FiltersApi.md#addfilter) | **POST** /filters | Add a new filter
*FiltersApi* | [**deleteFilter**](Api/FiltersApi.md#deletefilter) | **DELETE** /filters/{id} | Delete a filter
*FiltersApi* | [**deleteFilters**](Api/FiltersApi.md#deletefilters) | **DELETE** /filters | Delete multiple filters in bulk
*FiltersApi* | [**getFilter**](Api/FiltersApi.md#getfilter) | **GET** /filters/{id} | Get one filter
*FiltersApi* | [**getFilterHelpers**](Api/FiltersApi.md#getfilterhelpers) | **GET** /filters/helpers | Get all filter helpers
*FiltersApi* | [**getFilters**](Api/FiltersApi.md#getfilters) | **GET** /filters | Get all filters
*FiltersApi* | [**updateFilter**](Api/FiltersApi.md#updatefilter) | **PUT** /filters/{id} | Update filter
*GoalsApi* | [**addGoal**](Api/GoalsApi.md#addgoal) | **POST** /goals | Add a new goal
*GoalsApi* | [**deleteGoal**](Api/GoalsApi.md#deletegoal) | **DELETE** /goals/{id} | Delete existing goal
*GoalsApi* | [**getGoalResult**](Api/GoalsApi.md#getgoalresult) | **GET** /goals/{id}/results | Get result of a goal
*GoalsApi* | [**getGoals**](Api/GoalsApi.md#getgoals) | **GET** /goals/find | Find goals
*GoalsApi* | [**updateGoal**](Api/GoalsApi.md#updategoal) | **PUT** /goals/{id} | Update existing goal
*ItemSearchApi* | [**searchItem**](Api/ItemSearchApi.md#searchitem) | **GET** /itemSearch | Perform a search from multiple item types
*ItemSearchApi* | [**searchItemByField**](Api/ItemSearchApi.md#searchitembyfield) | **GET** /itemSearch/field | Perform a search using a specific field from an item type
*LeadFieldsApi* | [**getLeadFields**](Api/LeadFieldsApi.md#getleadfields) | **GET** /leadFields | Get all lead fields
*LeadLabelsApi* | [**addLeadLabel**](Api/LeadLabelsApi.md#addleadlabel) | **POST** /leadLabels | Add a lead label
*LeadLabelsApi* | [**deleteLeadLabel**](Api/LeadLabelsApi.md#deleteleadlabel) | **DELETE** /leadLabels/{id} | Delete a lead label
*LeadLabelsApi* | [**getLeadLabels**](Api/LeadLabelsApi.md#getleadlabels) | **GET** /leadLabels | Get all lead labels
*LeadLabelsApi* | [**updateLeadLabel**](Api/LeadLabelsApi.md#updateleadlabel) | **PATCH** /leadLabels/{id} | Update a lead label
*LeadSourcesApi* | [**getLeadSources**](Api/LeadSourcesApi.md#getleadsources) | **GET** /leadSources | Get all lead sources
*LeadsApi* | [**addLead**](Api/LeadsApi.md#addlead) | **POST** /leads | Add a lead
*LeadsApi* | [**deleteLead**](Api/LeadsApi.md#deletelead) | **DELETE** /leads/{id} | Delete a lead
*LeadsApi* | [**getArchivedLeads**](Api/LeadsApi.md#getarchivedleads) | **GET** /leads/archived | Get all archived leads
*LeadsApi* | [**getLead**](Api/LeadsApi.md#getlead) | **GET** /leads/{id} | Get one lead
*LeadsApi* | [**getLeadUsers**](Api/LeadsApi.md#getleadusers) | **GET** /leads/{id}/permittedUsers | List permitted users
*LeadsApi* | [**getLeads**](Api/LeadsApi.md#getleads) | **GET** /leads | Get all leads
*LeadsApi* | [**searchLeads**](Api/LeadsApi.md#searchleads) | **GET** /leads/search | Search leads
*LeadsApi* | [**updateLead**](Api/LeadsApi.md#updatelead) | **PATCH** /leads/{id} | Update a lead
*LegacyTeamsApi* | [**addTeam**](Api/LegacyTeamsApi.md#addteam) | **POST** /legacyTeams | Add a new team
*LegacyTeamsApi* | [**addTeamUser**](Api/LegacyTeamsApi.md#addteamuser) | **POST** /legacyTeams/{id}/users | Add users to a team
*LegacyTeamsApi* | [**deleteTeamUser**](Api/LegacyTeamsApi.md#deleteteamuser) | **DELETE** /legacyTeams/{id}/users | Delete users from a team
*LegacyTeamsApi* | [**getTeam**](Api/LegacyTeamsApi.md#getteam) | **GET** /legacyTeams/{id} | Get a single team
*LegacyTeamsApi* | [**getTeamUsers**](Api/LegacyTeamsApi.md#getteamusers) | **GET** /legacyTeams/{id}/users | Get all users in a team
*LegacyTeamsApi* | [**getTeams**](Api/LegacyTeamsApi.md#getteams) | **GET** /legacyTeams | Get all teams
*LegacyTeamsApi* | [**getUserTeams**](Api/LegacyTeamsApi.md#getuserteams) | **GET** /legacyTeams/user/{id} | Get all teams of a user
*LegacyTeamsApi* | [**updateTeam**](Api/LegacyTeamsApi.md#updateteam) | **PUT** /legacyTeams/{id} | Update a team
*MailboxApi* | [**deleteMailThread**](Api/MailboxApi.md#deletemailthread) | **DELETE** /mailbox/mailThreads/{id} | Delete mail thread
*MailboxApi* | [**getMailMessage**](Api/MailboxApi.md#getmailmessage) | **GET** /mailbox/mailMessages/{id} | Get one mail message
*MailboxApi* | [**getMailThread**](Api/MailboxApi.md#getmailthread) | **GET** /mailbox/mailThreads/{id} | Get one mail thread
*MailboxApi* | [**getMailThreadMessages**](Api/MailboxApi.md#getmailthreadmessages) | **GET** /mailbox/mailThreads/{id}/mailMessages | Get all mail messages of mail thread
*MailboxApi* | [**getMailThreads**](Api/MailboxApi.md#getmailthreads) | **GET** /mailbox/mailThreads | Get mail threads
*MailboxApi* | [**updateMailThreadDetails**](Api/MailboxApi.md#updatemailthreaddetails) | **PUT** /mailbox/mailThreads/{id} | Update mail thread details
*MeetingsApi* | [**deleteUserProviderLink**](Api/MeetingsApi.md#deleteuserproviderlink) | **DELETE** /meetings/userProviderLinks/{id} | Delete the link between a user and the installed video call integration
*MeetingsApi* | [**saveUserProviderLink**](Api/MeetingsApi.md#saveuserproviderlink) | **POST** /meetings/userProviderLinks | Link a user with the installed video call integration
*NoteFieldsApi* | [**getNoteFields**](Api/NoteFieldsApi.md#getnotefields) | **GET** /noteFields | Get all note fields
*NotesApi* | [**addNote**](Api/NotesApi.md#addnote) | **POST** /notes | Add a note
*NotesApi* | [**addNoteComment**](Api/NotesApi.md#addnotecomment) | **POST** /notes/{id}/comments | Add a comment to a note
*NotesApi* | [**deleteComment**](Api/NotesApi.md#deletecomment) | **DELETE** /notes/{id}/comments/{commentId} | Delete a comment related to a note
*NotesApi* | [**deleteNote**](Api/NotesApi.md#deletenote) | **DELETE** /notes/{id} | Delete a note
*NotesApi* | [**getComment**](Api/NotesApi.md#getcomment) | **GET** /notes/{id}/comments/{commentId} | Get one comment
*NotesApi* | [**getNote**](Api/NotesApi.md#getnote) | **GET** /notes/{id} | Get one note
*NotesApi* | [**getNoteComments**](Api/NotesApi.md#getnotecomments) | **GET** /notes/{id}/comments | Get all comments for a note
*NotesApi* | [**getNotes**](Api/NotesApi.md#getnotes) | **GET** /notes | Get all notes
*NotesApi* | [**updateCommentForNote**](Api/NotesApi.md#updatecommentfornote) | **PUT** /notes/{id}/comments/{commentId} | Update a comment related to a note
*NotesApi* | [**updateNote**](Api/NotesApi.md#updatenote) | **PUT** /notes/{id} | Update a note
*OrganizationFieldsApi* | [**addOrganizationField**](Api/OrganizationFieldsApi.md#addorganizationfield) | **POST** /organizationFields | Add a new organization field
*OrganizationFieldsApi* | [**deleteOrganizationField**](Api/OrganizationFieldsApi.md#deleteorganizationfield) | **DELETE** /organizationFields/{id} | Delete an organization field
*OrganizationFieldsApi* | [**deleteOrganizationFields**](Api/OrganizationFieldsApi.md#deleteorganizationfields) | **DELETE** /organizationFields | Delete multiple organization fields in bulk
*OrganizationFieldsApi* | [**getOrganizationField**](Api/OrganizationFieldsApi.md#getorganizationfield) | **GET** /organizationFields/{id} | Get one organization field
*OrganizationFieldsApi* | [**getOrganizationFields**](Api/OrganizationFieldsApi.md#getorganizationfields) | **GET** /organizationFields | Get all organization fields
*OrganizationFieldsApi* | [**updateOrganizationField**](Api/OrganizationFieldsApi.md#updateorganizationfield) | **PUT** /organizationFields/{id} | Update an organization field
*OrganizationRelationshipsApi* | [**addOrganizationRelationship**](Api/OrganizationRelationshipsApi.md#addorganizationrelationship) | **POST** /organizationRelationships | Create an organization relationship
*OrganizationRelationshipsApi* | [**deleteOrganizationRelationship**](Api/OrganizationRelationshipsApi.md#deleteorganizationrelationship) | **DELETE** /organizationRelationships/{id} | Delete an organization relationship
*OrganizationRelationshipsApi* | [**getOrganizationRelationship**](Api/OrganizationRelationshipsApi.md#getorganizationrelationship) | **GET** /organizationRelationships/{id} | Get one organization relationship
*OrganizationRelationshipsApi* | [**getOrganizationRelationships**](Api/OrganizationRelationshipsApi.md#getorganizationrelationships) | **GET** /organizationRelationships | Get all relationships for organization
*OrganizationRelationshipsApi* | [**updateOrganizationRelationship**](Api/OrganizationRelationshipsApi.md#updateorganizationrelationship) | **PUT** /organizationRelationships/{id} | Update an organization relationship
*OrganizationsApi* | [**addOrganization**](Api/OrganizationsApi.md#addorganization) | **POST** /organizations | Add an organization
*OrganizationsApi* | [**addOrganizationFollower**](Api/OrganizationsApi.md#addorganizationfollower) | **POST** /organizations/{id}/followers | Add a follower to an organization
*OrganizationsApi* | [**deleteOrganization**](Api/OrganizationsApi.md#deleteorganization) | **DELETE** /organizations/{id} | Delete an organization
*OrganizationsApi* | [**deleteOrganizationFollower**](Api/OrganizationsApi.md#deleteorganizationfollower) | **DELETE** /organizations/{id}/followers/{follower_id} | Delete a follower from an organization
*OrganizationsApi* | [**getOrganization**](Api/OrganizationsApi.md#getorganization) | **GET** /organizations/{id} | Get details of an organization
*OrganizationsApi* | [**getOrganizationActivities**](Api/OrganizationsApi.md#getorganizationactivities) | **GET** /organizations/{id}/activities | List activities associated with an organization
*OrganizationsApi* | [**getOrganizationChangelog**](Api/OrganizationsApi.md#getorganizationchangelog) | **GET** /organizations/{id}/changelog | List updates about organization field values
*OrganizationsApi* | [**getOrganizationDeals**](Api/OrganizationsApi.md#getorganizationdeals) | **GET** /organizations/{id}/deals | List deals associated with an organization
*OrganizationsApi* | [**getOrganizationFiles**](Api/OrganizationsApi.md#getorganizationfiles) | **GET** /organizations/{id}/files | List files attached to an organization
*OrganizationsApi* | [**getOrganizationFollowers**](Api/OrganizationsApi.md#getorganizationfollowers) | **GET** /organizations/{id}/followers | List followers of an organization
*OrganizationsApi* | [**getOrganizationMailMessages**](Api/OrganizationsApi.md#getorganizationmailmessages) | **GET** /organizations/{id}/mailMessages | List mail messages associated with an organization
*OrganizationsApi* | [**getOrganizationPersons**](Api/OrganizationsApi.md#getorganizationpersons) | **GET** /organizations/{id}/persons | List persons of an organization
*OrganizationsApi* | [**getOrganizationUpdates**](Api/OrganizationsApi.md#getorganizationupdates) | **GET** /organizations/{id}/flow | List updates about an organization
*OrganizationsApi* | [**getOrganizationUsers**](Api/OrganizationsApi.md#getorganizationusers) | **GET** /organizations/{id}/permittedUsers | List permitted users
*OrganizationsApi* | [**getOrganizations**](Api/OrganizationsApi.md#getorganizations) | **GET** /organizations | Get all organizations
*OrganizationsApi* | [**getOrganizationsCollection**](Api/OrganizationsApi.md#getorganizationscollection) | **GET** /organizations/collection | Get all organizations collection
*OrganizationsApi* | [**mergeOrganizations**](Api/OrganizationsApi.md#mergeorganizations) | **PUT** /organizations/{id}/merge | Merge two organizations
*OrganizationsApi* | [**searchOrganization**](Api/OrganizationsApi.md#searchorganization) | **GET** /organizations/search | Search organizations
*OrganizationsApi* | [**updateOrganization**](Api/OrganizationsApi.md#updateorganization) | **PUT** /organizations/{id} | Update an organization
*PermissionSetsApi* | [**getPermissionSet**](Api/PermissionSetsApi.md#getpermissionset) | **GET** /permissionSets/{id} | Get one permission set
*PermissionSetsApi* | [**getPermissionSetAssignments**](Api/PermissionSetsApi.md#getpermissionsetassignments) | **GET** /permissionSets/{id}/assignments | List permission set assignments
*PermissionSetsApi* | [**getPermissionSets**](Api/PermissionSetsApi.md#getpermissionsets) | **GET** /permissionSets | Get all permission sets
*PersonFieldsApi* | [**addPersonField**](Api/PersonFieldsApi.md#addpersonfield) | **POST** /personFields | Add a new person field
*PersonFieldsApi* | [**deletePersonField**](Api/PersonFieldsApi.md#deletepersonfield) | **DELETE** /personFields/{id} | Delete a person field
*PersonFieldsApi* | [**deletePersonFields**](Api/PersonFieldsApi.md#deletepersonfields) | **DELETE** /personFields | Delete multiple person fields in bulk
*PersonFieldsApi* | [**getPersonField**](Api/PersonFieldsApi.md#getpersonfield) | **GET** /personFields/{id} | Get one person field
*PersonFieldsApi* | [**getPersonFields**](Api/PersonFieldsApi.md#getpersonfields) | **GET** /personFields | Get all person fields
*PersonFieldsApi* | [**updatePersonField**](Api/PersonFieldsApi.md#updatepersonfield) | **PUT** /personFields/{id} | Update a person field
*PersonsApi* | [**addPerson**](Api/PersonsApi.md#addperson) | **POST** /persons | Add a person
*PersonsApi* | [**addPersonFollower**](Api/PersonsApi.md#addpersonfollower) | **POST** /persons/{id}/followers | Add a follower to a person
*PersonsApi* | [**addPersonPicture**](Api/PersonsApi.md#addpersonpicture) | **POST** /persons/{id}/picture | Add person picture
*PersonsApi* | [**deletePerson**](Api/PersonsApi.md#deleteperson) | **DELETE** /persons/{id} | Delete a person
*PersonsApi* | [**deletePersonFollower**](Api/PersonsApi.md#deletepersonfollower) | **DELETE** /persons/{id}/followers/{follower_id} | Delete a follower from a person
*PersonsApi* | [**deletePersonPicture**](Api/PersonsApi.md#deletepersonpicture) | **DELETE** /persons/{id}/picture | Delete person picture
*PersonsApi* | [**getPerson**](Api/PersonsApi.md#getperson) | **GET** /persons/{id} | Get details of a person
*PersonsApi* | [**getPersonActivities**](Api/PersonsApi.md#getpersonactivities) | **GET** /persons/{id}/activities | List activities associated with a person
*PersonsApi* | [**getPersonChangelog**](Api/PersonsApi.md#getpersonchangelog) | **GET** /persons/{id}/changelog | List updates about person field values
*PersonsApi* | [**getPersonDeals**](Api/PersonsApi.md#getpersondeals) | **GET** /persons/{id}/deals | List deals associated with a person
*PersonsApi* | [**getPersonFiles**](Api/PersonsApi.md#getpersonfiles) | **GET** /persons/{id}/files | List files attached to a person
*PersonsApi* | [**getPersonFollowers**](Api/PersonsApi.md#getpersonfollowers) | **GET** /persons/{id}/followers | List followers of a person
*PersonsApi* | [**getPersonMailMessages**](Api/PersonsApi.md#getpersonmailmessages) | **GET** /persons/{id}/mailMessages | List mail messages associated with a person
*PersonsApi* | [**getPersonProducts**](Api/PersonsApi.md#getpersonproducts) | **GET** /persons/{id}/products | List products associated with a person
*PersonsApi* | [**getPersonUpdates**](Api/PersonsApi.md#getpersonupdates) | **GET** /persons/{id}/flow | List updates about a person
*PersonsApi* | [**getPersonUsers**](Api/PersonsApi.md#getpersonusers) | **GET** /persons/{id}/permittedUsers | List permitted users
*PersonsApi* | [**getPersons**](Api/PersonsApi.md#getpersons) | **GET** /persons | Get all persons
*PersonsApi* | [**getPersonsCollection**](Api/PersonsApi.md#getpersonscollection) | **GET** /persons/collection | Get all persons collection
*PersonsApi* | [**mergePersons**](Api/PersonsApi.md#mergepersons) | **PUT** /persons/{id}/merge | Merge two persons
*PersonsApi* | [**searchPersons**](Api/PersonsApi.md#searchpersons) | **GET** /persons/search | Search persons
*PersonsApi* | [**updatePerson**](Api/PersonsApi.md#updateperson) | **PUT** /persons/{id} | Update a person
*PipelinesApi* | [**addPipeline**](Api/PipelinesApi.md#addpipeline) | **POST** /pipelines | Add a new pipeline
*PipelinesApi* | [**deletePipeline**](Api/PipelinesApi.md#deletepipeline) | **DELETE** /pipelines/{id} | Delete a pipeline
*PipelinesApi* | [**getPipeline**](Api/PipelinesApi.md#getpipeline) | **GET** /pipelines/{id} | Get one pipeline
*PipelinesApi* | [**getPipelineConversionStatistics**](Api/PipelinesApi.md#getpipelineconversionstatistics) | **GET** /pipelines/{id}/conversion_statistics | Get deals conversion rates in pipeline
*PipelinesApi* | [**getPipelineDeals**](Api/PipelinesApi.md#getpipelinedeals) | **GET** /pipelines/{id}/deals | Get deals in a pipeline
*PipelinesApi* | [**getPipelineMovementStatistics**](Api/PipelinesApi.md#getpipelinemovementstatistics) | **GET** /pipelines/{id}/movement_statistics | Get deals movements in pipeline
*PipelinesApi* | [**getPipelines**](Api/PipelinesApi.md#getpipelines) | **GET** /pipelines | Get all pipelines
*PipelinesApi* | [**updatePipeline**](Api/PipelinesApi.md#updatepipeline) | **PUT** /pipelines/{id} | Update a pipeline
*ProductFieldsApi* | [**addProductField**](Api/ProductFieldsApi.md#addproductfield) | **POST** /productFields | Add a new product field
*ProductFieldsApi* | [**deleteProductField**](Api/ProductFieldsApi.md#deleteproductfield) | **DELETE** /productFields/{id} | Delete a product field
*ProductFieldsApi* | [**deleteProductFields**](Api/ProductFieldsApi.md#deleteproductfields) | **DELETE** /productFields | Delete multiple product fields in bulk
*ProductFieldsApi* | [**getProductField**](Api/ProductFieldsApi.md#getproductfield) | **GET** /productFields/{id} | Get one product field
*ProductFieldsApi* | [**getProductFields**](Api/ProductFieldsApi.md#getproductfields) | **GET** /productFields | Get all product fields
*ProductFieldsApi* | [**updateProductField**](Api/ProductFieldsApi.md#updateproductfield) | **PUT** /productFields/{id} | Update a product field
*ProductsApi* | [**addProduct**](Api/ProductsApi.md#addproduct) | **POST** /products | Add a product
*ProductsApi* | [**addProductFollower**](Api/ProductsApi.md#addproductfollower) | **POST** /products/{id}/followers | Add a follower to a product
*ProductsApi* | [**deleteProduct**](Api/ProductsApi.md#deleteproduct) | **DELETE** /products/{id} | Delete a product
*ProductsApi* | [**deleteProductFollower**](Api/ProductsApi.md#deleteproductfollower) | **DELETE** /products/{id}/followers/{follower_id} | Delete a follower from a product
*ProductsApi* | [**getProduct**](Api/ProductsApi.md#getproduct) | **GET** /products/{id} | Get one product
*ProductsApi* | [**getProductDeals**](Api/ProductsApi.md#getproductdeals) | **GET** /products/{id}/deals | Get deals where a product is attached to
*ProductsApi* | [**getProductFiles**](Api/ProductsApi.md#getproductfiles) | **GET** /products/{id}/files | List files attached to a product
*ProductsApi* | [**getProductFollowers**](Api/ProductsApi.md#getproductfollowers) | **GET** /products/{id}/followers | List followers of a product
*ProductsApi* | [**getProductUsers**](Api/ProductsApi.md#getproductusers) | **GET** /products/{id}/permittedUsers | List permitted users
*ProductsApi* | [**getProducts**](Api/ProductsApi.md#getproducts) | **GET** /products | Get all products
*ProductsApi* | [**searchProducts**](Api/ProductsApi.md#searchproducts) | **GET** /products/search | Search products
*ProductsApi* | [**updateProduct**](Api/ProductsApi.md#updateproduct) | **PUT** /products/{id} | Update a product
*ProjectTemplatesApi* | [**getProjectTemplate**](Api/ProjectTemplatesApi.md#getprojecttemplate) | **GET** /projectTemplates/{id} | Get details of a template
*ProjectTemplatesApi* | [**getProjectTemplates**](Api/ProjectTemplatesApi.md#getprojecttemplates) | **GET** /projectTemplates | Get all project templates
*ProjectTemplatesApi* | [**getProjectsBoard**](Api/ProjectTemplatesApi.md#getprojectsboard) | **GET** /projects/boards/{id} | Get details of a board
*ProjectTemplatesApi* | [**getProjectsPhase**](Api/ProjectTemplatesApi.md#getprojectsphase) | **GET** /projects/phases/{id} | Get details of a phase
*ProjectsApi* | [**addProject**](Api/ProjectsApi.md#addproject) | **POST** /projects | Add a project
*ProjectsApi* | [**archiveProject**](Api/ProjectsApi.md#archiveproject) | **POST** /projects/{id}/archive | Archive a project
*ProjectsApi* | [**deleteProject**](Api/ProjectsApi.md#deleteproject) | **DELETE** /projects/{id} | Delete a project
*ProjectsApi* | [**getProject**](Api/ProjectsApi.md#getproject) | **GET** /projects/{id} | Get details of a project
*ProjectsApi* | [**getProjectActivities**](Api/ProjectsApi.md#getprojectactivities) | **GET** /projects/{id}/activities | Returns project activities
*ProjectsApi* | [**getProjectGroups**](Api/ProjectsApi.md#getprojectgroups) | **GET** /projects/{id}/groups | Returns project groups
*ProjectsApi* | [**getProjectPlan**](Api/ProjectsApi.md#getprojectplan) | **GET** /projects/{id}/plan | Returns project plan
*ProjectsApi* | [**getProjectTasks**](Api/ProjectsApi.md#getprojecttasks) | **GET** /projects/{id}/tasks | Returns project tasks
*ProjectsApi* | [**getProjects**](Api/ProjectsApi.md#getprojects) | **GET** /projects | Get all projects
*ProjectsApi* | [**getProjectsBoards**](Api/ProjectsApi.md#getprojectsboards) | **GET** /projects/boards | Get all project boards
*ProjectsApi* | [**getProjectsPhases**](Api/ProjectsApi.md#getprojectsphases) | **GET** /projects/phases | Get project phases
*ProjectsApi* | [**putProjectPlanActivity**](Api/ProjectsApi.md#putprojectplanactivity) | **PUT** /projects/{id}/plan/activities/{activityId} | Update activity in project plan
*ProjectsApi* | [**putProjectPlanTask**](Api/ProjectsApi.md#putprojectplantask) | **PUT** /projects/{id}/plan/tasks/{taskId} | Update task in project plan
*ProjectsApi* | [**updateProject**](Api/ProjectsApi.md#updateproject) | **PUT** /projects/{id} | Update a project
*RecentsApi* | [**getRecents**](Api/RecentsApi.md#getrecents) | **GET** /recents | Get recents
*RolesApi* | [**addOrUpdateRoleSetting**](Api/RolesApi.md#addorupdaterolesetting) | **POST** /roles/{id}/settings | Add or update role setting
*RolesApi* | [**addRole**](Api/RolesApi.md#addrole) | **POST** /roles | Add a role
*RolesApi* | [**addRoleAssignment**](Api/RolesApi.md#addroleassignment) | **POST** /roles/{id}/assignments | Add role assignment
*RolesApi* | [**deleteRole**](Api/RolesApi.md#deleterole) | **DELETE** /roles/{id} | Delete a role
*RolesApi* | [**deleteRoleAssignment**](Api/RolesApi.md#deleteroleassignment) | **DELETE** /roles/{id}/assignments | Delete a role assignment
*RolesApi* | [**getRole**](Api/RolesApi.md#getrole) | **GET** /roles/{id} | Get one role
*RolesApi* | [**getRoleAssignments**](Api/RolesApi.md#getroleassignments) | **GET** /roles/{id}/assignments | List role assignments
*RolesApi* | [**getRolePipelines**](Api/RolesApi.md#getrolepipelines) | **GET** /roles/{id}/pipelines | List pipeline visibility for a role
*RolesApi* | [**getRoleSettings**](Api/RolesApi.md#getrolesettings) | **GET** /roles/{id}/settings | List role settings
*RolesApi* | [**getRoles**](Api/RolesApi.md#getroles) | **GET** /roles | Get all roles
*RolesApi* | [**updateRole**](Api/RolesApi.md#updaterole) | **PUT** /roles/{id} | Update role details
*RolesApi* | [**updateRolePipelines**](Api/RolesApi.md#updaterolepipelines) | **PUT** /roles/{id}/pipelines | Update pipeline visibility for a role
*StagesApi* | [**addStage**](Api/StagesApi.md#addstage) | **POST** /stages | Add a new stage
*StagesApi* | [**deleteStage**](Api/StagesApi.md#deletestage) | **DELETE** /stages/{id} | Delete a stage
*StagesApi* | [**getStage**](Api/StagesApi.md#getstage) | **GET** /stages/{id} | Get one stage
*StagesApi* | [**getStageDeals**](Api/StagesApi.md#getstagedeals) | **GET** /stages/{id}/deals | Get deals in a stage
*StagesApi* | [**getStages**](Api/StagesApi.md#getstages) | **GET** /stages | Get all stages
*StagesApi* | [**updateStage**](Api/StagesApi.md#updatestage) | **PUT** /stages/{id} | Update stage details
*TasksApi* | [**addTask**](Api/TasksApi.md#addtask) | **POST** /tasks | Add a task
*TasksApi* | [**deleteTask**](Api/TasksApi.md#deletetask) | **DELETE** /tasks/{id} | Delete a task
*TasksApi* | [**getTask**](Api/TasksApi.md#gettask) | **GET** /tasks/{id} | Get details of a task
*TasksApi* | [**getTasks**](Api/TasksApi.md#gettasks) | **GET** /tasks | Get all tasks
*TasksApi* | [**updateTask**](Api/TasksApi.md#updatetask) | **PUT** /tasks/{id} | Update a task
*UserConnectionsApi* | [**getUserConnections**](Api/UserConnectionsApi.md#getuserconnections) | **GET** /userConnections | Get all user connections
*UserSettingsApi* | [**getUserSettings**](Api/UserSettingsApi.md#getusersettings) | **GET** /userSettings | List settings of an authorized user
*UsersApi* | [**addUser**](Api/UsersApi.md#adduser) | **POST** /users | Add a new user
*UsersApi* | [**findUsersByName**](Api/UsersApi.md#findusersbyname) | **GET** /users/find | Find users by name
*UsersApi* | [**getCurrentUser**](Api/UsersApi.md#getcurrentuser) | **GET** /users/me | Get current user data
*UsersApi* | [**getUser**](Api/UsersApi.md#getuser) | **GET** /users/{id} | Get one user
*UsersApi* | [**getUserFollowers**](Api/UsersApi.md#getuserfollowers) | **GET** /users/{id}/followers | List followers of a user
*UsersApi* | [**getUserPermissions**](Api/UsersApi.md#getuserpermissions) | **GET** /users/{id}/permissions | List user permissions
*UsersApi* | [**getUserRoleAssignments**](Api/UsersApi.md#getuserroleassignments) | **GET** /users/{id}/roleAssignments | List role assignments
*UsersApi* | [**getUserRoleSettings**](Api/UsersApi.md#getuserrolesettings) | **GET** /users/{id}/roleSettings | List user role settings
*UsersApi* | [**getUsers**](Api/UsersApi.md#getusers) | **GET** /users | Get all users
*UsersApi* | [**updateUser**](Api/UsersApi.md#updateuser) | **PUT** /users/{id} | Update user details
*WebhooksApi* | [**addWebhook**](Api/WebhooksApi.md#addwebhook) | **POST** /webhooks | Create a new Webhook
*WebhooksApi* | [**deleteWebhook**](Api/WebhooksApi.md#deletewebhook) | **DELETE** /webhooks/{id} | Delete existing Webhook
*WebhooksApi* | [**getWebhooks**](Api/WebhooksApi.md#getwebhooks) | **GET** /webhooks | Get all Webhooks


## Documentation for models

 - [ActivityCollectionResponseObject](Model/ActivityCollectionResponseObject.md)
 - [ActivityCollectionResponseObjectAllOf](Model/ActivityCollectionResponseObjectAllOf.md)
 - [ActivityDistribution](Model/ActivityDistribution.md)
 - [ActivityDistributionData](Model/ActivityDistributionData.md)
 - [ActivityDistributionDataWithAdditionalData](Model/ActivityDistributionDataWithAdditionalData.md)
 - [ActivityDistributionItem](Model/ActivityDistributionItem.md)
 - [ActivityInfo](Model/ActivityInfo.md)
 - [ActivityObjectFragment](Model/ActivityObjectFragment.md)
 - [ActivityPostObject](Model/ActivityPostObject.md)
 - [ActivityPostObjectAllOf](Model/ActivityPostObjectAllOf.md)
 - [ActivityPutObject](Model/ActivityPutObject.md)
 - [ActivityPutObjectAllOf](Model/ActivityPutObjectAllOf.md)
 - [ActivityRecordAdditionalData](Model/ActivityRecordAdditionalData.md)
 - [ActivityResponseObject](Model/ActivityResponseObject.md)
 - [ActivityResponseObjectAllOf](Model/ActivityResponseObjectAllOf.md)
 - [ActivityTypeCount](Model/ActivityTypeCount.md)
 - [ActivityTypeCreateRequest](Model/ActivityTypeCreateRequest.md)
 - [ActivityTypeCreateUpdateDeleteResponse](Model/ActivityTypeCreateUpdateDeleteResponse.md)
 - [ActivityTypeCreateUpdateDeleteResponseAllOf](Model/ActivityTypeCreateUpdateDeleteResponseAllOf.md)
 - [ActivityTypeListResponse](Model/ActivityTypeListResponse.md)
 - [ActivityTypeListResponseAllOf](Model/ActivityTypeListResponseAllOf.md)
 - [ActivityTypeObjectResponse](Model/ActivityTypeObjectResponse.md)
 - [ActivityTypeUpdateRequest](Model/ActivityTypeUpdateRequest.md)
 - [AddActivityResponse](Model/AddActivityResponse.md)
 - [AddActivityResponseRelatedObjects](Model/AddActivityResponseRelatedObjects.md)
 - [AddChannelBadRequestResponse](Model/AddChannelBadRequestResponse.md)
 - [AddChannelBadRequestResponseAdditionalData](Model/AddChannelBadRequestResponseAdditionalData.md)
 - [AddChannelForbiddenErrorResponse](Model/AddChannelForbiddenErrorResponse.md)
 - [AddChannelForbiddenErrorResponseAdditionalData](Model/AddChannelForbiddenErrorResponseAdditionalData.md)
 - [AddDealFollowerRequest](Model/AddDealFollowerRequest.md)
 - [AddDealParticipantRequest](Model/AddDealParticipantRequest.md)
 - [AddFile](Model/AddFile.md)
 - [AddFilterRequest](Model/AddFilterRequest.md)
 - [AddFollowerToPersonResponse](Model/AddFollowerToPersonResponse.md)
 - [AddFollowerToPersonResponseAllOf](Model/AddFollowerToPersonResponseAllOf.md)
 - [AddFollowerToPersonResponseAllOfData](Model/AddFollowerToPersonResponseAllOfData.md)
 - [AddLeadLabelRequest](Model/AddLeadLabelRequest.md)
 - [AddLeadRequest](Model/AddLeadRequest.md)
 - [AddNewPipeline](Model/AddNewPipeline.md)
 - [AddNewPipelineAllOf](Model/AddNewPipelineAllOf.md)
 - [AddNoteRequest](Model/AddNoteRequest.md)
 - [AddNoteRequestAllOf](Model/AddNoteRequestAllOf.md)
 - [AddOrUpdateRoleSettingRequest](Model/AddOrUpdateRoleSettingRequest.md)
 - [AddOrganizationFollowerRequest](Model/AddOrganizationFollowerRequest.md)
 - [AddOrganizationRelationshipRequest](Model/AddOrganizationRelationshipRequest.md)
 - [AddPersonFollowerRequest](Model/AddPersonFollowerRequest.md)
 - [AddPersonPictureResponse](Model/AddPersonPictureResponse.md)
 - [AddPersonPictureResponseAllOf](Model/AddPersonPictureResponseAllOf.md)
 - [AddPersonResponse](Model/AddPersonResponse.md)
 - [AddPersonResponseAllOf](Model/AddPersonResponseAllOf.md)
 - [AddProductAttachmentDetails](Model/AddProductAttachmentDetails.md)
 - [AddProductAttachmentDetailsAllOf](Model/AddProductAttachmentDetailsAllOf.md)
 - [AddProductFollowerRequest](Model/AddProductFollowerRequest.md)
 - [AddProductRequestBody](Model/AddProductRequestBody.md)
 - [AddProductRequestBodyAllOf](Model/AddProductRequestBodyAllOf.md)
 - [AddProductRequestBodyAllOf1](Model/AddProductRequestBodyAllOf1.md)
 - [AddProjectResponse](Model/AddProjectResponse.md)
 - [AddRole](Model/AddRole.md)
 - [AddRoleAssignmentRequest](Model/AddRoleAssignmentRequest.md)
 - [AddRoleAssignmentResponseData](Model/AddRoleAssignmentResponseData.md)
 - [AddRoleAssignmentResponseDataData](Model/AddRoleAssignmentResponseDataData.md)
 - [AddRolesResponseData](Model/AddRolesResponseData.md)
 - [AddTaskResponse](Model/AddTaskResponse.md)
 - [AddTeamUserRequest](Model/AddTeamUserRequest.md)
 - [AddUserRequest](Model/AddUserRequest.md)
 - [AddWebhookRequest](Model/AddWebhookRequest.md)
 - [AddedDealFollower](Model/AddedDealFollower.md)
 - [AddedDealFollowerData](Model/AddedDealFollowerData.md)
 - [AdditionalBaseOrganizationItemInfo](Model/AdditionalBaseOrganizationItemInfo.md)
 - [AdditionalData](Model/AdditionalData.md)
 - [AdditionalDataWithCursorPagination](Model/AdditionalDataWithCursorPagination.md)
 - [AdditionalDataWithOffsetPagination](Model/AdditionalDataWithOffsetPagination.md)
 - [AdditionalDataWithPaginationDetails](Model/AdditionalDataWithPaginationDetails.md)
 - [AdditionalMergePersonInfo](Model/AdditionalMergePersonInfo.md)
 - [AdditionalPersonInfo](Model/AdditionalPersonInfo.md)
 - [AllOrganizationRelationshipsGetResponse](Model/AllOrganizationRelationshipsGetResponse.md)
 - [AllOrganizationRelationshipsGetResponseAllOf](Model/AllOrganizationRelationshipsGetResponseAllOf.md)
 - [AllOrganizationRelationshipsGetResponseAllOfRelatedObjects](Model/AllOrganizationRelationshipsGetResponseAllOfRelatedObjects.md)
 - [AllOrganizationsGetResponse](Model/AllOrganizationsGetResponse.md)
 - [AllOrganizationsGetResponseAllOf](Model/AllOrganizationsGetResponseAllOf.md)
 - [AllOrganizationsGetResponseAllOfRelatedObjects](Model/AllOrganizationsGetResponseAllOfRelatedObjects.md)
 - [ArrayPrices](Model/ArrayPrices.md)
 - [Assignee](Model/Assignee.md)
 - [BaseComment](Model/BaseComment.md)
 - [BaseCurrency](Model/BaseCurrency.md)
 - [BaseDeal](Model/BaseDeal.md)
 - [BaseFollowerItem](Model/BaseFollowerItem.md)
 - [BaseMailThread](Model/BaseMailThread.md)
 - [BaseMailThreadAllOf](Model/BaseMailThreadAllOf.md)
 - [BaseMailThreadAllOfParties](Model/BaseMailThreadAllOfParties.md)
 - [BaseMailThreadMessages](Model/BaseMailThreadMessages.md)
 - [BaseMailThreadMessagesAllOf](Model/BaseMailThreadMessagesAllOf.md)
 - [BaseNote](Model/BaseNote.md)
 - [BaseNoteDealTitle](Model/BaseNoteDealTitle.md)
 - [BaseNoteOrganization](Model/BaseNoteOrganization.md)
 - [BaseNotePerson](Model/BaseNotePerson.md)
 - [BaseNoteProject](Model/BaseNoteProject.md)
 - [BaseOrganizationItem](Model/BaseOrganizationItem.md)
 - [BaseOrganizationItemFields](Model/BaseOrganizationItemFields.md)
 - [BaseOrganizationItemWithEditNameFlag](Model/BaseOrganizationItemWithEditNameFlag.md)
 - [BaseOrganizationItemWithEditNameFlagAllOf](Model/BaseOrganizationItemWithEditNameFlagAllOf.md)
 - [BaseOrganizationRelationshipItem](Model/BaseOrganizationRelationshipItem.md)
 - [BasePersonItem](Model/BasePersonItem.md)
 - [BasePersonItemEmail](Model/BasePersonItemEmail.md)
 - [BasePersonItemPhone](Model/BasePersonItemPhone.md)
 - [BasePipeline](Model/BasePipeline.md)
 - [BasePipelineWithSelectedFlag](Model/BasePipelineWithSelectedFlag.md)
 - [BasePipelineWithSelectedFlagAllOf](Model/BasePipelineWithSelectedFlagAllOf.md)
 - [BaseProduct](Model/BaseProduct.md)
 - [BaseResponse](Model/BaseResponse.md)
 - [BaseResponseWithStatus](Model/BaseResponseWithStatus.md)
 - [BaseResponseWithStatusAllOf](Model/BaseResponseWithStatusAllOf.md)
 - [BaseRole](Model/BaseRole.md)
 - [BaseStage](Model/BaseStage.md)
 - [BaseTeam](Model/BaseTeam.md)
 - [BaseTeamAdditionalProperties](Model/BaseTeamAdditionalProperties.md)
 - [BaseUser](Model/BaseUser.md)
 - [BaseUserMe](Model/BaseUserMe.md)
 - [BaseUserMeAllOf](Model/BaseUserMeAllOf.md)
 - [BaseUserMeAllOfLanguage](Model/BaseUserMeAllOfLanguage.md)
 - [BaseWebhook](Model/BaseWebhook.md)
 - [BasicDeal](Model/BasicDeal.md)
 - [BasicDealProduct](Model/BasicDealProduct.md)
 - [BasicDealProductAllOf](Model/BasicDealProductAllOf.md)
 - [BasicGoal](Model/BasicGoal.md)
 - [BasicOrganization](Model/BasicOrganization.md)
 - [BasicPerson](Model/BasicPerson.md)
 - [BasicPersonEmail](Model/BasicPersonEmail.md)
 - [BillingFrequency](Model/BillingFrequency.md)
 - [BillingFrequency1](Model/BillingFrequency1.md)
 - [BulkDeleteResponse](Model/BulkDeleteResponse.md)
 - [BulkDeleteResponseAllOf](Model/BulkDeleteResponseAllOf.md)
 - [BulkDeleteResponseAllOfData](Model/BulkDeleteResponseAllOfData.md)
 - [CalculatedFields](Model/CalculatedFields.md)
 - [CallLogBadRequestResponse](Model/CallLogBadRequestResponse.md)
 - [CallLogConflictResponse](Model/CallLogConflictResponse.md)
 - [CallLogForbiddenResponse](Model/CallLogForbiddenResponse.md)
 - [CallLogGoneResponse](Model/CallLogGoneResponse.md)
 - [CallLogInternalErrorResponse](Model/CallLogInternalErrorResponse.md)
 - [CallLogNotFoundResponse](Model/CallLogNotFoundResponse.md)
 - [CallLogObject](Model/CallLogObject.md)
 - [CallLogResponse200](Model/CallLogResponse200.md)
 - [CallLogsResponse](Model/CallLogsResponse.md)
 - [CallLogsResponseAdditionalData](Model/CallLogsResponseAdditionalData.md)
 - [ChangelogResponse](Model/ChangelogResponse.md)
 - [ChangelogResponseAllOf](Model/ChangelogResponseAllOf.md)
 - [ChangelogResponseAllOfData](Model/ChangelogResponseAllOfData.md)
 - [ChannelObject](Model/ChannelObject.md)
 - [ChannelObjectResponse](Model/ChannelObjectResponse.md)
 - [ChannelObjectResponseData](Model/ChannelObjectResponseData.md)
 - [CommentPostPutObject](Model/CommentPostPutObject.md)
 - [CommonMailThread](Model/CommonMailThread.md)
 - [CreateRemoteFileAndLinkItToItem](Model/CreateRemoteFileAndLinkItToItem.md)
 - [CreateTeam](Model/CreateTeam.md)
 - [Currencies](Model/Currencies.md)
 - [DealCollectionResponseObject](Model/DealCollectionResponseObject.md)
 - [DealCountAndActivityInfo](Model/DealCountAndActivityInfo.md)
 - [DealFlowResponse](Model/DealFlowResponse.md)
 - [DealFlowResponseAllOf](Model/DealFlowResponseAllOf.md)
 - [DealFlowResponseAllOfData](Model/DealFlowResponseAllOfData.md)
 - [DealFlowResponseAllOfRelatedObjects](Model/DealFlowResponseAllOfRelatedObjects.md)
 - [DealListActivitiesResponse](Model/DealListActivitiesResponse.md)
 - [DealListActivitiesResponseAllOf](Model/DealListActivitiesResponseAllOf.md)
 - [DealListActivitiesResponseAllOfRelatedObjects](Model/DealListActivitiesResponseAllOfRelatedObjects.md)
 - [DealNonStrict](Model/DealNonStrict.md)
 - [DealNonStrictModeFields](Model/DealNonStrictModeFields.md)
 - [DealNonStrictModeFieldsCreatorUserId](Model/DealNonStrictModeFieldsCreatorUserId.md)
 - [DealNonStrictWithDetails](Model/DealNonStrictWithDetails.md)
 - [DealNonStrictWithDetailsAllOf](Model/DealNonStrictWithDetailsAllOf.md)
 - [DealNonStrictWithDetailsAllOfAge](Model/DealNonStrictWithDetailsAllOfAge.md)
 - [DealNonStrictWithDetailsAllOfAverageTimeToWon](Model/DealNonStrictWithDetailsAllOfAverageTimeToWon.md)
 - [DealNonStrictWithDetailsAllOfStayInPipelineStages](Model/DealNonStrictWithDetailsAllOfStayInPipelineStages.md)
 - [DealOrganizationData](Model/DealOrganizationData.md)
 - [DealOrganizationDataWithId](Model/DealOrganizationDataWithId.md)
 - [DealOrganizationDataWithIdAllOf](Model/DealOrganizationDataWithIdAllOf.md)
 - [DealParticipantCountInfo](Model/DealParticipantCountInfo.md)
 - [DealParticipants](Model/DealParticipants.md)
 - [DealParticipantsChangelog](Model/DealParticipantsChangelog.md)
 - [DealPersonData](Model/DealPersonData.md)
 - [DealPersonDataEmail](Model/DealPersonDataEmail.md)
 - [DealPersonDataPhone](Model/DealPersonDataPhone.md)
 - [DealPersonDataWithId](Model/DealPersonDataWithId.md)
 - [DealPersonDataWithIdAllOf](Model/DealPersonDataWithIdAllOf.md)
 - [DealProductRequestBody](Model/DealProductRequestBody.md)
 - [DealSearchItem](Model/DealSearchItem.md)
 - [DealSearchItemItem](Model/DealSearchItemItem.md)
 - [DealSearchItemItemOrganization](Model/DealSearchItemItemOrganization.md)
 - [DealSearchItemItemOwner](Model/DealSearchItemItemOwner.md)
 - [DealSearchItemItemPerson](Model/DealSearchItemItemPerson.md)
 - [DealSearchItemItemStage](Model/DealSearchItemItemStage.md)
 - [DealSearchResponse](Model/DealSearchResponse.md)
 - [DealSearchResponseAllOf](Model/DealSearchResponseAllOf.md)
 - [DealSearchResponseAllOfData](Model/DealSearchResponseAllOfData.md)
 - [DealStrict](Model/DealStrict.md)
 - [DealStrictModeFields](Model/DealStrictModeFields.md)
 - [DealStrictWithMergeId](Model/DealStrictWithMergeId.md)
 - [DealStrictWithMergeIdAllOf](Model/DealStrictWithMergeIdAllOf.md)
 - [DealTitleParameter](Model/DealTitleParameter.md)
 - [DealUserData](Model/DealUserData.md)
 - [DealUserDataWithId](Model/DealUserDataWithId.md)
 - [DealUserDataWithIdAllOf](Model/DealUserDataWithIdAllOf.md)
 - [DealsCountAndActivityInfo](Model/DealsCountAndActivityInfo.md)
 - [DealsCountInfo](Model/DealsCountInfo.md)
 - [DealsMovementsInfo](Model/DealsMovementsInfo.md)
 - [DealsMovementsInfoFormattedValues](Model/DealsMovementsInfoFormattedValues.md)
 - [DealsMovementsInfoValues](Model/DealsMovementsInfoValues.md)
 - [DeleteActivityResponse](Model/DeleteActivityResponse.md)
 - [DeleteActivityResponseData](Model/DeleteActivityResponseData.md)
 - [DeleteChannelSuccess](Model/DeleteChannelSuccess.md)
 - [DeleteComment](Model/DeleteComment.md)
 - [DeleteConversationForbiddenErrorResponse](Model/DeleteConversationForbiddenErrorResponse.md)
 - [DeleteConversationForbiddenErrorResponseAdditionalData](Model/DeleteConversationForbiddenErrorResponseAdditionalData.md)
 - [DeleteConversationNotFoundErrorResponse](Model/DeleteConversationNotFoundErrorResponse.md)
 - [DeleteConversationNotFoundErrorResponseAdditionalData](Model/DeleteConversationNotFoundErrorResponseAdditionalData.md)
 - [DeleteConversationSuccess](Model/DeleteConversationSuccess.md)
 - [DeleteDeal](Model/DeleteDeal.md)
 - [DeleteDealData](Model/DeleteDealData.md)
 - [DeleteDealFollower](Model/DeleteDealFollower.md)
 - [DeleteDealFollowerData](Model/DeleteDealFollowerData.md)
 - [DeleteDealParticipant](Model/DeleteDealParticipant.md)
 - [DeleteDealParticipantData](Model/DeleteDealParticipantData.md)
 - [DeleteDealProduct](Model/DeleteDealProduct.md)
 - [DeleteDealProductData](Model/DeleteDealProductData.md)
 - [DeleteFile](Model/DeleteFile.md)
 - [DeleteFileData](Model/DeleteFileData.md)
 - [DeleteGoalResponse](Model/DeleteGoalResponse.md)
 - [DeleteLeadIdResponse](Model/DeleteLeadIdResponse.md)
 - [DeleteMultipleProductFieldsResponse](Model/DeleteMultipleProductFieldsResponse.md)
 - [DeleteMultipleProductFieldsResponseData](Model/DeleteMultipleProductFieldsResponseData.md)
 - [DeleteNote](Model/DeleteNote.md)
 - [DeletePersonResponse](Model/DeletePersonResponse.md)
 - [DeletePersonResponseAllOf](Model/DeletePersonResponseAllOf.md)
 - [DeletePersonResponseAllOfData](Model/DeletePersonResponseAllOfData.md)
 - [DeletePipelineResponse](Model/DeletePipelineResponse.md)
 - [DeletePipelineResponseData](Model/DeletePipelineResponseData.md)
 - [DeleteProductFieldResponse](Model/DeleteProductFieldResponse.md)
 - [DeleteProductFieldResponseData](Model/DeleteProductFieldResponseData.md)
 - [DeleteProductFollowerResponse](Model/DeleteProductFollowerResponse.md)
 - [DeleteProductFollowerResponseData](Model/DeleteProductFollowerResponseData.md)
 - [DeleteProductResponse](Model/DeleteProductResponse.md)
 - [DeleteProductResponseData](Model/DeleteProductResponseData.md)
 - [DeleteProject](Model/DeleteProject.md)
 - [DeleteProjectData](Model/DeleteProjectData.md)
 - [DeleteProjectResponse](Model/DeleteProjectResponse.md)
 - [DeleteResponse](Model/DeleteResponse.md)
 - [DeleteResponseAllOf](Model/DeleteResponseAllOf.md)
 - [DeleteResponseAllOfData](Model/DeleteResponseAllOfData.md)
 - [DeleteRole](Model/DeleteRole.md)
 - [DeleteRoleAssignment](Model/DeleteRoleAssignment.md)
 - [DeleteRoleAssignmentRequest](Model/DeleteRoleAssignmentRequest.md)
 - [DeleteRoleAssignmentResponseData](Model/DeleteRoleAssignmentResponseData.md)
 - [DeleteRoleAssignmentResponseDataData](Model/DeleteRoleAssignmentResponseDataData.md)
 - [DeleteRoleResponseData](Model/DeleteRoleResponseData.md)
 - [DeleteRoleResponseDataData](Model/DeleteRoleResponseDataData.md)
 - [DeleteStageResponse](Model/DeleteStageResponse.md)
 - [DeleteStageResponseData](Model/DeleteStageResponseData.md)
 - [DeleteTask](Model/DeleteTask.md)
 - [DeleteTaskData](Model/DeleteTaskData.md)
 - [DeleteTaskResponse](Model/DeleteTaskResponse.md)
 - [DeleteTeamUserRequest](Model/DeleteTeamUserRequest.md)
 - [Duration](Model/Duration.md)
 - [EditPipeline](Model/EditPipeline.md)
 - [EditPipelineAllOf](Model/EditPipelineAllOf.md)
 - [EmailInfo](Model/EmailInfo.md)
 - [ExpectedOutcome](Model/ExpectedOutcome.md)
 - [FailResponse](Model/FailResponse.md)
 - [Field](Model/Field.md)
 - [FieldCreateRequest](Model/FieldCreateRequest.md)
 - [FieldCreateRequestAllOf](Model/FieldCreateRequestAllOf.md)
 - [FieldResponse](Model/FieldResponse.md)
 - [FieldResponseAllOf](Model/FieldResponseAllOf.md)
 - [FieldType](Model/FieldType.md)
 - [FieldTypeAsString](Model/FieldTypeAsString.md)
 - [FieldUpdateRequest](Model/FieldUpdateRequest.md)
 - [FieldsResponse](Model/FieldsResponse.md)
 - [FieldsResponseAllOf](Model/FieldsResponseAllOf.md)
 - [FileData](Model/FileData.md)
 - [FileItem](Model/FileItem.md)
 - [Filter](Model/Filter.md)
 - [FilterGetItem](Model/FilterGetItem.md)
 - [FilterType](Model/FilterType.md)
 - [FiltersBulkDeleteResponse](Model/FiltersBulkDeleteResponse.md)
 - [FiltersBulkDeleteResponseAllOf](Model/FiltersBulkDeleteResponseAllOf.md)
 - [FiltersBulkDeleteResponseAllOfData](Model/FiltersBulkDeleteResponseAllOfData.md)
 - [FiltersBulkGetResponse](Model/FiltersBulkGetResponse.md)
 - [FiltersBulkGetResponseAllOf](Model/FiltersBulkGetResponseAllOf.md)
 - [FiltersDeleteResponse](Model/FiltersDeleteResponse.md)
 - [FiltersDeleteResponseAllOf](Model/FiltersDeleteResponseAllOf.md)
 - [FiltersDeleteResponseAllOfData](Model/FiltersDeleteResponseAllOfData.md)
 - [FiltersGetResponse](Model/FiltersGetResponse.md)
 - [FiltersGetResponseAllOf](Model/FiltersGetResponseAllOf.md)
 - [FiltersPostResponse](Model/FiltersPostResponse.md)
 - [FiltersPostResponseAllOf](Model/FiltersPostResponseAllOf.md)
 - [FindGoalResponse](Model/FindGoalResponse.md)
 - [FollowerData](Model/FollowerData.md)
 - [FollowerDataWithID](Model/FollowerDataWithID.md)
 - [FollowerDataWithIDAllOf](Model/FollowerDataWithIDAllOf.md)
 - [FullProjectObject](Model/FullProjectObject.md)
 - [FullRole](Model/FullRole.md)
 - [FullRoleAllOf](Model/FullRoleAllOf.md)
 - [FullTaskObject](Model/FullTaskObject.md)
 - [GetActivitiesCollectionResponse](Model/GetActivitiesCollectionResponse.md)
 - [GetActivitiesResponse](Model/GetActivitiesResponse.md)
 - [GetActivitiesResponseRelatedObjects](Model/GetActivitiesResponseRelatedObjects.md)
 - [GetActivityResponse](Model/GetActivityResponse.md)
 - [GetAddProductAttachmentDetails](Model/GetAddProductAttachmentDetails.md)
 - [GetAddUpdateStage](Model/GetAddUpdateStage.md)
 - [GetAddedDeal](Model/GetAddedDeal.md)
 - [GetAllFiles](Model/GetAllFiles.md)
 - [GetAllPersonsResponse](Model/GetAllPersonsResponse.md)
 - [GetAllPersonsResponseAllOf](Model/GetAllPersonsResponseAllOf.md)
 - [GetAllPipelines](Model/GetAllPipelines.md)
 - [GetAllPipelinesAllOf](Model/GetAllPipelinesAllOf.md)
 - [GetAllProductFieldsResponse](Model/GetAllProductFieldsResponse.md)
 - [GetComments](Model/GetComments.md)
 - [GetDeal](Model/GetDeal.md)
 - [GetDealAdditionalData](Model/GetDealAdditionalData.md)
 - [GetDealRelatedObjects](Model/GetDealRelatedObjects.md)
 - [GetDeals](Model/GetDeals.md)
 - [GetDealsCollection](Model/GetDealsCollection.md)
 - [GetDealsConversionRatesInPipeline](Model/GetDealsConversionRatesInPipeline.md)
 - [GetDealsConversionRatesInPipelineAllOf](Model/GetDealsConversionRatesInPipelineAllOf.md)
 - [GetDealsConversionRatesInPipelineAllOfData](Model/GetDealsConversionRatesInPipelineAllOfData.md)
 - [GetDealsMovementsInPipeline](Model/GetDealsMovementsInPipeline.md)
 - [GetDealsMovementsInPipelineAllOf](Model/GetDealsMovementsInPipelineAllOf.md)
 - [GetDealsMovementsInPipelineAllOfData](Model/GetDealsMovementsInPipelineAllOfData.md)
 - [GetDealsMovementsInPipelineAllOfDataAverageAgeInDays](Model/GetDealsMovementsInPipelineAllOfDataAverageAgeInDays.md)
 - [GetDealsMovementsInPipelineAllOfDataAverageAgeInDaysByStages](Model/GetDealsMovementsInPipelineAllOfDataAverageAgeInDaysByStages.md)
 - [GetDealsMovementsInPipelineAllOfDataMovementsBetweenStages](Model/GetDealsMovementsInPipelineAllOfDataMovementsBetweenStages.md)
 - [GetDealsRelatedObjects](Model/GetDealsRelatedObjects.md)
 - [GetDealsSummary](Model/GetDealsSummary.md)
 - [GetDealsSummaryData](Model/GetDealsSummaryData.md)
 - [GetDealsSummaryDataValuesTotal](Model/GetDealsSummaryDataValuesTotal.md)
 - [GetDealsSummaryDataWeightedValuesTotal](Model/GetDealsSummaryDataWeightedValuesTotal.md)
 - [GetDealsTimeline](Model/GetDealsTimeline.md)
 - [GetDealsTimelineData](Model/GetDealsTimelineData.md)
 - [GetDealsTimelineDataTotals](Model/GetDealsTimelineDataTotals.md)
 - [GetDuplicatedDeal](Model/GetDuplicatedDeal.md)
 - [GetField](Model/GetField.md)
 - [GetFieldAllOf](Model/GetFieldAllOf.md)
 - [GetGoalResultResponse](Model/GetGoalResultResponse.md)
 - [GetGoalsResponse](Model/GetGoalsResponse.md)
 - [GetLeadIdResponse](Model/GetLeadIdResponse.md)
 - [GetLeadIdResponseData](Model/GetLeadIdResponseData.md)
 - [GetLeadLabelsResponse](Model/GetLeadLabelsResponse.md)
 - [GetLeadResponse](Model/GetLeadResponse.md)
 - [GetLeadsResponse](Model/GetLeadsResponse.md)
 - [GetLeadsSourceResponse](Model/GetLeadsSourceResponse.md)
 - [GetMergedDeal](Model/GetMergedDeal.md)
 - [GetNoteField](Model/GetNoteField.md)
 - [GetNotes](Model/GetNotes.md)
 - [GetOneFile](Model/GetOneFile.md)
 - [GetOnePipeline](Model/GetOnePipeline.md)
 - [GetOnePipelineAllOf](Model/GetOnePipelineAllOf.md)
 - [GetOneStage](Model/GetOneStage.md)
 - [GetPersonDetailsResponse](Model/GetPersonDetailsResponse.md)
 - [GetPersonDetailsResponseAllOf](Model/GetPersonDetailsResponseAllOf.md)
 - [GetPersonDetailsResponseAllOfAdditionalData](Model/GetPersonDetailsResponseAllOfAdditionalData.md)
 - [GetProductAttachmentDetails](Model/GetProductAttachmentDetails.md)
 - [GetProductField](Model/GetProductField.md)
 - [GetProductFieldResponse](Model/GetProductFieldResponse.md)
 - [GetProjectBoardResponse](Model/GetProjectBoardResponse.md)
 - [GetProjectBoardsResponse](Model/GetProjectBoardsResponse.md)
 - [GetProjectGroupsResponse](Model/GetProjectGroupsResponse.md)
 - [GetProjectPhaseResponse](Model/GetProjectPhaseResponse.md)
 - [GetProjectPhasesResponse](Model/GetProjectPhasesResponse.md)
 - [GetProjectPlanResponse](Model/GetProjectPlanResponse.md)
 - [GetProjectResponse](Model/GetProjectResponse.md)
 - [GetProjectTemplateResponse](Model/GetProjectTemplateResponse.md)
 - [GetProjectTemplatesResponse](Model/GetProjectTemplatesResponse.md)
 - [GetProjectsResponse](Model/GetProjectsResponse.md)
 - [GetReceiveMessageSuccessResponse](Model/GetReceiveMessageSuccessResponse.md)
 - [GetRecents](Model/GetRecents.md)
 - [GetRecentsAdditionalData](Model/GetRecentsAdditionalData.md)
 - [GetRole](Model/GetRole.md)
 - [GetRoleAssignments](Model/GetRoleAssignments.md)
 - [GetRoleAssignmentsResponseData](Model/GetRoleAssignmentsResponseData.md)
 - [GetRolePipelines](Model/GetRolePipelines.md)
 - [GetRolePipelinesAllOf](Model/GetRolePipelinesAllOf.md)
 - [GetRolePipelinesResponseData](Model/GetRolePipelinesResponseData.md)
 - [GetRoleResponseData](Model/GetRoleResponseData.md)
 - [GetRoleResponseDataAdditionalData](Model/GetRoleResponseDataAdditionalData.md)
 - [GetRoleSettings](Model/GetRoleSettings.md)
 - [GetRoleSettingsResponseData](Model/GetRoleSettingsResponseData.md)
 - [GetRoles](Model/GetRoles.md)
 - [GetRolesAllOf](Model/GetRolesAllOf.md)
 - [GetStageDeals](Model/GetStageDeals.md)
 - [GetStages](Model/GetStages.md)
 - [GetTaskResponse](Model/GetTaskResponse.md)
 - [GetTasksResponse](Model/GetTasksResponse.md)
 - [GetUserConnectionsResponseData](Model/GetUserConnectionsResponseData.md)
 - [GetUserConnectionsResponseDataData](Model/GetUserConnectionsResponseDataData.md)
 - [GetUserResponseData](Model/GetUserResponseData.md)
 - [GetUserSettingsResponseData](Model/GetUserSettingsResponseData.md)
 - [GetWebhookResponseData](Model/GetWebhookResponseData.md)
 - [GoalResults](Model/GoalResults.md)
 - [GoalType](Model/GoalType.md)
 - [GoalsResponseComponent](Model/GoalsResponseComponent.md)
 - [IconKey](Model/IconKey.md)
 - [InlineResponse200](Model/InlineResponse200.md)
 - [InlineResponse2001](Model/InlineResponse2001.md)
 - [InternalFieldType](Model/InternalFieldType.md)
 - [ItemSearchAdditionalData](Model/ItemSearchAdditionalData.md)
 - [ItemSearchAdditionalDataPagination](Model/ItemSearchAdditionalDataPagination.md)
 - [ItemSearchFieldResponse](Model/ItemSearchFieldResponse.md)
 - [ItemSearchFieldResponseAllOf](Model/ItemSearchFieldResponseAllOf.md)
 - [ItemSearchFieldResponseAllOfData](Model/ItemSearchFieldResponseAllOfData.md)
 - [ItemSearchItem](Model/ItemSearchItem.md)
 - [ItemSearchResponse](Model/ItemSearchResponse.md)
 - [ItemSearchResponseAllOf](Model/ItemSearchResponseAllOf.md)
 - [ItemSearchResponseAllOfData](Model/ItemSearchResponseAllOfData.md)
 - [LeadLabelColor](Model/LeadLabelColor.md)
 - [LeadLabelResponse](Model/LeadLabelResponse.md)
 - [LeadNotFoundResponse](Model/LeadNotFoundResponse.md)
 - [LeadResponse](Model/LeadResponse.md)
 - [LeadSearchItem](Model/LeadSearchItem.md)
 - [LeadSearchItemItem](Model/LeadSearchItemItem.md)
 - [LeadSearchItemItemOrganization](Model/LeadSearchItemItemOrganization.md)
 - [LeadSearchItemItemOwner](Model/LeadSearchItemItemOwner.md)
 - [LeadSearchItemItemPerson](Model/LeadSearchItemItemPerson.md)
 - [LeadSearchResponse](Model/LeadSearchResponse.md)
 - [LeadSearchResponseAllOf](Model/LeadSearchResponseAllOf.md)
 - [LeadSearchResponseAllOfData](Model/LeadSearchResponseAllOfData.md)
 - [LeadSource](Model/LeadSource.md)
 - [LeadValue](Model/LeadValue.md)
 - [LinkRemoteFileToItem](Model/LinkRemoteFileToItem.md)
 - [ListActivitiesResponse](Model/ListActivitiesResponse.md)
 - [ListActivitiesResponseAllOf](Model/ListActivitiesResponseAllOf.md)
 - [ListDealsResponse](Model/ListDealsResponse.md)
 - [ListDealsResponseAllOf](Model/ListDealsResponseAllOf.md)
 - [ListDealsResponseAllOfRelatedObjects](Model/ListDealsResponseAllOfRelatedObjects.md)
 - [ListFilesResponse](Model/ListFilesResponse.md)
 - [ListFilesResponseAllOf](Model/ListFilesResponseAllOf.md)
 - [ListFollowersResponse](Model/ListFollowersResponse.md)
 - [ListFollowersResponseAllOf](Model/ListFollowersResponseAllOf.md)
 - [ListFollowersResponseAllOfData](Model/ListFollowersResponseAllOfData.md)
 - [ListMailMessagesResponse](Model/ListMailMessagesResponse.md)
 - [ListMailMessagesResponseAllOf](Model/ListMailMessagesResponseAllOf.md)
 - [ListMailMessagesResponseAllOfData](Model/ListMailMessagesResponseAllOfData.md)
 - [ListPermittedUsersResponse](Model/ListPermittedUsersResponse.md)
 - [ListPermittedUsersResponse1](Model/ListPermittedUsersResponse1.md)
 - [ListPermittedUsersResponse1AllOf](Model/ListPermittedUsersResponse1AllOf.md)
 - [ListPersonProductsResponse](Model/ListPersonProductsResponse.md)
 - [ListPersonProductsResponseAllOf](Model/ListPersonProductsResponseAllOf.md)
 - [ListPersonProductsResponseAllOfDEALID](Model/ListPersonProductsResponseAllOfDEALID.md)
 - [ListPersonProductsResponseAllOfData](Model/ListPersonProductsResponseAllOfData.md)
 - [ListPersonsResponse](Model/ListPersonsResponse.md)
 - [ListPersonsResponseAllOf](Model/ListPersonsResponseAllOf.md)
 - [ListPersonsResponseAllOfRelatedObjects](Model/ListPersonsResponseAllOfRelatedObjects.md)
 - [ListProductAdditionalData](Model/ListProductAdditionalData.md)
 - [ListProductAdditionalDataAllOf](Model/ListProductAdditionalDataAllOf.md)
 - [ListProductFilesResponse](Model/ListProductFilesResponse.md)
 - [ListProductFilesResponseAllOf](Model/ListProductFilesResponseAllOf.md)
 - [ListProductFollowersResponse](Model/ListProductFollowersResponse.md)
 - [ListProductFollowersResponseAllOf](Model/ListProductFollowersResponseAllOf.md)
 - [ListProductFollowersResponseAllOfData](Model/ListProductFollowersResponseAllOfData.md)
 - [ListProductsResponse](Model/ListProductsResponse.md)
 - [ListProductsResponseAllOf](Model/ListProductsResponseAllOf.md)
 - [ListProductsResponseAllOfRelatedObjects](Model/ListProductsResponseAllOfRelatedObjects.md)
 - [MailMessage](Model/MailMessage.md)
 - [MailMessageAllOf](Model/MailMessageAllOf.md)
 - [MailMessageData](Model/MailMessageData.md)
 - [MailMessageItemForList](Model/MailMessageItemForList.md)
 - [MailMessageItemForListAllOf](Model/MailMessageItemForListAllOf.md)
 - [MailParticipant](Model/MailParticipant.md)
 - [MailServiceBaseResponse](Model/MailServiceBaseResponse.md)
 - [MailThread](Model/MailThread.md)
 - [MailThreadAllOf](Model/MailThreadAllOf.md)
 - [MailThreadDelete](Model/MailThreadDelete.md)
 - [MailThreadDeleteAllOf](Model/MailThreadDeleteAllOf.md)
 - [MailThreadDeleteAllOfData](Model/MailThreadDeleteAllOfData.md)
 - [MailThreadMessages](Model/MailThreadMessages.md)
 - [MailThreadMessagesAllOf](Model/MailThreadMessagesAllOf.md)
 - [MailThreadOne](Model/MailThreadOne.md)
 - [MailThreadOneAllOf](Model/MailThreadOneAllOf.md)
 - [MailThreadParticipant](Model/MailThreadParticipant.md)
 - [MailThreadPut](Model/MailThreadPut.md)
 - [MailThreadPutAllOf](Model/MailThreadPutAllOf.md)
 - [MarketingStatus](Model/MarketingStatus.md)
 - [MergeDealsRequest](Model/MergeDealsRequest.md)
 - [MergeOrganizationsRequest](Model/MergeOrganizationsRequest.md)
 - [MergePersonDealRelatedInfo](Model/MergePersonDealRelatedInfo.md)
 - [MergePersonItem](Model/MergePersonItem.md)
 - [MergePersonsRequest](Model/MergePersonsRequest.md)
 - [MergePersonsResponse](Model/MergePersonsResponse.md)
 - [MergePersonsResponseAllOf](Model/MergePersonsResponseAllOf.md)
 - [MessageObject](Model/MessageObject.md)
 - [MessageObjectAttachments](Model/MessageObjectAttachments.md)
 - [NameObject](Model/NameObject.md)
 - [NewDeal](Model/NewDeal.md)
 - [NewDealParameters](Model/NewDealParameters.md)
 - [NewDealProduct](Model/NewDealProduct.md)
 - [NewDealProductAllOf](Model/NewDealProductAllOf.md)
 - [NewDealProductAllOf1](Model/NewDealProductAllOf1.md)
 - [NewDealProductAllOf2](Model/NewDealProductAllOf2.md)
 - [NewFollowerResponse](Model/NewFollowerResponse.md)
 - [NewFollowerResponseData](Model/NewFollowerResponseData.md)
 - [NewGoal](Model/NewGoal.md)
 - [NewOrganization](Model/NewOrganization.md)
 - [NewOrganizationAllOf](Model/NewOrganizationAllOf.md)
 - [NewPerson](Model/NewPerson.md)
 - [NewPersonAllOf](Model/NewPersonAllOf.md)
 - [NewProductField](Model/NewProductField.md)
 - [Note](Model/Note.md)
 - [NoteAllOf](Model/NoteAllOf.md)
 - [NoteConnectToParams](Model/NoteConnectToParams.md)
 - [NoteCreatorUser](Model/NoteCreatorUser.md)
 - [NoteField](Model/NoteField.md)
 - [NoteFieldOptions](Model/NoteFieldOptions.md)
 - [NoteFieldsResponse](Model/NoteFieldsResponse.md)
 - [NoteFieldsResponseAllOf](Model/NoteFieldsResponseAllOf.md)
 - [NoteParams](Model/NoteParams.md)
 - [NumberBoolean](Model/NumberBoolean.md)
 - [NumberBooleanDefault0](Model/NumberBooleanDefault0.md)
 - [NumberBooleanDefault1](Model/NumberBooleanDefault1.md)
 - [OrgAndOwnerId](Model/OrgAndOwnerId.md)
 - [OrganizationAddressInfo](Model/OrganizationAddressInfo.md)
 - [OrganizationCountAndAddressInfo](Model/OrganizationCountAndAddressInfo.md)
 - [OrganizationCountInfo](Model/OrganizationCountInfo.md)
 - [OrganizationData](Model/OrganizationData.md)
 - [OrganizationDataWithId](Model/OrganizationDataWithId.md)
 - [OrganizationDataWithIdAllOf](Model/OrganizationDataWithIdAllOf.md)
 - [OrganizationDataWithIdAndActiveFlag](Model/OrganizationDataWithIdAndActiveFlag.md)
 - [OrganizationDataWithIdAndActiveFlagAllOf](Model/OrganizationDataWithIdAndActiveFlagAllOf.md)
 - [OrganizationDeleteResponse](Model/OrganizationDeleteResponse.md)
 - [OrganizationDeleteResponseData](Model/OrganizationDeleteResponseData.md)
 - [OrganizationDetailsGetResponse](Model/OrganizationDetailsGetResponse.md)
 - [OrganizationDetailsGetResponseAllOf](Model/OrganizationDetailsGetResponseAllOf.md)
 - [OrganizationDetailsGetResponseAllOfAdditionalData](Model/OrganizationDetailsGetResponseAllOfAdditionalData.md)
 - [OrganizationFlowResponse](Model/OrganizationFlowResponse.md)
 - [OrganizationFlowResponseAllOf](Model/OrganizationFlowResponseAllOf.md)
 - [OrganizationFlowResponseAllOfData](Model/OrganizationFlowResponseAllOfData.md)
 - [OrganizationFlowResponseAllOfRelatedObjects](Model/OrganizationFlowResponseAllOfRelatedObjects.md)
 - [OrganizationFollowerDeleteResponse](Model/OrganizationFollowerDeleteResponse.md)
 - [OrganizationFollowerDeleteResponseData](Model/OrganizationFollowerDeleteResponseData.md)
 - [OrganizationFollowerItem](Model/OrganizationFollowerItem.md)
 - [OrganizationFollowerItemAllOf](Model/OrganizationFollowerItemAllOf.md)
 - [OrganizationFollowerPostResponse](Model/OrganizationFollowerPostResponse.md)
 - [OrganizationFollowersListResponse](Model/OrganizationFollowersListResponse.md)
 - [OrganizationItem](Model/OrganizationItem.md)
 - [OrganizationItemAllOf](Model/OrganizationItemAllOf.md)
 - [OrganizationPostResponse](Model/OrganizationPostResponse.md)
 - [OrganizationPostResponseAllOf](Model/OrganizationPostResponseAllOf.md)
 - [OrganizationRelationship](Model/OrganizationRelationship.md)
 - [OrganizationRelationshipDeleteResponse](Model/OrganizationRelationshipDeleteResponse.md)
 - [OrganizationRelationshipDeleteResponseAllOf](Model/OrganizationRelationshipDeleteResponseAllOf.md)
 - [OrganizationRelationshipDeleteResponseAllOfData](Model/OrganizationRelationshipDeleteResponseAllOfData.md)
 - [OrganizationRelationshipDetails](Model/OrganizationRelationshipDetails.md)
 - [OrganizationRelationshipGetResponse](Model/OrganizationRelationshipGetResponse.md)
 - [OrganizationRelationshipGetResponseAllOf](Model/OrganizationRelationshipGetResponseAllOf.md)
 - [OrganizationRelationshipPostResponse](Model/OrganizationRelationshipPostResponse.md)
 - [OrganizationRelationshipPostResponseAllOf](Model/OrganizationRelationshipPostResponseAllOf.md)
 - [OrganizationRelationshipUpdateResponse](Model/OrganizationRelationshipUpdateResponse.md)
 - [OrganizationRelationshipWithCalculatedFields](Model/OrganizationRelationshipWithCalculatedFields.md)
 - [OrganizationSearchItem](Model/OrganizationSearchItem.md)
 - [OrganizationSearchItemItem](Model/OrganizationSearchItemItem.md)
 - [OrganizationSearchResponse](Model/OrganizationSearchResponse.md)
 - [OrganizationSearchResponseAllOf](Model/OrganizationSearchResponseAllOf.md)
 - [OrganizationSearchResponseAllOfData](Model/OrganizationSearchResponseAllOfData.md)
 - [OrganizationUpdateResponse](Model/OrganizationUpdateResponse.md)
 - [OrganizationUpdateResponseAllOf](Model/OrganizationUpdateResponseAllOf.md)
 - [OrganizationsCollectionResponseObject](Model/OrganizationsCollectionResponseObject.md)
 - [OrganizationsCollectionResponseObjectAllOf](Model/OrganizationsCollectionResponseObjectAllOf.md)
 - [OrganizationsMergeResponse](Model/OrganizationsMergeResponse.md)
 - [OrganizationsMergeResponseData](Model/OrganizationsMergeResponseData.md)
 - [Owner](Model/Owner.md)
 - [OwnerAllOf](Model/OwnerAllOf.md)
 - [PaginationDetails](Model/PaginationDetails.md)
 - [PaginationDetailsAllOf](Model/PaginationDetailsAllOf.md)
 - [Params](Model/Params.md)
 - [ParticipantsChangelog](Model/ParticipantsChangelog.md)
 - [ParticipantsChangelogItem](Model/ParticipantsChangelogItem.md)
 - [PermissionSets](Model/PermissionSets.md)
 - [PermissionSetsAllOf](Model/PermissionSetsAllOf.md)
 - [PermissionSetsItem](Model/PermissionSetsItem.md)
 - [PersonCountAndEmailInfo](Model/PersonCountAndEmailInfo.md)
 - [PersonCountEmailDealAndActivityInfo](Model/PersonCountEmailDealAndActivityInfo.md)
 - [PersonCountInfo](Model/PersonCountInfo.md)
 - [PersonData](Model/PersonData.md)
 - [PersonDataEmail](Model/PersonDataEmail.md)
 - [PersonDataWithActiveFlag](Model/PersonDataWithActiveFlag.md)
 - [PersonDataWithActiveFlagAllOf](Model/PersonDataWithActiveFlagAllOf.md)
 - [PersonFlowResponse](Model/PersonFlowResponse.md)
 - [PersonFlowResponseAllOf](Model/PersonFlowResponseAllOf.md)
 - [PersonFlowResponseAllOfData](Model/PersonFlowResponseAllOfData.md)
 - [PersonItem](Model/PersonItem.md)
 - [PersonListProduct](Model/PersonListProduct.md)
 - [PersonNameCountAndEmailInfo](Model/PersonNameCountAndEmailInfo.md)
 - [PersonNameCountAndEmailInfoWithIds](Model/PersonNameCountAndEmailInfoWithIds.md)
 - [PersonNameCountAndEmailInfoWithIdsAllOf](Model/PersonNameCountAndEmailInfoWithIdsAllOf.md)
 - [PersonNameInfo](Model/PersonNameInfo.md)
 - [PersonNameInfoWithOrgAndOwnerId](Model/PersonNameInfoWithOrgAndOwnerId.md)
 - [PersonSearchItem](Model/PersonSearchItem.md)
 - [PersonSearchItemItem](Model/PersonSearchItemItem.md)
 - [PersonSearchItemItemOrganization](Model/PersonSearchItemItemOrganization.md)
 - [PersonSearchItemItemOwner](Model/PersonSearchItemItemOwner.md)
 - [PersonSearchResponse](Model/PersonSearchResponse.md)
 - [PersonSearchResponseAllOf](Model/PersonSearchResponseAllOf.md)
 - [PersonSearchResponseAllOfData](Model/PersonSearchResponseAllOfData.md)
 - [PersonsCollectionResponseObject](Model/PersonsCollectionResponseObject.md)
 - [PhoneData](Model/PhoneData.md)
 - [PictureData](Model/PictureData.md)
 - [PictureDataPictures](Model/PictureDataPictures.md)
 - [PictureDataWithID](Model/PictureDataWithID.md)
 - [PictureDataWithID1](Model/PictureDataWithID1.md)
 - [PictureDataWithValue](Model/PictureDataWithValue.md)
 - [PictureDataWithValue1](Model/PictureDataWithValue1.md)
 - [Pipeline](Model/Pipeline.md)
 - [PipelineDetails](Model/PipelineDetails.md)
 - [PostComment](Model/PostComment.md)
 - [PostDealParticipants](Model/PostDealParticipants.md)
 - [PostDealParticipantsRelatedObjects](Model/PostDealParticipantsRelatedObjects.md)
 - [PostGoalResponse](Model/PostGoalResponse.md)
 - [PostNote](Model/PostNote.md)
 - [PostRoleAssignment](Model/PostRoleAssignment.md)
 - [PostRoleSettings](Model/PostRoleSettings.md)
 - [PostRoles](Model/PostRoles.md)
 - [PostRolesAllOf](Model/PostRolesAllOf.md)
 - [ProductAttachementFields](Model/ProductAttachementFields.md)
 - [ProductAttachmentDetails](Model/ProductAttachmentDetails.md)
 - [ProductBaseDeal](Model/ProductBaseDeal.md)
 - [ProductField](Model/ProductField.md)
 - [ProductFieldAllOf](Model/ProductFieldAllOf.md)
 - [ProductFileItem](Model/ProductFileItem.md)
 - [ProductListItem](Model/ProductListItem.md)
 - [ProductRequest](Model/ProductRequest.md)
 - [ProductResponse](Model/ProductResponse.md)
 - [ProductSearchItem](Model/ProductSearchItem.md)
 - [ProductSearchItemItem](Model/ProductSearchItemItem.md)
 - [ProductSearchItemItemOwner](Model/ProductSearchItemItemOwner.md)
 - [ProductSearchResponse](Model/ProductSearchResponse.md)
 - [ProductSearchResponseAllOf](Model/ProductSearchResponseAllOf.md)
 - [ProductSearchResponseAllOfData](Model/ProductSearchResponseAllOfData.md)
 - [ProductWithArrayPrices](Model/ProductWithArrayPrices.md)
 - [ProductsResponse](Model/ProductsResponse.md)
 - [ProjectBoardObject](Model/ProjectBoardObject.md)
 - [ProjectGroupsObject](Model/ProjectGroupsObject.md)
 - [ProjectId](Model/ProjectId.md)
 - [ProjectMandatoryObjectFragment](Model/ProjectMandatoryObjectFragment.md)
 - [ProjectNotChangeableObjectFragment](Model/ProjectNotChangeableObjectFragment.md)
 - [ProjectObjectFragment](Model/ProjectObjectFragment.md)
 - [ProjectPhaseObject](Model/ProjectPhaseObject.md)
 - [ProjectPlanItemObject](Model/ProjectPlanItemObject.md)
 - [ProjectPostObject](Model/ProjectPostObject.md)
 - [ProjectPostObjectAllOf](Model/ProjectPostObjectAllOf.md)
 - [ProjectPutObject](Model/ProjectPutObject.md)
 - [ProjectPutPlanItemBodyObject](Model/ProjectPutPlanItemBodyObject.md)
 - [ProjectResponseObject](Model/ProjectResponseObject.md)
 - [PutRole](Model/PutRole.md)
 - [PutRoleAllOf](Model/PutRoleAllOf.md)
 - [PutRolePipelinesBody](Model/PutRolePipelinesBody.md)
 - [ReceiveMessageBadRequestErrorResponse](Model/ReceiveMessageBadRequestErrorResponse.md)
 - [ReceiveMessageBadRequestErrorResponseAdditionalData](Model/ReceiveMessageBadRequestErrorResponseAdditionalData.md)
 - [RecentDataProduct](Model/RecentDataProduct.md)
 - [RecentsActivity](Model/RecentsActivity.md)
 - [RecentsActivityType](Model/RecentsActivityType.md)
 - [RecentsDeal](Model/RecentsDeal.md)
 - [RecentsFile](Model/RecentsFile.md)
 - [RecentsFilter](Model/RecentsFilter.md)
 - [RecentsNote](Model/RecentsNote.md)
 - [RecentsOrganization](Model/RecentsOrganization.md)
 - [RecentsPerson](Model/RecentsPerson.md)
 - [RecentsPipeline](Model/RecentsPipeline.md)
 - [RecentsProduct](Model/RecentsProduct.md)
 - [RecentsStage](Model/RecentsStage.md)
 - [RecentsUser](Model/RecentsUser.md)
 - [RelatedDealData](Model/RelatedDealData.md)
 - [RelatedDealDataDEALID](Model/RelatedDealDataDEALID.md)
 - [RelatedFollowerData](Model/RelatedFollowerData.md)
 - [RelatedOrganizationData](Model/RelatedOrganizationData.md)
 - [RelatedOrganizationDataWithActiveFlag](Model/RelatedOrganizationDataWithActiveFlag.md)
 - [RelatedOrganizationName](Model/RelatedOrganizationName.md)
 - [RelatedPersonData](Model/RelatedPersonData.md)
 - [RelatedPersonDataWithActiveFlag](Model/RelatedPersonDataWithActiveFlag.md)
 - [RelatedPictureData](Model/RelatedPictureData.md)
 - [RelatedUserData](Model/RelatedUserData.md)
 - [RelationshipOrganizationInfoItem](Model/RelationshipOrganizationInfoItem.md)
 - [RelationshipOrganizationInfoItemAllOf](Model/RelationshipOrganizationInfoItemAllOf.md)
 - [RelationshipOrganizationInfoItemWithActiveFlag](Model/RelationshipOrganizationInfoItemWithActiveFlag.md)
 - [RelationshipOrganizationInfoItemWithActiveFlagAllOf](Model/RelationshipOrganizationInfoItemWithActiveFlagAllOf.md)
 - [RequiredPostProjectParameters](Model/RequiredPostProjectParameters.md)
 - [RequiredPostTaskParameters](Model/RequiredPostTaskParameters.md)
 - [RequiredTitleParameter](Model/RequiredTitleParameter.md)
 - [ResponseCallLogObject](Model/ResponseCallLogObject.md)
 - [ResponseCallLogObjectAllOf](Model/ResponseCallLogObjectAllOf.md)
 - [RoleAssignment](Model/RoleAssignment.md)
 - [RoleAssignmentData](Model/RoleAssignmentData.md)
 - [RoleSettings](Model/RoleSettings.md)
 - [RolesAdditionalData](Model/RolesAdditionalData.md)
 - [RolesAdditionalDataPagination](Model/RolesAdditionalDataPagination.md)
 - [SinglePermissionSetsItem](Model/SinglePermissionSetsItem.md)
 - [SinglePermissionSetsItemAllOf](Model/SinglePermissionSetsItemAllOf.md)
 - [Stage](Model/Stage.md)
 - [StageConversions](Model/StageConversions.md)
 - [StageDetails](Model/StageDetails.md)
 - [StageWithPipelineInfo](Model/StageWithPipelineInfo.md)
 - [StageWithPipelineInfo1](Model/StageWithPipelineInfo1.md)
 - [SubRole](Model/SubRole.md)
 - [SubRoleAllOf](Model/SubRoleAllOf.md)
 - [SubscriptionAddonsResponse](Model/SubscriptionAddonsResponse.md)
 - [SubscriptionAddonsResponseAllOf](Model/SubscriptionAddonsResponseAllOf.md)
 - [TaskId](Model/TaskId.md)
 - [TaskMandatoryObjectFragment](Model/TaskMandatoryObjectFragment.md)
 - [TaskNotChangeableObjectFragment](Model/TaskNotChangeableObjectFragment.md)
 - [TaskObjectFragment](Model/TaskObjectFragment.md)
 - [TaskPostObject](Model/TaskPostObject.md)
 - [TaskPutObject](Model/TaskPutObject.md)
 - [TaskResponseObject](Model/TaskResponseObject.md)
 - [Team](Model/Team.md)
 - [TeamAllOf](Model/TeamAllOf.md)
 - [TeamId](Model/TeamId.md)
 - [Teams](Model/Teams.md)
 - [TeamsAllOf](Model/TeamsAllOf.md)
 - [TemplateObject](Model/TemplateObject.md)
 - [TemplateResponseObject](Model/TemplateResponseObject.md)
 - [Unauthorized](Model/Unauthorized.md)
 - [UpdateActivityPlanItemResponse](Model/UpdateActivityPlanItemResponse.md)
 - [UpdateActivityResponse](Model/UpdateActivityResponse.md)
 - [UpdateDealParameters](Model/UpdateDealParameters.md)
 - [UpdateDealProduct](Model/UpdateDealProduct.md)
 - [UpdateDealRequest](Model/UpdateDealRequest.md)
 - [UpdateFile](Model/UpdateFile.md)
 - [UpdateFilterRequest](Model/UpdateFilterRequest.md)
 - [UpdateLeadLabelRequest](Model/UpdateLeadLabelRequest.md)
 - [UpdateLeadRequest](Model/UpdateLeadRequest.md)
 - [UpdateOrganization](Model/UpdateOrganization.md)
 - [UpdateOrganizationAllOf](Model/UpdateOrganizationAllOf.md)
 - [UpdatePerson](Model/UpdatePerson.md)
 - [UpdatePersonAllOf](Model/UpdatePersonAllOf.md)
 - [UpdatePersonResponse](Model/UpdatePersonResponse.md)
 - [UpdateProductField](Model/UpdateProductField.md)
 - [UpdateProductRequestBody](Model/UpdateProductRequestBody.md)
 - [UpdateProductResponse](Model/UpdateProductResponse.md)
 - [UpdateProjectResponse](Model/UpdateProjectResponse.md)
 - [UpdateRoleResponseData](Model/UpdateRoleResponseData.md)
 - [UpdateStageRequest](Model/UpdateStageRequest.md)
 - [UpdateStageRequestAllOf](Model/UpdateStageRequestAllOf.md)
 - [UpdateTaskPlanItemResponse](Model/UpdateTaskPlanItemResponse.md)
 - [UpdateTaskResponse](Model/UpdateTaskResponse.md)
 - [UpdateTeam](Model/UpdateTeam.md)
 - [UpdateTeamAllOf](Model/UpdateTeamAllOf.md)
 - [UpdateTeamWithAdditionalProperties](Model/UpdateTeamWithAdditionalProperties.md)
 - [UpdateUserRequest](Model/UpdateUserRequest.md)
 - [UpsertGoalResponse](Model/UpsertGoalResponse.md)
 - [UpsertLeadLabelResponse](Model/UpsertLeadLabelResponse.md)
 - [UpsertRoleSettingsResponseData](Model/UpsertRoleSettingsResponseData.md)
 - [UpsertRoleSettingsResponseDataData](Model/UpsertRoleSettingsResponseDataData.md)
 - [User](Model/User.md)
 - [UserAccess](Model/UserAccess.md)
 - [UserAssignmentToPermissionSet](Model/UserAssignmentToPermissionSet.md)
 - [UserAssignmentsToPermissionSet](Model/UserAssignmentsToPermissionSet.md)
 - [UserAssignmentsToPermissionSetAllOf](Model/UserAssignmentsToPermissionSetAllOf.md)
 - [UserConnections](Model/UserConnections.md)
 - [UserData](Model/UserData.md)
 - [UserDataWithId](Model/UserDataWithId.md)
 - [UserIDs](Model/UserIDs.md)
 - [UserIDsAllOf](Model/UserIDsAllOf.md)
 - [UserMe](Model/UserMe.md)
 - [UserMeAllOf](Model/UserMeAllOf.md)
 - [UserPermissions](Model/UserPermissions.md)
 - [UserPermissionsAllOf](Model/UserPermissionsAllOf.md)
 - [UserPermissionsItem](Model/UserPermissionsItem.md)
 - [UserProviderLinkCreateRequest](Model/UserProviderLinkCreateRequest.md)
 - [UserProviderLinkErrorResponse](Model/UserProviderLinkErrorResponse.md)
 - [UserProviderLinkSuccessResponse](Model/UserProviderLinkSuccessResponse.md)
 - [UserProviderLinkSuccessResponseData](Model/UserProviderLinkSuccessResponseData.md)
 - [UserSettings](Model/UserSettings.md)
 - [UserSettingsItem](Model/UserSettingsItem.md)
 - [Users](Model/Users.md)
 - [UsersAllOf](Model/UsersAllOf.md)
 - [VisibleTo](Model/VisibleTo.md)
 - [Webhook](Model/Webhook.md)
 - [WebhookBadRequest](Model/WebhookBadRequest.md)
 - [WebhookBadRequestAllOf](Model/WebhookBadRequestAllOf.md)
 - [Webhooks](Model/Webhooks.md)
 - [WebhooksAllOf](Model/WebhooksAllOf.md)
 - [WebhooksDeleteForbiddenSchema](Model/WebhooksDeleteForbiddenSchema.md)
 - [WebhooksDeleteForbiddenSchemaAllOf](Model/WebhooksDeleteForbiddenSchemaAllOf.md)


## Documentation for authorization



## api_key


- **Type**: API key
- **API key parameter name**: x-api-token
- **Location**: HTTP header




## basic_authentication


- **Type**: HTTP basic authentication



## oauth2


- **Type**: OAuth
- **Flow**: accessCode
- **Authorization URL**: https://oauth.pipedrive.com/oauth/authorize
- **Scopes**: 
- **base**: Read settings of the authorized user and currencies in an account
- **deals:read**: Read most of the data about deals and related entities - deal fields, products, followers, participants; all notes, files, filters, pipelines, stages, and statistics. Does not include access to activities (except the last and next activity related to a deal)
- **deals:full**: Create, read, update and delete deals, its participants and followers; all files, notes, and filters. It also includes read access to deal fields, pipelines, stages, and statistics. Does not include access to activities (except the last and next activity related to a deal)
- **mail:read**: Read mail threads and messages
- **mail:full**: Read, update and delete mail threads. Also grants read access to mail messages
- **activities:read**: Read activities, its fields and types; all files and filters
- **activities:full**: Create, read, update and delete activities and all files and filters. Also includes read access to activity fields and types
- **contacts:read**: Read the data about persons and organizations, their related fields and followers; also all notes, files, filters
- **contacts:full**: Create, read, update and delete persons and organizations and their followers; all notes, files, filters. Also grants read access to contacts-related fields
- **products:read**: Read products, its fields, files, followers and products connected to a deal
- **products:full**: Create, read, update and delete products and its fields; add products to deals
- **deal-fields:full**: Create, read, update and delete deal fields
- **product-fields:full**: Create, read, update and delete product fields
- **contact-fields:full**: Create, read, update and delete person and organization fields
- **projects:read**: Read projects and its fields, tasks and project templates
- **projects:full**: Create, read, update and delete projects and its fields; add projects templates and project related tasks
- **users:read**: Read data about users (people with access to a Pipedrive account), their permissions, roles and followers
- **recents:read**: Read all recent changes occurred in an account. Includes data about activities, activity types, deals, files, filters, notes, persons, organizations, pipelines, stages, products and users
- **search:read**: Search across the account for deals, persons, organizations, files and products, and see details about the returned results
- **admin**: Allows to do many things that an administrator can do in a Pipedrive company account - create, read, update and delete pipelines and its stages; deal, person and organization fields; activity types; users and permissions, etc. It also allows the app to create webhooks and fetch and delete webhooks that are created by the app
- **leads:read**: Read data about leads and lead labels
- **leads:full**: Create, read, update and delete leads and lead labels
- **phone-integration**: Enables advanced call integration features like logging call duration and other metadata, and play call recordings inside Pipedrive
- **goals:read**: Read data on all goals
- **goals:full**: Create, read, update and delete goals
- **video-calls**: Allows application to register as a video call integration provider and create conference links
- **messengers-integration**: Allows application to register as a messengers integration provider and allows them to deliver incoming messages and their statuses

