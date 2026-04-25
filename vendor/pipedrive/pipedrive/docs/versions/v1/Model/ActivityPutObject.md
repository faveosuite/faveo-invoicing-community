# # ActivityPutObject

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**due_date** | **\DateTime** | The due date of the activity. Format: YYYY-MM-DD | [optional]
**due_time** | **string** | The due time of the activity in UTC. Format: HH:MM | [optional]
**duration** | **string** | The duration of the activity. Format: HH:MM | [optional]
**deal_id** | **int** | The ID of the deal this activity is associated with | [optional]
**lead_id** | **string** | The ID of the lead in the UUID format this activity is associated with | [optional]
**person_id** | **int** | The ID of the person this activity is associated with | [optional]
**project_id** | **int** | The ID of the project this activity is associated with | [optional]
**org_id** | **int** | The ID of the organization this activity is associated with | [optional]
**location** | **string** | The address of the activity. | [optional]
**public_description** | **string** | Additional details about the activity that is synced to your external calendar. Unlike the note added to the activity, the description is publicly visible to any guests added to the activity. | [optional]
**note** | **string** | The note of the activity (HTML format) | [optional]
**subject** | **string** | The subject of the activity | [optional]
**type** | **string** | The type of the activity. This is in correlation with the &#x60;key_string&#x60; parameter of ActivityTypes. | [optional]
**user_id** | **int** | The ID of the user whom the activity is assigned to | [optional]
**participants** | **object[]** | List of multiple persons (participants) this activity is associated with. It requires a structure as follows: &#x60;[{\&quot;person_id\&quot;:1,\&quot;primary_flag\&quot;:true}]&#x60; | [optional]
**busy_flag** | **bool** | Set the activity as &#39;Busy&#39; or &#39;Free&#39;. If the flag is set to &#x60;true&#x60;, your customers will not be able to book that time slot through any Scheduler links. The flag can also be unset by never setting it or overriding it with &#x60;null&#x60;. When the value of the flag is unset (&#x60;null&#x60;), the flag defaults to &#39;Busy&#39; if it has a time set, and &#39;Free&#39; if it is an all-day event without specified time. | [optional]
**attendees** | **object[]** | The attendees of the activity. This can be either your existing Pipedrive contacts or an external email address. It requires a structure as follows: &#x60;[{\&quot;email_address\&quot;:\&quot;mail@example.org\&quot;}]&#x60; or &#x60;[{\&quot;person_id\&quot;:1, \&quot;email_address\&quot;:\&quot;mail@example.org\&quot;}]&#x60; | [optional]
**done** | [**NumberBoolean**](NumberBoolean.md) | Whether the activity is done or not. 0 &#x3D; Not done, 1 &#x3D; Done | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
