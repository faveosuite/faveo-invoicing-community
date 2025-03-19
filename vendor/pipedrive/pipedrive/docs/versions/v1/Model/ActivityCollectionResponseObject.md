# # ActivityCollectionResponseObject

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
**id** | **int** | The ID of the activity, generated when the activity was created |
**done** | **bool** | Whether the activity is done or not |
**subject** | **string** | The subject of the activity |
**type** | **string** | The type of the activity. This is in correlation with the &#x60;key_string&#x60; parameter of ActivityTypes. |
**user_id** | **int** | The ID of the user whom the activity is assigned to |
**busy_flag** | **bool** | Marks if the activity is set as &#39;Busy&#39; or &#39;Free&#39;. If the flag is set to &#x60;true&#x60;, your customers will not be able to book that time slot through any Scheduler links. The flag can also be unset. When the value of the flag is unset (&#x60;null&#x60;), the flag defaults to &#39;Busy&#39; if it has a time set, and &#39;Free&#39; if it is an all-day event without specified time. |
**company_id** | **int** | The user&#39;s company ID |
**conference_meeting_client** | **string** | The ID of the Marketplace app, which is connected to this activity |
**conference_meeting_url** | **string** | The link to join the meeting which is associated with this activity |
**conference_meeting_id** | **string** | The meeting ID of the meeting provider (Zoom, MS Teams etc.) that is associated with this activity |
**add_time** | **string** | The creation date and time of the activity in UTC. Format: YYYY-MM-DD HH:MM:SS. |
**marked_as_done_time** | **string** | The date and time this activity was marked as done. Format: YYYY-MM-DD HH:MM:SS. |
**active_flag** | **bool** | Whether the activity is active or not |
**update_time** | **string** | The last update date and time of the activity. Format: YYYY-MM-DD HH:MM:SS. |
**update_user_id** | **int** | The ID of the user who was the last to update this activity |
**source_timezone** | **string** | The timezone the activity was created in an external calendar |
**location_subpremise** | **string** | A subfield of the location field. Indicates apartment/suite number. |
**location_street_number** | **string** | A subfield of the location field. Indicates house number. |
**location_route** | **string** | A subfield of the location field. Indicates street name. |
**location_sublocality** | **string** | A subfield of the location field. Indicates district/sublocality. |
**location_locality** | **string** | A subfield of the location field. Indicates city/town/village/locality. |
**location_admin_area_level_1** | **string** | A subfield of the location field. Indicates state/county. |
**location_admin_area_level_2** | **string** | A subfield of the location field. Indicates region. |
**location_country** | **string** | A subfield of the location field. Indicates country. |
**location_postal_code** | **string** | A subfield of the location field. Indicates ZIP/postal code. |
**location_formatted_address** | **string** | A subfield of the location field. Indicates full/combined address. |

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
