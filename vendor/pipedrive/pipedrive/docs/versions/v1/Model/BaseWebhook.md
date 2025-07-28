# # BaseWebhook

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the Webhook | [optional]
**company_id** | **int** | The ID of the company related to the Webhook | [optional]
**owner_id** | **int** | The ID of the user who owns the Webhook | [optional]
**user_id** | **int** | The ID of the user related to the Webhook | [optional]
**event_action** | **string** | The Webhook action | [optional]
**event_object** | **string** | The Webhook object | [optional]
**subscription_url** | **string** | The subscription URL of the Webhook | [optional]
**is_active** | [**NumberBooleanDefault1**](NumberBooleanDefault1.md) | The Webhook&#39;s status | [optional]
**add_time** | **\DateTime** | The date when the Webhook was added | [optional]
**remove_time** | **\DateTime** | The date when the Webhook was removed (if removed) | [optional]
**type** | **string** | The type of the Webhook | [optional]
**http_auth_user** | **string** | The username of the &#x60;subscription_url&#x60; of the Webhook | [optional]
**http_auth_password** | **string** | The password of the &#x60;subscription_url&#x60; of the Webhook | [optional]
**additional_data** | **object** | Any additional data related to the Webhook | [optional]
**remove_reason** | **string** | The removal reason of the Webhook (if removed) | [optional]
**last_delivery_time** | **\DateTime** | The last delivery time of the Webhook | [optional]
**last_http_status** | **int** | The last delivery HTTP status of the Webhook | [optional]
**admin_id** | **int** | The ID of the admin of the Webhook | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
