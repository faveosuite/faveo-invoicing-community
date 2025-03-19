# # ResponseCallLogObject

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**user_id** | **int** | The ID of the owner of the call log. Please note that a user without account settings access cannot create call logs for other users. | [optional]
**activity_id** | **int** | If specified, this activity will be converted into a call log, with the information provided. When this field is used, you don&#39;t need to specify &#x60;deal_id&#x60;, &#x60;person_id&#x60; or &#x60;org_id&#x60;, as they will be ignored in favor of the values already available in the activity. The &#x60;activity_id&#x60; must refer to a &#x60;call&#x60; type activity. | [optional]
**subject** | **string** | The name of the activity this call is attached to | [optional]
**duration** | **string** | The duration of the call in seconds | [optional]
**outcome** | **string** | Describes the outcome of the call |
**from_phone_number** | **string** | The number that made the call | [optional]
**to_phone_number** | **string** | The number called |
**start_time** | **\DateTime** | The date and time of the start of the call in UTC. Format: YYYY-MM-DD HH:MM:SS. |
**end_time** | **\DateTime** | The date and time of the end of the call in UTC. Format: YYYY-MM-DD HH:MM:SS. |
**person_id** | **int** | The ID of the person this call is associated with | [optional]
**org_id** | **int** | The ID of the organization this call is associated with | [optional]
**deal_id** | **int** | The ID of the deal this call is associated with. A call log can be associated with either a deal or a lead, but not both at once. | [optional]
**lead_id** | **string** | The ID of the lead in the UUID format this call is associated with. A call log can be associated with either a deal or a lead, but not both at once. | [optional]
**note** | **string** | The note for the call log in HTML format | [optional]
**id** | **string** | The call log ID, generated when the call log was created |
**has_recording** | **bool** | If the call log has an audio recording attached, the value should be true |
**company_id** | **int** | The company ID of the owner of the call log |

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
