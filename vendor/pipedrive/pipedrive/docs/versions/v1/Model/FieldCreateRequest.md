# # FieldCreateRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | The name of the field |
**options** | **object[]** | When &#x60;field_type&#x60; is either set or enum, possible options must be supplied as a JSON-encoded sequential array of objects. Example: &#x60;[{\&quot;label\&quot;:\&quot;New Item\&quot;}]&#x60; | [optional]
**add_visible_flag** | **bool** | Whether the field is available in the &#39;add new&#39; modal or not (both in the web and mobile app) | [optional] [default to true]
**field_type** | [**\Pipedrive\versions\v1\Model\FieldTypeAsString**](FieldTypeAsString.md) |  |

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
