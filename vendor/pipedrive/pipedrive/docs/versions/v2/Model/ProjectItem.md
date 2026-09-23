# # ProjectItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the project | [optional]
**title** | **string** | The title of the project | [optional]
**description** | **string** | The description of the project | [optional]
**status** | **string** | The status of the project | [optional]
**board_id** | **int** | The ID of the board this project is associated with | [optional]
**phase_id** | **int** | The ID of the phase this project is associated with | [optional]
**owner_id** | **int** | The ID of the user who owns the project | [optional]
**start_date** | **\DateTime** | The start date of the project. Format: YYYY-MM-DD | [optional]
**end_date** | **\DateTime** | The end date of the project. Format: YYYY-MM-DD | [optional]
**deal_ids** | **int[]** | An array of IDs of the deals this project is associated with | [optional]
**person_ids** | **int[]** | An array of IDs of the persons this project is associated with | [optional]
**org_ids** | **int[]** | An array of IDs of the organizations this project is associated with | [optional]
**label_ids** | **int[]** | An array of IDs of the labels this project has | [optional]
**health_status** | **int** | The health status of the project | [optional]
**add_time** | **string** | The creation date and time of the project in ISO 8601 format | [optional]
**update_time** | **string** | The last updated date and time of the project in ISO 8601 format | [optional]
**status_change_time** | **string** | The date and time of the last status change of the project in ISO 8601 format | [optional]
**archive_time** | **string** | The date and time the project was archived in ISO 8601 format. If not archived, this field is null. | [optional]
**custom_fields** | **array<string,object>** | An object where each key represents a custom field. All custom fields are referenced as randomly generated 40-character hashes. To clear a custom field value, set it to &#x60;null&#x60;. For multi-option fields (field type &#x60;set&#x60;), use &#x60;null&#x60; to clear the selection — sending an empty array &#x60;[]&#x60; is not supported and will result in a validation error. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
