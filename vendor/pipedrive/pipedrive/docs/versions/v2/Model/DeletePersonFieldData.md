# # DeletePersonFieldData

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**field_name** | **string** | The display name/label of the field |
**field_code** | **string** | The unique identifier for the field (40-character hash for custom fields) |
**field_type** | **string** | The type of the field |
**options** | **object[]** | Array of available options for enum/set fields, null for other field types | [optional]
**subfields** | **object[]** | Array of subfields for complex field types (address, monetary), null for simple field types | [optional]
**is_custom_field** | **bool** | Whether this is a user-created custom field |
**is_optional_response_field** | **bool** | Whether this field is not returned by default in entity responses |

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
