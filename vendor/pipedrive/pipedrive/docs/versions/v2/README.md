# Pipedrive client for API v2

## Getting started

Please follow the [installation procedure](#installation--usage) and then run the following:

### With a pre-set API token

```php
<?php

use Pipedrive\versions\v2\Configuration;

require_once('/path/to/client/vendor/autoload.php');

// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');

$apiInstance = new Pipedrive\versions\v2\Api\ActivitiesApi(
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

$config = (new Pipedrive\versions\v2\Configuration());
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
$config = (new Pipedrive\versions\v2\Configuration());
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

use Pipedrive\versions\v2\Api\DealsApi;
use Pipedrive\versions\v2\Configuration;

require_once('../../sdks/php/vendor/autoload.php');

session_start();

$config = (new Pipedrive\versions\v2\Configuration());
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

use Pipedrive\versions\v2\Configuration;

session_start();

$config = (new Pipedrive\versions\v2\Configuration());

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

All URIs are relative to *https://api.pipedrive.com/api/v2*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*ActivitiesApi* | [**addActivity**](Api/ActivitiesApi.md#addactivity) | **POST** /activities | Add a new activity
*ActivitiesApi* | [**deleteActivity**](Api/ActivitiesApi.md#deleteactivity) | **DELETE** /activities/{id} | Delete an activity
*ActivitiesApi* | [**getActivities**](Api/ActivitiesApi.md#getactivities) | **GET** /activities | Get all activities
*ActivitiesApi* | [**getActivity**](Api/ActivitiesApi.md#getactivity) | **GET** /activities/{id} | Get details of an activity
*ActivitiesApi* | [**updateActivity**](Api/ActivitiesApi.md#updateactivity) | **PATCH** /activities/{id} | Update an activity
*ActivityFieldsApi* | [**getActivityField**](Api/ActivityFieldsApi.md#getactivityfield) | **GET** /activityFields/{field_code} | Get one activity field
*ActivityFieldsApi* | [**getActivityFields**](Api/ActivityFieldsApi.md#getactivityfields) | **GET** /activityFields | Get all activity fields
*BetaApi* | [**convertDealToLead**](Api/BetaApi.md#convertdealtolead) | **POST** /deals/{id}/convert/lead | Convert a deal to a lead (BETA)
*BetaApi* | [**convertLeadToDeal**](Api/BetaApi.md#convertleadtodeal) | **POST** /leads/{id}/convert/deal | Convert a lead to a deal (BETA)
*BetaApi* | [**deleteInstallment**](Api/BetaApi.md#deleteinstallment) | **DELETE** /deals/{id}/installments/{installment_id} | Delete an installment from a deal
*BetaApi* | [**deleteProductImage**](Api/BetaApi.md#deleteproductimage) | **DELETE** /products/{id}/images | Delete an image of a product
*BetaApi* | [**getDealConversionStatus**](Api/BetaApi.md#getdealconversionstatus) | **GET** /deals/{id}/convert/status/{conversion_id} | Get Deal conversion status (BETA)
*BetaApi* | [**getInstallments**](Api/BetaApi.md#getinstallments) | **GET** /deals/installments | List installments added to a list of deals
*BetaApi* | [**getLeadConversionStatus**](Api/BetaApi.md#getleadconversionstatus) | **GET** /leads/{id}/convert/status/{conversion_id} | Get Lead conversion status (BETA)
*BetaApi* | [**getProductImage**](Api/BetaApi.md#getproductimage) | **GET** /products/{id}/images | Get image of a product
*BetaApi* | [**postInstallment**](Api/BetaApi.md#postinstallment) | **POST** /deals/{id}/installments | Add an installment to a deal
*BetaApi* | [**updateInstallment**](Api/BetaApi.md#updateinstallment) | **PATCH** /deals/{id}/installments/{installment_id} | Update an installment added to a deal
*BetaApi* | [**updateProductImage**](Api/BetaApi.md#updateproductimage) | **PUT** /products/{id}/images | Update an image for a product
*BetaApi* | [**uploadProductImage**](Api/BetaApi.md#uploadproductimage) | **POST** /products/{id}/images | Upload an image for a product
*DealFieldsApi* | [**addDealField**](Api/DealFieldsApi.md#adddealfield) | **POST** /dealFields | Create one deal field
*DealFieldsApi* | [**addDealFieldOptions**](Api/DealFieldsApi.md#adddealfieldoptions) | **POST** /dealFields/{field_code}/options | Add deal field options in bulk
*DealFieldsApi* | [**deleteDealField**](Api/DealFieldsApi.md#deletedealfield) | **DELETE** /dealFields/{field_code} | Delete one deal field
*DealFieldsApi* | [**deleteDealFieldOptions**](Api/DealFieldsApi.md#deletedealfieldoptions) | **DELETE** /dealFields/{field_code}/options | Delete deal field options in bulk
*DealFieldsApi* | [**getDealField**](Api/DealFieldsApi.md#getdealfield) | **GET** /dealFields/{field_code} | Get one deal field
*DealFieldsApi* | [**getDealFields**](Api/DealFieldsApi.md#getdealfields) | **GET** /dealFields | Get all deal fields
*DealFieldsApi* | [**updateDealField**](Api/DealFieldsApi.md#updatedealfield) | **PATCH** /dealFields/{field_code} | Update one deal field
*DealFieldsApi* | [**updateDealFieldOptions**](Api/DealFieldsApi.md#updatedealfieldoptions) | **PATCH** /dealFields/{field_code}/options | Update deal field options in bulk
*DealsApi* | [**addDeal**](Api/DealsApi.md#adddeal) | **POST** /deals | Add a new deal
*DealsApi* | [**addDealFollower**](Api/DealsApi.md#adddealfollower) | **POST** /deals/{id}/followers | Add a follower to a deal
*DealsApi* | [**addDealProduct**](Api/DealsApi.md#adddealproduct) | **POST** /deals/{id}/products | Add a product to a deal
*DealsApi* | [**addManyDealProducts**](Api/DealsApi.md#addmanydealproducts) | **POST** /deals/{id}/products/bulk | Add multiple products to a deal
*DealsApi* | [**convertDealToLead**](Api/DealsApi.md#convertdealtolead) | **POST** /deals/{id}/convert/lead | Convert a deal to a lead (BETA)
*DealsApi* | [**deleteAdditionalDiscount**](Api/DealsApi.md#deleteadditionaldiscount) | **DELETE** /deals/{id}/discounts/{discount_id} | Delete a discount from a deal
*DealsApi* | [**deleteDeal**](Api/DealsApi.md#deletedeal) | **DELETE** /deals/{id} | Delete a deal
*DealsApi* | [**deleteDealFollower**](Api/DealsApi.md#deletedealfollower) | **DELETE** /deals/{id}/followers/{follower_id} | Delete a follower from a deal
*DealsApi* | [**deleteDealProduct**](Api/DealsApi.md#deletedealproduct) | **DELETE** /deals/{id}/products/{product_attachment_id} | Delete an attached product from a deal
*DealsApi* | [**deleteInstallment**](Api/DealsApi.md#deleteinstallment) | **DELETE** /deals/{id}/installments/{installment_id} | Delete an installment from a deal
*DealsApi* | [**deleteManyDealProducts**](Api/DealsApi.md#deletemanydealproducts) | **DELETE** /deals/{id}/products | Delete many products from a deal
*DealsApi* | [**getAdditionalDiscounts**](Api/DealsApi.md#getadditionaldiscounts) | **GET** /deals/{id}/discounts | List discounts added to a deal
*DealsApi* | [**getArchivedDeals**](Api/DealsApi.md#getarchiveddeals) | **GET** /deals/archived | Get all archived deals
*DealsApi* | [**getDeal**](Api/DealsApi.md#getdeal) | **GET** /deals/{id} | Get details of a deal
*DealsApi* | [**getDealConversionStatus**](Api/DealsApi.md#getdealconversionstatus) | **GET** /deals/{id}/convert/status/{conversion_id} | Get Deal conversion status (BETA)
*DealsApi* | [**getDealFollowers**](Api/DealsApi.md#getdealfollowers) | **GET** /deals/{id}/followers | List followers of a deal
*DealsApi* | [**getDealFollowersChangelog**](Api/DealsApi.md#getdealfollowerschangelog) | **GET** /deals/{id}/followers/changelog | List followers changelog of a deal
*DealsApi* | [**getDealProducts**](Api/DealsApi.md#getdealproducts) | **GET** /deals/{id}/products | List products attached to a deal
*DealsApi* | [**getDeals**](Api/DealsApi.md#getdeals) | **GET** /deals | Get all deals
*DealsApi* | [**getDealsProducts**](Api/DealsApi.md#getdealsproducts) | **GET** /deals/products | Get deal products of several deals
*DealsApi* | [**getInstallments**](Api/DealsApi.md#getinstallments) | **GET** /deals/installments | List installments added to a list of deals
*DealsApi* | [**postAdditionalDiscount**](Api/DealsApi.md#postadditionaldiscount) | **POST** /deals/{id}/discounts | Add a discount to a deal
*DealsApi* | [**postInstallment**](Api/DealsApi.md#postinstallment) | **POST** /deals/{id}/installments | Add an installment to a deal
*DealsApi* | [**searchDeals**](Api/DealsApi.md#searchdeals) | **GET** /deals/search | Search deals
*DealsApi* | [**updateAdditionalDiscount**](Api/DealsApi.md#updateadditionaldiscount) | **PATCH** /deals/{id}/discounts/{discount_id} | Update a discount added to a deal
*DealsApi* | [**updateDeal**](Api/DealsApi.md#updatedeal) | **PATCH** /deals/{id} | Update a deal
*DealsApi* | [**updateDealProduct**](Api/DealsApi.md#updatedealproduct) | **PATCH** /deals/{id}/products/{product_attachment_id} | Update the product attached to a deal
*DealsApi* | [**updateInstallment**](Api/DealsApi.md#updateinstallment) | **PATCH** /deals/{id}/installments/{installment_id} | Update an installment added to a deal
*ItemSearchApi* | [**searchItem**](Api/ItemSearchApi.md#searchitem) | **GET** /itemSearch | Perform a search from multiple item types
*ItemSearchApi* | [**searchItemByField**](Api/ItemSearchApi.md#searchitembyfield) | **GET** /itemSearch/field | Perform a search using a specific field from an item type
*LeadsApi* | [**convertLeadToDeal**](Api/LeadsApi.md#convertleadtodeal) | **POST** /leads/{id}/convert/deal | Convert a lead to a deal (BETA)
*LeadsApi* | [**getLeadConversionStatus**](Api/LeadsApi.md#getleadconversionstatus) | **GET** /leads/{id}/convert/status/{conversion_id} | Get Lead conversion status (BETA)
*LeadsApi* | [**searchLeads**](Api/LeadsApi.md#searchleads) | **GET** /leads/search | Search leads
*OrganizationFieldsApi* | [**addOrganizationField**](Api/OrganizationFieldsApi.md#addorganizationfield) | **POST** /organizationFields | Create one organization field
*OrganizationFieldsApi* | [**addOrganizationFieldOptions**](Api/OrganizationFieldsApi.md#addorganizationfieldoptions) | **POST** /organizationFields/{field_code}/options | Add organization field options in bulk
*OrganizationFieldsApi* | [**deleteOrganizationField**](Api/OrganizationFieldsApi.md#deleteorganizationfield) | **DELETE** /organizationFields/{field_code} | Delete one organization field
*OrganizationFieldsApi* | [**deleteOrganizationFieldOptions**](Api/OrganizationFieldsApi.md#deleteorganizationfieldoptions) | **DELETE** /organizationFields/{field_code}/options | Delete organization field options in bulk
*OrganizationFieldsApi* | [**getOrganizationField**](Api/OrganizationFieldsApi.md#getorganizationfield) | **GET** /organizationFields/{field_code} | Get one organization field
*OrganizationFieldsApi* | [**getOrganizationFields**](Api/OrganizationFieldsApi.md#getorganizationfields) | **GET** /organizationFields | Get all organization fields
*OrganizationFieldsApi* | [**updateOrganizationField**](Api/OrganizationFieldsApi.md#updateorganizationfield) | **PATCH** /organizationFields/{field_code} | Update one organization field
*OrganizationFieldsApi* | [**updateOrganizationFieldOptions**](Api/OrganizationFieldsApi.md#updateorganizationfieldoptions) | **PATCH** /organizationFields/{field_code}/options | Update organization field options in bulk
*OrganizationsApi* | [**addOrganization**](Api/OrganizationsApi.md#addorganization) | **POST** /organizations | Add a new organization
*OrganizationsApi* | [**addOrganizationFollower**](Api/OrganizationsApi.md#addorganizationfollower) | **POST** /organizations/{id}/followers | Add a follower to an organization
*OrganizationsApi* | [**deleteOrganization**](Api/OrganizationsApi.md#deleteorganization) | **DELETE** /organizations/{id} | Delete a organization
*OrganizationsApi* | [**deleteOrganizationFollower**](Api/OrganizationsApi.md#deleteorganizationfollower) | **DELETE** /organizations/{id}/followers/{follower_id} | Delete a follower from an organization
*OrganizationsApi* | [**getOrganization**](Api/OrganizationsApi.md#getorganization) | **GET** /organizations/{id} | Get details of a organization
*OrganizationsApi* | [**getOrganizationFollowers**](Api/OrganizationsApi.md#getorganizationfollowers) | **GET** /organizations/{id}/followers | List followers of an organization
*OrganizationsApi* | [**getOrganizationFollowersChangelog**](Api/OrganizationsApi.md#getorganizationfollowerschangelog) | **GET** /organizations/{id}/followers/changelog | List followers changelog of an organization
*OrganizationsApi* | [**getOrganizations**](Api/OrganizationsApi.md#getorganizations) | **GET** /organizations | Get all organizations
*OrganizationsApi* | [**searchOrganization**](Api/OrganizationsApi.md#searchorganization) | **GET** /organizations/search | Search organizations
*OrganizationsApi* | [**updateOrganization**](Api/OrganizationsApi.md#updateorganization) | **PATCH** /organizations/{id} | Update a organization
*PersonFieldsApi* | [**addPersonField**](Api/PersonFieldsApi.md#addpersonfield) | **POST** /personFields | Create one person field
*PersonFieldsApi* | [**addPersonFieldOptions**](Api/PersonFieldsApi.md#addpersonfieldoptions) | **POST** /personFields/{field_code}/options | Add person field options in bulk
*PersonFieldsApi* | [**deletePersonField**](Api/PersonFieldsApi.md#deletepersonfield) | **DELETE** /personFields/{field_code} | Delete one person field
*PersonFieldsApi* | [**deletePersonFieldOptions**](Api/PersonFieldsApi.md#deletepersonfieldoptions) | **DELETE** /personFields/{field_code}/options | Delete person field options in bulk
*PersonFieldsApi* | [**getPersonField**](Api/PersonFieldsApi.md#getpersonfield) | **GET** /personFields/{field_code} | Get one person field
*PersonFieldsApi* | [**getPersonFields**](Api/PersonFieldsApi.md#getpersonfields) | **GET** /personFields | Get all person fields
*PersonFieldsApi* | [**updatePersonField**](Api/PersonFieldsApi.md#updatepersonfield) | **PATCH** /personFields/{field_code} | Update one person field
*PersonFieldsApi* | [**updatePersonFieldOptions**](Api/PersonFieldsApi.md#updatepersonfieldoptions) | **PATCH** /personFields/{field_code}/options | Update person field options in bulk
*PersonsApi* | [**addPerson**](Api/PersonsApi.md#addperson) | **POST** /persons | Add a new person
*PersonsApi* | [**addPersonFollower**](Api/PersonsApi.md#addpersonfollower) | **POST** /persons/{id}/followers | Add a follower to a person
*PersonsApi* | [**deletePerson**](Api/PersonsApi.md#deleteperson) | **DELETE** /persons/{id} | Delete a person
*PersonsApi* | [**deletePersonFollower**](Api/PersonsApi.md#deletepersonfollower) | **DELETE** /persons/{id}/followers/{follower_id} | Delete a follower from a person
*PersonsApi* | [**getPerson**](Api/PersonsApi.md#getperson) | **GET** /persons/{id} | Get details of a person
*PersonsApi* | [**getPersonFollowers**](Api/PersonsApi.md#getpersonfollowers) | **GET** /persons/{id}/followers | List followers of a person
*PersonsApi* | [**getPersonFollowersChangelog**](Api/PersonsApi.md#getpersonfollowerschangelog) | **GET** /persons/{id}/followers/changelog | List followers changelog of a person
*PersonsApi* | [**getPersons**](Api/PersonsApi.md#getpersons) | **GET** /persons | Get all persons
*PersonsApi* | [**searchPersons**](Api/PersonsApi.md#searchpersons) | **GET** /persons/search | Search persons
*PersonsApi* | [**updatePerson**](Api/PersonsApi.md#updateperson) | **PATCH** /persons/{id} | Update a person
*PipelinesApi* | [**addPipeline**](Api/PipelinesApi.md#addpipeline) | **POST** /pipelines | Add a new pipeline
*PipelinesApi* | [**deletePipeline**](Api/PipelinesApi.md#deletepipeline) | **DELETE** /pipelines/{id} | Delete a pipeline
*PipelinesApi* | [**getPipeline**](Api/PipelinesApi.md#getpipeline) | **GET** /pipelines/{id} | Get one pipeline
*PipelinesApi* | [**getPipelines**](Api/PipelinesApi.md#getpipelines) | **GET** /pipelines | Get all pipelines
*PipelinesApi* | [**updatePipeline**](Api/PipelinesApi.md#updatepipeline) | **PATCH** /pipelines/{id} | Update a pipeline
*ProductFieldsApi* | [**addProductField**](Api/ProductFieldsApi.md#addproductfield) | **POST** /productFields | Create one product field
*ProductFieldsApi* | [**addProductFieldOptions**](Api/ProductFieldsApi.md#addproductfieldoptions) | **POST** /productFields/{field_code}/options | Add product field options in bulk
*ProductFieldsApi* | [**deleteProductField**](Api/ProductFieldsApi.md#deleteproductfield) | **DELETE** /productFields/{field_code} | Delete one product field
*ProductFieldsApi* | [**deleteProductFieldOptions**](Api/ProductFieldsApi.md#deleteproductfieldoptions) | **DELETE** /productFields/{field_code}/options | Delete product field options in bulk
*ProductFieldsApi* | [**getProductField**](Api/ProductFieldsApi.md#getproductfield) | **GET** /productFields/{field_code} | Get one product field
*ProductFieldsApi* | [**getProductFields**](Api/ProductFieldsApi.md#getproductfields) | **GET** /productFields | Get all product fields
*ProductFieldsApi* | [**updateProductField**](Api/ProductFieldsApi.md#updateproductfield) | **PATCH** /productFields/{field_code} | Update one product field
*ProductFieldsApi* | [**updateProductFieldOptions**](Api/ProductFieldsApi.md#updateproductfieldoptions) | **PATCH** /productFields/{field_code}/options | Update product field options in bulk
*ProductsApi* | [**addProduct**](Api/ProductsApi.md#addproduct) | **POST** /products | Add a product
*ProductsApi* | [**addProductFollower**](Api/ProductsApi.md#addproductfollower) | **POST** /products/{id}/followers | Add a follower to a product
*ProductsApi* | [**addProductVariation**](Api/ProductsApi.md#addproductvariation) | **POST** /products/{id}/variations | Add a product variation
*ProductsApi* | [**deleteProduct**](Api/ProductsApi.md#deleteproduct) | **DELETE** /products/{id} | Delete a product
*ProductsApi* | [**deleteProductFollower**](Api/ProductsApi.md#deleteproductfollower) | **DELETE** /products/{id}/followers/{follower_id} | Delete a follower from a product
*ProductsApi* | [**deleteProductImage**](Api/ProductsApi.md#deleteproductimage) | **DELETE** /products/{id}/images | Delete an image of a product
*ProductsApi* | [**deleteProductVariation**](Api/ProductsApi.md#deleteproductvariation) | **DELETE** /products/{id}/variations/{product_variation_id} | Delete a product variation
*ProductsApi* | [**duplicateProduct**](Api/ProductsApi.md#duplicateproduct) | **POST** /products/{id}/duplicate | Duplicate a product
*ProductsApi* | [**getProduct**](Api/ProductsApi.md#getproduct) | **GET** /products/{id} | Get one product
*ProductsApi* | [**getProductFollowers**](Api/ProductsApi.md#getproductfollowers) | **GET** /products/{id}/followers | List followers of a product
*ProductsApi* | [**getProductFollowersChangelog**](Api/ProductsApi.md#getproductfollowerschangelog) | **GET** /products/{id}/followers/changelog | List followers changelog of a product
*ProductsApi* | [**getProductImage**](Api/ProductsApi.md#getproductimage) | **GET** /products/{id}/images | Get image of a product
*ProductsApi* | [**getProductVariations**](Api/ProductsApi.md#getproductvariations) | **GET** /products/{id}/variations | Get all product variations
*ProductsApi* | [**getProducts**](Api/ProductsApi.md#getproducts) | **GET** /products | Get all products
*ProductsApi* | [**searchProducts**](Api/ProductsApi.md#searchproducts) | **GET** /products/search | Search products
*ProductsApi* | [**updateProduct**](Api/ProductsApi.md#updateproduct) | **PATCH** /products/{id} | Update a product
*ProductsApi* | [**updateProductImage**](Api/ProductsApi.md#updateproductimage) | **PUT** /products/{id}/images | Update an image for a product
*ProductsApi* | [**updateProductVariation**](Api/ProductsApi.md#updateproductvariation) | **PATCH** /products/{id}/variations/{product_variation_id} | Update a product variation
*ProductsApi* | [**uploadProductImage**](Api/ProductsApi.md#uploadproductimage) | **POST** /products/{id}/images | Upload an image for a product
*StagesApi* | [**addStage**](Api/StagesApi.md#addstage) | **POST** /stages | Add a new stage
*StagesApi* | [**deleteStage**](Api/StagesApi.md#deletestage) | **DELETE** /stages/{id} | Delete a stage
*StagesApi* | [**getStage**](Api/StagesApi.md#getstage) | **GET** /stages/{id} | Get one stage
*StagesApi* | [**getStages**](Api/StagesApi.md#getstages) | **GET** /stages | Get all stages
*StagesApi* | [**updateStage**](Api/StagesApi.md#updatestage) | **PATCH** /stages/{id} | Update stage details
*UsersApi* | [**getUserFollowers**](Api/UsersApi.md#getuserfollowers) | **GET** /users/{id}/followers | List followers of a user


## Documentation for models

 - [ActivityFieldItem](Model/ActivityFieldItem.md)
 - [ActivityFieldItemOptions](Model/ActivityFieldItemOptions.md)
 - [ActivityFieldItemSubfields](Model/ActivityFieldItemSubfields.md)
 - [ActivityFieldItemUiVisibility](Model/ActivityFieldItemUiVisibility.md)
 - [ActivityItem](Model/ActivityItem.md)
 - [ActivityItemAttendees](Model/ActivityItemAttendees.md)
 - [ActivityItemLocation](Model/ActivityItemLocation.md)
 - [ActivityItemParticipants](Model/ActivityItemParticipants.md)
 - [ActivityRequestBody](Model/ActivityRequestBody.md)
 - [AddAdditionalDiscountResponse](Model/AddAdditionalDiscountResponse.md)
 - [AddConvertDealToLeadResponse](Model/AddConvertDealToLeadResponse.md)
 - [AddConvertLeadToDealResponse](Model/AddConvertLeadToDealResponse.md)
 - [AddDealProductResponse](Model/AddDealProductResponse.md)
 - [AddInstallmentResponse](Model/AddInstallmentResponse.md)
 - [AddProductImageResponse](Model/AddProductImageResponse.md)
 - [AddProductRequestBody](Model/AddProductRequestBody.md)
 - [AdditionalDataWithCursorPagination](Model/AdditionalDataWithCursorPagination.md)
 - [AdditionalDiscountRequestBody](Model/AdditionalDiscountRequestBody.md)
 - [AdditionalDiscountsResponse](Model/AdditionalDiscountsResponse.md)
 - [ArrayPrices](Model/ArrayPrices.md)
 - [BaseAdditionalDiscount](Model/BaseAdditionalDiscount.md)
 - [BaseDealProduct](Model/BaseDealProduct.md)
 - [BaseDealProductAllOf](Model/BaseDealProductAllOf.md)
 - [BaseDealProductAllOf1](Model/BaseDealProductAllOf1.md)
 - [BaseDealProductAllOf2](Model/BaseDealProductAllOf2.md)
 - [BaseDealProductAllOf3](Model/BaseDealProductAllOf3.md)
 - [BaseInstallment](Model/BaseInstallment.md)
 - [BaseProduct](Model/BaseProduct.md)
 - [BaseProductAllOf](Model/BaseProductAllOf.md)
 - [BaseProductAllOf1](Model/BaseProductAllOf1.md)
 - [BaseProductImage](Model/BaseProductImage.md)
 - [BaseResponse](Model/BaseResponse.md)
 - [BillingFrequency](Model/BillingFrequency.md)
 - [BillingFrequency1](Model/BillingFrequency1.md)
 - [ConvertEntityResponse](Model/ConvertEntityResponse.md)
 - [CreateDealField](Model/CreateDealField.md)
 - [CreateDealFieldRequest](Model/CreateDealFieldRequest.md)
 - [CreateDealFieldRequestOptions](Model/CreateDealFieldRequestOptions.md)
 - [CreateManyDealProductRequestBody](Model/CreateManyDealProductRequestBody.md)
 - [CreateManyDealProductResponse](Model/CreateManyDealProductResponse.md)
 - [CreateOrganizationField](Model/CreateOrganizationField.md)
 - [CreateOrganizationFieldRequest](Model/CreateOrganizationFieldRequest.md)
 - [CreatePersonField](Model/CreatePersonField.md)
 - [CreatePersonFieldRequest](Model/CreatePersonFieldRequest.md)
 - [CreateProductField](Model/CreateProductField.md)
 - [CreateProductFieldRequest](Model/CreateProductFieldRequest.md)
 - [DealFieldItem](Model/DealFieldItem.md)
 - [DealFieldItemImportantFields](Model/DealFieldItemImportantFields.md)
 - [DealFieldItemRequiredFields](Model/DealFieldItemRequiredFields.md)
 - [DealFieldItemUiVisibility](Model/DealFieldItemUiVisibility.md)
 - [DealFieldItemUiVisibilityShowInPipelines](Model/DealFieldItemUiVisibilityShowInPipelines.md)
 - [DealItem](Model/DealItem.md)
 - [DealProductRequestBody](Model/DealProductRequestBody.md)
 - [DealRequestBody](Model/DealRequestBody.md)
 - [DealSearchItem](Model/DealSearchItem.md)
 - [DealSearchItemItem](Model/DealSearchItemItem.md)
 - [DealSearchItemItemOrganization](Model/DealSearchItemItemOrganization.md)
 - [DealSearchItemItemOwner](Model/DealSearchItemItemOwner.md)
 - [DealSearchItemItemPerson](Model/DealSearchItemItemPerson.md)
 - [DealSearchItemItemStage](Model/DealSearchItemItemStage.md)
 - [DealsProductsResponse](Model/DealsProductsResponse.md)
 - [DeleteActivityResponse](Model/DeleteActivityResponse.md)
 - [DeleteActivityResponseData](Model/DeleteActivityResponseData.md)
 - [DeleteAdditionalDiscountResponse](Model/DeleteAdditionalDiscountResponse.md)
 - [DeleteAdditionalDiscountResponseData](Model/DeleteAdditionalDiscountResponseData.md)
 - [DeleteDealField](Model/DeleteDealField.md)
 - [DeleteDealFieldData](Model/DeleteDealFieldData.md)
 - [DeleteDealProduct](Model/DeleteDealProduct.md)
 - [DeleteDealProductData](Model/DeleteDealProductData.md)
 - [DeleteDealResponse](Model/DeleteDealResponse.md)
 - [DeleteDealResponseData](Model/DeleteDealResponseData.md)
 - [DeleteFollowerResponse](Model/DeleteFollowerResponse.md)
 - [DeleteFollowerResponseData](Model/DeleteFollowerResponseData.md)
 - [DeleteInstallmentResponse](Model/DeleteInstallmentResponse.md)
 - [DeleteInstallmentResponseData](Model/DeleteInstallmentResponseData.md)
 - [DeleteManyDealProductResponse](Model/DeleteManyDealProductResponse.md)
 - [DeleteManyDealProductResponseAdditionalData](Model/DeleteManyDealProductResponseAdditionalData.md)
 - [DeleteManyDealProductResponseData](Model/DeleteManyDealProductResponseData.md)
 - [DeleteOrganizationField](Model/DeleteOrganizationField.md)
 - [DeleteOrganizationResponse](Model/DeleteOrganizationResponse.md)
 - [DeleteOrganizationResponseData](Model/DeleteOrganizationResponseData.md)
 - [DeletePersonField](Model/DeletePersonField.md)
 - [DeletePersonFieldData](Model/DeletePersonFieldData.md)
 - [DeletePersonResponse](Model/DeletePersonResponse.md)
 - [DeletePersonResponseData](Model/DeletePersonResponseData.md)
 - [DeletePipelineResponse](Model/DeletePipelineResponse.md)
 - [DeletePipelineResponseData](Model/DeletePipelineResponseData.md)
 - [DeleteProductField](Model/DeleteProductField.md)
 - [DeleteProductImageResponse](Model/DeleteProductImageResponse.md)
 - [DeleteProductImageResponseData](Model/DeleteProductImageResponseData.md)
 - [DeleteProductResponse](Model/DeleteProductResponse.md)
 - [DeleteProductResponseData](Model/DeleteProductResponseData.md)
 - [DeleteProductVariation](Model/DeleteProductVariation.md)
 - [DeleteProductVariationData](Model/DeleteProductVariationData.md)
 - [DeleteStageResponse](Model/DeleteStageResponse.md)
 - [DeleteStageResponseData](Model/DeleteStageResponseData.md)
 - [FieldOption](Model/FieldOption.md)
 - [FollowerChangelogItem](Model/FollowerChangelogItem.md)
 - [FollowerItem](Model/FollowerItem.md)
 - [FollowerRequestBody](Model/FollowerRequestBody.md)
 - [GetActivities](Model/GetActivities.md)
 - [GetActivitiesAllOf](Model/GetActivitiesAllOf.md)
 - [GetActivityField](Model/GetActivityField.md)
 - [GetActivityFields](Model/GetActivityFields.md)
 - [GetActivityFieldsAdditionalData](Model/GetActivityFieldsAdditionalData.md)
 - [GetConvertResponse](Model/GetConvertResponse.md)
 - [GetDealField](Model/GetDealField.md)
 - [GetDealFields](Model/GetDealFields.md)
 - [GetDealSearchResponse](Model/GetDealSearchResponse.md)
 - [GetDealSearchResponseAllOf](Model/GetDealSearchResponseAllOf.md)
 - [GetDealSearchResponseAllOfData](Model/GetDealSearchResponseAllOfData.md)
 - [GetDeals](Model/GetDeals.md)
 - [GetDealsAllOf](Model/GetDealsAllOf.md)
 - [GetFollowerChangelogs](Model/GetFollowerChangelogs.md)
 - [GetFollowerChangelogsAllOf](Model/GetFollowerChangelogsAllOf.md)
 - [GetFollowers](Model/GetFollowers.md)
 - [GetFollowersAllOf](Model/GetFollowersAllOf.md)
 - [GetItemSearchResponseData](Model/GetItemSearchResponseData.md)
 - [GetItemSearchResponseDataData](Model/GetItemSearchResponseDataData.md)
 - [GetLeadSearchResponseData](Model/GetLeadSearchResponseData.md)
 - [GetLeadSearchResponseDataData](Model/GetLeadSearchResponseDataData.md)
 - [GetOrganizationField](Model/GetOrganizationField.md)
 - [GetOrganizationFields](Model/GetOrganizationFields.md)
 - [GetOrganizationSearchResponse](Model/GetOrganizationSearchResponse.md)
 - [GetOrganizationSearchResponseAllOf](Model/GetOrganizationSearchResponseAllOf.md)
 - [GetOrganizationSearchResponseAllOfData](Model/GetOrganizationSearchResponseAllOfData.md)
 - [GetOrganizations](Model/GetOrganizations.md)
 - [GetOrganizationsAllOf](Model/GetOrganizationsAllOf.md)
 - [GetPersonField](Model/GetPersonField.md)
 - [GetPersonFields](Model/GetPersonFields.md)
 - [GetPersons](Model/GetPersons.md)
 - [GetPersonsAllOf](Model/GetPersonsAllOf.md)
 - [GetPipelines](Model/GetPipelines.md)
 - [GetPipelinesAllOf](Model/GetPipelinesAllOf.md)
 - [GetProductField](Model/GetProductField.md)
 - [GetProductFields](Model/GetProductFields.md)
 - [GetStages](Model/GetStages.md)
 - [InlineObject](Model/InlineObject.md)
 - [InlineResponse200](Model/InlineResponse200.md)
 - [InstallmentRequestBody](Model/InstallmentRequestBody.md)
 - [InstallmentsResponse](Model/InstallmentsResponse.md)
 - [ItemSearchFieldResponse](Model/ItemSearchFieldResponse.md)
 - [ItemSearchFieldResponseAllOf](Model/ItemSearchFieldResponseAllOf.md)
 - [ItemSearchItem](Model/ItemSearchItem.md)
 - [ItemSearchResponse](Model/ItemSearchResponse.md)
 - [LeadSearchItem](Model/LeadSearchItem.md)
 - [LeadSearchItemItem](Model/LeadSearchItemItem.md)
 - [LeadSearchItemItemOrganization](Model/LeadSearchItemItemOrganization.md)
 - [LeadSearchItemItemOwner](Model/LeadSearchItemItemOwner.md)
 - [LeadSearchItemItemPerson](Model/LeadSearchItemItemPerson.md)
 - [LeadSearchResponse](Model/LeadSearchResponse.md)
 - [NameObject](Model/NameObject.md)
 - [NewDealProductRequestBody](Model/NewDealProductRequestBody.md)
 - [OrganizationFieldItem](Model/OrganizationFieldItem.md)
 - [OrganizationFieldItemUiVisibility](Model/OrganizationFieldItemUiVisibility.md)
 - [OrganizationFieldItemUiVisibilityShowInAddPersonDialog](Model/OrganizationFieldItemUiVisibilityShowInAddPersonDialog.md)
 - [OrganizationItem](Model/OrganizationItem.md)
 - [OrganizationItemAddress](Model/OrganizationItemAddress.md)
 - [OrganizationRequestBody](Model/OrganizationRequestBody.md)
 - [OrganizationSearchItem](Model/OrganizationSearchItem.md)
 - [OrganizationSearchItemItem](Model/OrganizationSearchItemItem.md)
 - [PersonFieldItem](Model/PersonFieldItem.md)
 - [PersonFieldItemImportantFields](Model/PersonFieldItemImportantFields.md)
 - [PersonFieldItemRequiredFields](Model/PersonFieldItemRequiredFields.md)
 - [PersonFieldItemUiVisibility](Model/PersonFieldItemUiVisibility.md)
 - [PersonFieldItemUiVisibilityShowInAddDealDialog](Model/PersonFieldItemUiVisibilityShowInAddDealDialog.md)
 - [PersonItem](Model/PersonItem.md)
 - [PersonItemEmails](Model/PersonItemEmails.md)
 - [PersonItemIm](Model/PersonItemIm.md)
 - [PersonItemPhones](Model/PersonItemPhones.md)
 - [PersonItemPostalAddress](Model/PersonItemPostalAddress.md)
 - [PersonRequestBody](Model/PersonRequestBody.md)
 - [PersonRequestBodyEmails](Model/PersonRequestBodyEmails.md)
 - [PersonRequestBodyPhones](Model/PersonRequestBodyPhones.md)
 - [PersonSearchItem](Model/PersonSearchItem.md)
 - [PersonSearchItemItem](Model/PersonSearchItemItem.md)
 - [PersonSearchItemItemOrganization](Model/PersonSearchItemItemOrganization.md)
 - [PersonSearchItemItemOwner](Model/PersonSearchItemItemOwner.md)
 - [PersonSearchResponse](Model/PersonSearchResponse.md)
 - [PersonSearchResponseAllOf](Model/PersonSearchResponseAllOf.md)
 - [PersonSearchResponseAllOfData](Model/PersonSearchResponseAllOfData.md)
 - [PipelineItem](Model/PipelineItem.md)
 - [PipelineRequestBody](Model/PipelineRequestBody.md)
 - [PostFollower](Model/PostFollower.md)
 - [PostFollowerAllOf](Model/PostFollowerAllOf.md)
 - [PostPatchGetActivity](Model/PostPatchGetActivity.md)
 - [PostPatchGetDeal](Model/PostPatchGetDeal.md)
 - [PostPatchGetOrganization](Model/PostPatchGetOrganization.md)
 - [PostPatchGetPerson](Model/PostPatchGetPerson.md)
 - [PostPatchGetPipeline](Model/PostPatchGetPipeline.md)
 - [PostPatchGetStage](Model/PostPatchGetStage.md)
 - [ProductFieldItem](Model/ProductFieldItem.md)
 - [ProductImageResponse](Model/ProductImageResponse.md)
 - [ProductImageResponseData](Model/ProductImageResponseData.md)
 - [ProductRequest](Model/ProductRequest.md)
 - [ProductResponse](Model/ProductResponse.md)
 - [ProductSearchItem](Model/ProductSearchItem.md)
 - [ProductSearchItemItem](Model/ProductSearchItemItem.md)
 - [ProductSearchItemItemOwner](Model/ProductSearchItemItemOwner.md)
 - [ProductSearchResponse](Model/ProductSearchResponse.md)
 - [ProductSearchResponseAllOf](Model/ProductSearchResponseAllOf.md)
 - [ProductSearchResponseAllOfData](Model/ProductSearchResponseAllOfData.md)
 - [ProductVariation](Model/ProductVariation.md)
 - [ProductVariationRequestBody](Model/ProductVariationRequestBody.md)
 - [ProductVariationResponse](Model/ProductVariationResponse.md)
 - [ProductVariationsResponse](Model/ProductVariationsResponse.md)
 - [ProductWithArrayPrices](Model/ProductWithArrayPrices.md)
 - [ProductsResponse](Model/ProductsResponse.md)
 - [StageItem](Model/StageItem.md)
 - [StageRequestBody](Model/StageRequestBody.md)
 - [UpdateAdditionalDiscountResponse](Model/UpdateAdditionalDiscountResponse.md)
 - [UpdateDealFieldRequest](Model/UpdateDealFieldRequest.md)
 - [UpdateDealProductRequestBody](Model/UpdateDealProductRequestBody.md)
 - [UpdateInstallmentResponse](Model/UpdateInstallmentResponse.md)
 - [UpdateOrganizationFieldRequest](Model/UpdateOrganizationFieldRequest.md)
 - [UpdatePersonFieldRequest](Model/UpdatePersonFieldRequest.md)
 - [UpdateProductFieldRequest](Model/UpdateProductFieldRequest.md)
 - [UpdateProductImageResponse](Model/UpdateProductImageResponse.md)
 - [UpdateProductRequestBody](Model/UpdateProductRequestBody.md)
 - [UpdateProductResponse](Model/UpdateProductResponse.md)
 - [UpsertActivityResponseData](Model/UpsertActivityResponseData.md)
 - [UpsertDealResponseData](Model/UpsertDealResponseData.md)
 - [UpsertOrganizationResponseData](Model/UpsertOrganizationResponseData.md)
 - [UpsertPersonResponseData](Model/UpsertPersonResponseData.md)
 - [UpsertPipelineResponseData](Model/UpsertPipelineResponseData.md)
 - [VisibleTo](Model/VisibleTo.md)


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
- **deal-fields:full**: Create, read, update and delete deal fields
- **product-fields:full**: Create, read, update and delete product fields
- **contact-fields:full**: Create, read, update and delete person and organization fields

