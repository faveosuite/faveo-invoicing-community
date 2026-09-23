# # ProjectChangelogResponseData

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**change_source** | **string** | The source of change, for example &#39;app&#39;, &#39;mobile&#39;, &#39;api&#39;, etc. | [optional]
**change_source_user_agent** | **string** | The user agent from which the change was made | [optional]
**time** | **string** | The date and time of the change in ISO 8601 format | [optional]
**new_values** | **array<string,object>** | A map of field keys to their new values after the change | [optional]
**old_values** | **array<string,object>** | A map of field keys to their previous values before the change | [optional]
**actor_user_id** | **int** | The ID of the user who made the change | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
