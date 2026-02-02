# # AddWebhookRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**subscription_url** | **string** | A full, valid, publicly accessible URL which determines where to send the notifications. Please note that you cannot use Pipedrive API endpoints as the &#x60;subscription_url&#x60; and the chosen URL must not redirect to another link. |
**event_action** | **string** | The type of action to receive notifications about. Wildcard will match all supported actions. |
**event_object** | **string** | The type of object to receive notifications about. Wildcard will match all supported objects. |
**name** | **string** | The webhook&#39;s name |
**user_id** | **int** | The ID of the user that this webhook will be authorized with. You have the option to use a different user&#39;s &#x60;user_id&#x60;. If it is not set, the current user&#39;s &#x60;user_id&#x60; will be used. As each webhook event is checked against a user&#39;s permissions, the webhook will only be sent if the user has access to the specified object(s). If you want to receive notifications for all events, please use a top-level admin user’s &#x60;user_id&#x60;. | [optional]
**http_auth_user** | **string** | The HTTP basic auth username of the subscription URL endpoint (if required) | [optional]
**http_auth_password** | **string** | The HTTP basic auth password of the subscription URL endpoint (if required) | [optional]
**version** | **string** | The webhook&#39;s version. NB! Webhooks v2 is the default from March 17th, 2025. See &lt;a href&#x3D;\&quot;https://developers.pipedrive.com/changelog/post/breaking-change-webhooks-v2-will-become-the-new-default-version\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;this Changelog post&lt;/a&gt; for more details. | [optional] [default to '2.0']

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
