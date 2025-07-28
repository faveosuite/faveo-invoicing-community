# # StageRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | The name of the stage | [optional]
**pipeline_id** | **int** | The ID of the pipeline to add stage to | [optional]
**deal_probability** | **int** | The success probability percentage of the deal. Used/shown when deal weighted values are used. | [optional]
**is_deal_rot_enabled** | **bool** | Whether deals in this stage can become rotten | [optional]
**days_to_rotten** | **int** | The number of days the deals not updated in this stage would become rotten. Applies only if the &#x60;is_deal_rot_enabled&#x60; is set. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
