# # OrganizationRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | The name of the organization | [optional]
**owner_id** | **int** | The ID of the user who owns the organization | [optional]
**add_time** | **string** | The creation date and time of the organization | [optional]
**update_time** | **string** | The last updated date and time of the organization | [optional]
**visible_to** | **int** | The visibility of the organization | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the organization | [optional]
**address** | [**\Pipedrive\versions\v2\Model\OrganizationItemAddress**](OrganizationItemAddress.md) |  | [optional]
**custom_fields** | **array<string,object>** | An object where each key represents a custom field. All custom fields are referenced as randomly generated 40-character hashes | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
