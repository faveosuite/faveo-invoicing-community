# # GetProductField

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | The name of the field |
**options** | **object[]** | When &#x60;field_type&#x60; is either &#x60;set&#x60; or &#x60;enum&#x60;, possible options must be supplied as a JSON-encoded sequential array, for example:&lt;/br&gt;&#x60;[{\&quot;label\&quot;:\&quot;red\&quot;}, {\&quot;label\&quot;:\&quot;blue\&quot;}, {\&quot;label\&quot;:\&quot;lilac\&quot;}]&#x60; | [optional]
**field_type** | [**\Pipedrive\versions\v1\Model\InternalFieldType**](InternalFieldType.md) |  |
**id** | **int** | The ID of the product field | [optional]
**key** | **string** | The key of the product field | [optional]
**order_nr** | **int** | The position (index) of the product field in the detail view | [optional]
**add_time** | **string** | The product field creation time. Format: YYYY-MM-DD HH:MM:SS | [optional]
**update_time** | **string** | The product field last update time. Format: YYYY-MM-DD HH:MM:SS | [optional]
**last_updated_by_user_id** | **int** | The ID of the last user to update the product field | [optional]
**created_by_user_id** | **int** | The ID of the user who created the product field | [optional]
**active_flag** | **bool** | Whether or not the product field is currently active | [optional]
**edit_flag** | **bool** | Whether or not the product field name and metadata is editable | [optional]
**add_visible_flag** | **bool** | Whether or not the product field is visible in the Add Product Modal | [optional]
**important_flag** | **bool** | Whether or not the product field is marked as important | [optional]
**bulk_edit_allowed** | **bool** | Whether or not the product field data can be edited | [optional]
**searchable_flag** | **bool** | Whether or not the product field is searchable | [optional]
**filtering_allowed** | **bool** | Whether or not the product field value can be used when filtering searches | [optional]
**sortable_flag** | **bool** | Whether or not the product field is sortable | [optional]
**mandatory_flag** | **bool** | Whether or not the product field is mandatory when creating products | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
