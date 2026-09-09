# # TaskItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the task | [optional]
**title** | **string** | The title of the task | [optional]
**creator_id** | **int** | The ID of the user who created the task | [optional]
**description** | **string** | The description of the task | [optional]
**project_id** | **int** | The ID of the project this task is associated with | [optional]
**is_done** | **bool** | Whether the task is done or not. | [optional]
**is_milestone** | **bool** | Whether the task is a milestone or not. | [optional]
**due_date** | **\DateTime** | The due date of the task. Format: YYYY-MM-DD. | [optional]
**start_date** | **\DateTime** | The start date of the task. Format: YYYY-MM-DD. | [optional]
**parent_task_id** | **int** | The ID of the parent task. If &#x60;null&#x60;, the task is a root-level task. | [optional]
**assignee_ids** | **int[]** | The IDs of users assigned to the task | [optional]
**priority** | **int** | The priority of the task | [optional]
**add_time** | **string** | The creation date and time of the task in ISO 8601 format | [optional]
**update_time** | **string** | The update date and time of the task in ISO 8601 format | [optional]
**marked_as_done_time** | **string** | The date and time the task was marked as done in ISO 8601 format | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
