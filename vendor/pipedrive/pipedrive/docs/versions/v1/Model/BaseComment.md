# # BaseComment

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**uuid** | **string** | The ID of the note | [optional]
**active_flag** | **bool** | Whether the note is active or deleted | [optional]
**add_time** | **string** | The creation date and time of the note | [optional]
**update_time** | **string** | The creation date and time of the note | [optional]
**content** | **string** | The content of the note in HTML format. Subject to sanitization on the back-end. | [optional]
**object_id** | **string** | The ID of the object that the comment is attached to, will be the id of the note | [optional]
**object_type** | **string** | The type of object that the comment is attached to, will be \&quot;note\&quot; | [optional]
**user_id** | **int** | The ID of the user who created the comment | [optional]
**updater_id** | **int** | The ID of the user who last updated the comment | [optional]
**company_id** | **int** | The ID of the company | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
