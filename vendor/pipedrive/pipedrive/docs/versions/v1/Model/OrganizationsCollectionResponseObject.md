# # OrganizationsCollectionResponseObject

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**address** | **string** | The full address of the organization | [optional]
**address_subpremise** | **string** | The sub-premise of the organization location | [optional]
**address_street_number** | **string** | The street number of the organization location | [optional]
**address_route** | **string** | The route of the organization location | [optional]
**address_sublocality** | **string** | The sub-locality of the organization location | [optional]
**address_locality** | **string** | The locality of the organization location | [optional]
**address_admin_area_level_1** | **string** | The level 1 admin area of the organization location | [optional]
**address_admin_area_level_2** | **string** | The level 2 admin area of the organization location | [optional]
**address_country** | **string** | The country of the organization location | [optional]
**address_postal_code** | **string** | The postal code of the organization location | [optional]
**address_formatted_address** | **string** | The formatted organization location | [optional]
**id** | **int** | The ID of the organization | [optional]
**active_flag** | **bool** | Whether the organization is active or not | [optional]
**owner_id** | **int** | The ID of the owner | [optional]
**name** | **string** | The name of the organization | [optional]
**update_time** | **string** | The last updated date and time of the organization. Format: YYYY-MM-DD HH:MM:SS | [optional]
**delete_time** | **string** | The date and time this organization was deleted. Format: YYYY-MM-DD HH:MM:SS | [optional]
**add_time** | **string** | The date and time when the organization was added/created. Format: YYYY-MM-DD HH:MM:SS | [optional]
**visible_to** | **string** | The visibility group ID of who can see the organization | [optional]
**label** | **int** | The label assigned to the organization. When the &#x60;label&#x60; field is updated, the &#x60;label_ids&#x60; field value will be overwritten by the &#x60;label&#x60; field value. | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the organization. When the &#x60;label_ids&#x60; field is updated, the &#x60;label&#x60; field value will be set to the first value of the &#x60;label_ids&#x60; field. | [optional]
**cc_email** | **string** | The BCC email associated with the organization | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
