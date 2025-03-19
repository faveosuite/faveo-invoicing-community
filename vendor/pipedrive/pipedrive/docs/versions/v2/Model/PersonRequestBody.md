# # PersonRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | The name of the person | [optional]
**owner_id** | **int** | The ID of the user who owns the person | [optional]
**org_id** | **int** | The ID of the organization linked to the person | [optional]
**add_time** | **string** | The creation date and time of the person | [optional]
**update_time** | **string** | The last updated date and time of the person | [optional]
**emails** | [**\Pipedrive\versions\v2\Model\PersonRequestBodyEmails[]**](PersonRequestBodyEmails.md) | The emails of the person | [optional]
**phones** | [**\Pipedrive\versions\v2\Model\PersonRequestBodyPhones[]**](PersonRequestBodyPhones.md) | The phones of the person | [optional]
**visible_to** | **int** | The visibility of the person | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the person | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
