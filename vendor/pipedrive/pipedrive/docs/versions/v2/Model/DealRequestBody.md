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
**add_time** | **string** | The creation date and time of the deal | [optional]
**update_time** | **string** | The last updated date and time of the deal | [optional]
**stage_change_time** | **string** | The last updated date and time of the deal stage | [optional]
**is_deleted** | **bool** | Whether the deal is deleted or not | [optional]
**status** | **string** | The status of the deal | [optional]
**probability** | **float** | The success probability percentage of the deal | [optional]
**lost_reason** | **string** | The reason for losing the deal. Can only be set if deal status is lost. | [optional]
**visible_to** | **int** | The visibility of the deal | [optional]
**close_time** | **string** | The date and time of closing the deal. Can only be set if deal status is won or lost. | [optional]
**won_time** | **string** | The date and time of changing the deal status as won. Can only be set if deal status is won. | [optional]
**lost_time** | **string** | The date and time of changing the deal status as lost. Can only be set if deal status is lost. | [optional]
**expected_close_date** | **\DateTime** | The expected close date of the deal | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the deal | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
