# # Field

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the field. Value is &#x60;null&#x60; in case of subfields. | [optional]
**key** | **string** | The key of the field. For custom fields this is generated upon creation. | [optional]
**name** | **string** | The name of the field | [optional]
**order_nr** | **int** | The order number of the field | [optional]
**field_type** | [**FieldTypeAsString**](FieldTypeAsString.md) |  | [optional]
**add_time** | **\DateTime** | The creation time of the field | [optional]
**update_time** | **\DateTime** | The update time of the field | [optional]
**last_updated_by_user_id** | **int** | The ID of the user who created or most recently updated the field, only applicable for custom fields | [optional]
**created_by_user_id** | **int** | The ID of the user who created the field | [optional]
**active_flag** | **bool** | The active flag of the field | [optional]
**edit_flag** | **bool** | The edit flag of the field | [optional]
**index_visible_flag** | **bool** | Not used | [optional]
**details_visible_flag** | **bool** | Not used | [optional]
**add_visible_flag** | **bool** | Not used | [optional]
**important_flag** | **bool** | Not used | [optional]
**bulk_edit_allowed** | **bool** | Whether or not the field of an item can be edited in bulk | [optional]
**searchable_flag** | **bool** | Whether or not items can be searched by this field | [optional]
**filtering_allowed** | **bool** | Whether or not items can be filtered by this field | [optional]
**sortable_flag** | **bool** | Whether or not items can be sorted by this field | [optional]
**mandatory_flag** | **bool** | Whether or not the field is mandatory | [optional]
**options** | **object[]** | The options of the field. When there are no options, &#x60;null&#x60; is returned. | [optional]
**options_deleted** | **object[]** | The deleted options of the field. Only present when there is at least 1 deleted option. | [optional]
**is_subfield** | **bool** | Whether or not the field is a subfield of another field. Only present if field is subfield. | [optional]
**subfields** | **object[]** | The subfields of the field. Only present when the field has subfields. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
