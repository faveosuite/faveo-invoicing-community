# # ActivityRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**subject** | **string** | The subject of the activity | [optional]
**type** | **string** | The type of the activity | [optional]
**owner_id** | **int** | The ID of the user who owns the activity | [optional]
**deal_id** | **int** | The ID of the deal linked to the activity | [optional]
**lead_id** | **string** | The ID of the lead linked to the activity | [optional]
**org_id** | **int** | The ID of the organization linked to the activity | [optional]
**project_id** | **int** | The ID of the project linked to the activity | [optional]
**due_date** | **string** | The due date of the activity | [optional]
**due_time** | **string** | The due time of the activity | [optional]
**duration** | **string** | The duration of the activity | [optional]
**busy** | **bool** | Whether the activity marks the assignee as busy or not in their calendar | [optional]
**done** | **bool** | Whether the activity is marked as done or not | [optional]
**location** | [**\Pipedrive\versions\v2\Model\ActivityRequestBodyLocation**](ActivityRequestBodyLocation.md) |  | [optional]
**participants** | [**\Pipedrive\versions\v2\Model\ActivityItemParticipants[]**](ActivityItemParticipants.md) | The participants of the activity. Use this to set the activity&#39;s person — a primary participant (&#x60;primary: true&#x60;) sets &#x60;person_id&#x60; on the activity. | [optional]
**attendees** | [**\Pipedrive\versions\v2\Model\ActivityItemAttendees[]**](ActivityItemAttendees.md) | The attendees of the activity | [optional]
**public_description** | **string** | The public description of the activity | [optional]
**priority** | **int** | The priority of the activity. Mappable to a specific string using activityFields API. | [optional]
**outcome** | **int** | The ID of the Outcome for the activity. The available Outcome values depend on the activity type and can be retrieved using the Activity Fields API. Set to &#x60;null&#x60; to clear the Outcome. | [optional]
**note** | **string** | The note of the activity | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
