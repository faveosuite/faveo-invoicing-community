# # AddNoteRequestAllOf

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**content** | **string** | The content of the note in HTML format. Subject to sanitization on the back-end. |
**lead_id** | **string** | The ID of the lead the note will be attached to. This property is required unless one of (&#x60;deal_id/person_id/org_id&#x60;) is specified. | [optional]
**deal_id** | **int** | The ID of the deal the note will be attached to. This property is required unless one of (&#x60;lead_id/person_id/org_id&#x60;) is specified. | [optional]
**person_id** | **int** | The ID of the person this note will be attached to. This property is required unless one of (&#x60;deal_id/lead_id/org_id&#x60;) is specified. | [optional]
**org_id** | **int** | The ID of the organization this note will be attached to. This property is required unless one of (&#x60;deal_id/lead_id/person_id&#x60;) is specified. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
