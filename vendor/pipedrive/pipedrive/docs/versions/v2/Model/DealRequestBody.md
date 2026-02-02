# # DealRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**title** | **string** | The title of the deal | [optional]
**owner_id** | **int** | The ID of the user who owns the deal | [optional]
**person_id** | **int** | The ID of the person linked to the deal | [optional]
**org_id** | **int** | The ID of the organization linked to the deal | [optional]
**pipeline_id** | **int** | The ID of the pipeline associated with the deal | [optional]
**stage_id** | **int** | The ID of the deal stage | [optional]
**value** | **float** | The value of the deal | [optional]
**currency** | **string** | The currency associated with the deal | [optional]
**is_deleted** | **bool** | Whether the deal is deleted or not | [optional]
**is_archived** | **bool** | Whether the deal is archived or not | [optional]
**archive_time** | **string** | The optional date and time of archiving the deal in UTC. Format: YYYY-MM-DD HH:MM:SS. If omitted and &#x60;is_archived&#x60; is true, it will be set to the current date and time. | [optional]
**status** | **string** | The status of the deal | [optional]
**probability** | **float** | The success probability percentage of the deal | [optional]
**lost_reason** | **string** | The reason for losing the deal. Can only be set if deal status is lost. | [optional]
**visible_to** | **int** | The visibility of the deal | [optional]
**close_time** | **string** | The date and time of closing the deal. Can only be set if deal status is won or lost. | [optional]
**won_time** | **string** | The date and time of changing the deal status as won. Can only be set if deal status is won. | [optional]
**lost_time** | **string** | The date and time of changing the deal status as lost. Can only be set if deal status is lost. | [optional]
**expected_close_date** | **\DateTime** | The expected close date of the deal | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the deal | [optional]
**custom_fields** | **array<string,object>** | An object where each key represents a custom field. All custom fields are referenced as randomly generated 40-character hashes | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
