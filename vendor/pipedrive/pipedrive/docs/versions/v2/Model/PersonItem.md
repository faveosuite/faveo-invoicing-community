# # PersonItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the person | [optional]
**name** | **string** | The name of the person | [optional]
**first_name** | **string** | The first name of the person | [optional]
**last_name** | **string** | The last name of the person | [optional]
**owner_id** | **int** | The ID of the user who owns the person | [optional]
**org_id** | **int** | The ID of the organization linked to the person | [optional]
**add_time** | **string** | The creation date and time of the person | [optional]
**update_time** | **string** | The last updated date and time of the person | [optional]
**emails** | [**\Pipedrive\versions\v2\Model\PersonItemEmails[]**](PersonItemEmails.md) | The emails of the person | [optional]
**phones** | [**\Pipedrive\versions\v2\Model\PersonItemPhones[]**](PersonItemPhones.md) | The phones of the person | [optional]
**is_deleted** | **bool** | Whether the person is deleted or not | [optional]
**visible_to** | **int** | The visibility of the person | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the person | [optional]
**picture_id** | **int** | The ID of the picture associated with the person | [optional]
**postal_address** | [**\Pipedrive\versions\v2\Model\PersonItemPostalAddress**](PersonItemPostalAddress.md) |  | [optional]
**notes** | **string** | Contact sync notes of the person, maximum 10 000 characters, included if contact sync is enabled for the company | [optional]
**im** | [**\Pipedrive\versions\v2\Model\PersonItemIm[]**](PersonItemIm.md) | The instant messaging accounts of the person, included if contact sync is enabled for the company | [optional]
**birthday** | **string** | The birthday of the person, included if contact sync is enabled for the company | [optional]
**job_title** | **string** | The job title of the person, included if contact sync is enabled for the company | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
