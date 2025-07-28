# # AddUserRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**email** | **string** | The email of the user |
**access** | [**\Pipedrive\versions\v1\Model\UserAccess[]**](UserAccess.md) | The access given to the user. Each item in the array represents access to a specific app. Optionally may include either admin flag or permission set ID to specify which access to give within the app. If both are omitted, the default access for the corresponding app will be used. It requires structure as follows: &#x60;[{ app: &#39;sales&#39;, permission_set_id: &#39;62cc4d7f-4038-4352-abf3-a8c1c822b631&#39; }, { app: &#39;global&#39;, admin: true }, { app: &#39;account_settings&#39; }]&#x60; | [optional]
**active_flag** | **bool** | Whether the user is active or not. &#x60;false&#x60; &#x3D; Not activated, &#x60;true&#x60; &#x3D; Activated | [optional] [default to true]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
