# # DealItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the deal | [optional]
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
**lost_reason** | **string** | The reason for losing the deal | [optional]
**visible_to** | **int** | The visibility of the deal | [optional]
**close_time** | **string** | The date and time of closing the deal | [optional]
**won_time** | **string** | The date and time of changing the deal status as won | [optional]
**lost_time** | **string** | The date and time of changing the deal status as lost | [optional]
**expected_close_date** | **\DateTime** | The expected close date of the deal | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the deal | [optional]
**origin** | **string** | The way this Deal was created. &#x60;origin&#x60; field is set by Pipedrive when Deal is created and cannot be changed. | [optional]
**origin_id** | **string** | The optional ID to further distinguish the origin of the deal - e.g. Which API integration created this Deal. | [optional]
**channel** | **int** | The ID of your Marketing channel this Deal was created from. Recognized Marketing channels can be configured in your &lt;a href&#x3D;\&quot;https://app.pipedrive.com/settings/fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;Company settings&lt;/a&gt;. | [optional]
**channel_id** | **string** | The optional ID to further distinguish the Marketing channel. | [optional]
**arr** | **float** | Only available in Advanced and above plans  The Annual Recurring Revenue of the deal  Null if there are no products attached to the deal | [optional]
**mrr** | **float** | Only available in Advanced and above plans  The Monthly Recurring Revenue of the deal  Null if there are no products attached to the deal | [optional]
**acv** | **float** | Only available in Advanced and above plans  The Annual Contract Value of the deal  Null if there are no products attached to the deal | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
