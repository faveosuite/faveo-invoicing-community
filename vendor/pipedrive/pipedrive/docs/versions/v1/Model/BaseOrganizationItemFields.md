# # BaseOrganizationItemFields

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the organization | [optional]
**company_id** | **int** | The ID of the company related to the organization | [optional]
**owner_id** | [**\Pipedrive\versions\v1\Model\Owner**](Owner.md) |  | [optional]
**name** | **string** | The name of the organization | [optional]
**active_flag** | **bool** | Whether the organization is active or not | [optional]
**picture_id** | [**\Pipedrive\versions\v1\Model\PictureDataWithValue**](PictureDataWithValue.md) |  | [optional]
**country_code** | **string** | The country code of the organization | [optional]
**first_char** | **string** | The first character of the organization name | [optional]
**add_time** | **string** | The creation date and time of the organization | [optional]
**update_time** | **string** | The last updated date and time of the organization | [optional]
**visible_to** | **string** | The visibility group ID of who can see the organization | [optional]
**label** | **int** | The label assigned to the organization. When the &#x60;label&#x60; field is updated, the &#x60;label_ids&#x60; field value will be overwritten by the &#x60;label&#x60; field value. | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the organization. When the &#x60;label_ids&#x60; field is updated, the &#x60;label&#x60; field value will be set to the first value of the &#x60;label_ids&#x60; field. | [optional]
**owner_name** | **string** | The name of the organization owner | [optional]
**cc_email** | **string** | The BCC email associated with the organization | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
