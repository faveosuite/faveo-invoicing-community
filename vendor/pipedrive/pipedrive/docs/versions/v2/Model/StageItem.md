# # StageItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the stage | [optional]
**order_nr** | **int** | Defines the order of the stage | [optional]
**name** | **string** | The name of the stage | [optional]
**is_deleted** | **bool** | Whether the stage is marked as deleted or not | [optional]
**deal_probability** | **int** | The success probability percentage of the deal. Used/shown when the deal weighted values are used. | [optional]
**pipeline_id** | **int** | The ID of the pipeline to add the stage to | [optional]
**is_deal_rot_enabled** | **bool** | Whether deals in this stage can become rotten | [optional]
**days_to_rotten** | **int** | The number of days the deals not updated in this stage would become rotten. Applies only if the &#x60;is_deal_rot_enabled&#x60; is set. | [optional]
**add_time** | **string** | The stage creation time | [optional]
**update_time** | **string** | The stage update time | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
