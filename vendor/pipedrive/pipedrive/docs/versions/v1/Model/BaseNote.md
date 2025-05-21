# # BaseNote

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the note | [optional]
**active_flag** | **bool** | Whether the note is active or deleted | [optional]
**add_time** | **string** | The creation date and time of the note | [optional]
**content** | **string** | The content of the note in HTML format. Subject to sanitization on the back-end. | [optional]
**deal** | [**\Pipedrive\versions\v1\Model\BaseNoteDealTitle**](BaseNoteDealTitle.md) |  | [optional]
**lead_id** | **string** | The ID of the lead the note is attached to | [optional]
**deal_id** | **int** | The ID of the deal the note is attached to | [optional]
**last_update_user_id** | **int** | The ID of the user who last updated the note | [optional]
**org_id** | **int** | The ID of the organization the note is attached to | [optional]
**organization** | [**\Pipedrive\versions\v1\Model\BaseNoteOrganization**](BaseNoteOrganization.md) |  | [optional]
**person** | [**\Pipedrive\versions\v1\Model\BaseNotePerson**](BaseNotePerson.md) |  | [optional]
**person_id** | **int** | The ID of the person the note is attached to | [optional]
**project_id** | **int** | The ID of the project the note is attached to | [optional]
**project** | [**\Pipedrive\versions\v1\Model\BaseNoteProject**](BaseNoteProject.md) |  | [optional]
**pinned_to_deal_flag** | **bool** | If true, the results are filtered by note to deal pinning state | [optional]
**pinned_to_organization_flag** | **bool** | If true, the results are filtered by note to organization pinning state | [optional]
**pinned_to_person_flag** | **bool** | If true, the results are filtered by note to person pinning state | [optional]
**pinned_to_project_flag** | **bool** | If true, the results are filtered by note to project pinning state | [optional]
**update_time** | **string** | The last updated date and time of the note | [optional]
**user** | [**\Pipedrive\versions\v1\Model\NoteCreatorUser**](NoteCreatorUser.md) |  | [optional]
**user_id** | **int** | The ID of the note creator | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
