# # PipelineDetails

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the pipeline | [optional]
**name** | **string** | The name of the pipeline | [optional]
**url_title** | **string** | The pipeline title displayed in the URL | [optional]
**order_nr** | **int** | Defines the order of pipelines. First order (&#x60;order_nr&#x3D;0&#x60;) is the default pipeline. | [optional]
**active** | **bool** | Whether this pipeline will be made inactive (hidden) or active | [optional]
**deal_probability** | **bool** | Whether deal probability is disabled or enabled for this pipeline | [optional]
**add_time** | **string** | The pipeline creation time. Format: YYYY-MM-DD HH:MM:SS. | [optional]
**update_time** | **string** | The pipeline update time. Format: YYYY-MM-DD HH:MM:SS. | [optional]
**selected** | **bool** | A boolean that shows if the pipeline is selected from a filter or not | [optional]
**deals_summary** | [**\Pipedrive\versions\v1\Model\DealSummary**](DealSummary.md) |  | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
