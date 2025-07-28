# # BaseStage

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the stage | [optional]
**order_nr** | **int** | Defines the order of the stage | [optional]
**name** | **string** | The name of the stage | [optional]
**active_flag** | **bool** | Whether the stage is active or deleted | [optional]
**deal_probability** | **int** | The success probability percentage of the deal. Used/shown when the deal weighted values are used. | [optional]
**pipeline_id** | **int** | The ID of the pipeline to add the stage to | [optional]
**rotten_flag** | **bool** | Whether deals in this stage can become rotten | [optional]
**rotten_days** | **int** | The number of days the deals not updated in this stage would become rotten. Applies only if the &#x60;rotten_flag&#x60; is set. | [optional]
**add_time** | **string** | The stage creation time. Format: YYYY-MM-DD HH:MM:SS. | [optional]
**update_time** | **string** | The stage update time. Format: YYYY-MM-DD HH:MM:SS. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
