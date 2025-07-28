# # AddOrganizationRelationshipRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**org_id** | **int** | The ID of the base organization for the returned calculated values | [optional]
**type** | **string** | The type of organization relationship |
**rel_owner_org_id** | **int** | The owner of the relationship. If type is &#x60;parent&#x60;, then the owner is the parent and the linked organization is the daughter. |
**rel_linked_org_id** | **int** | The linked organization in the relationship. If type is &#x60;parent&#x60;, then the linked organization is the daughter. |

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
