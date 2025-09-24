# # FilterConditionItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**object** | **string** | The type of entity the condition applies to (e.g. \&quot;deal\&quot;, \&quot;person\&quot;) | [optional]
**field_id** | **string** | The ID of the field | [optional]
**operator** | **string** | The operator used in the condition (e.g. \&quot;&#x3D;\&quot;, \&quot;IS NOT NULL\&quot;) | [optional]
**value** | **string** | The value of the condition | [optional]
**extra_value** | **string** | An extra value for conditions that require two values | [optional]
**json_value_flag** | **bool** | Whether the value is JSON-encoded | [optional]
**field_code** | **string** | The code name of the field. Present when &#x60;include_field_code&#x3D;true&#x60; is passed as a query parameter; &#x60;null&#x60; if the field code cannot be resolved | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
