# # TaskPatchRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**title** | **string** | The title of the task | [optional]
**project_id** | **int** | The ID of the project this task is associated with | [optional]
**parent_task_id** | **int** | The ID of the parent task. Cannot be the ID of a task that is already a subtask. | [optional]
**description** | **string** | The description of the task | [optional]
**done** | **int** | Whether the task is done or not. &#x60;0&#x60; &#x3D; Not done, &#x60;1&#x60; &#x3D; Done. | [optional]
**milestone** | **int** | Whether the task is a milestone or not. &#x60;0&#x60; &#x3D; Not a milestone, &#x60;1&#x60; &#x3D; Milestone. | [optional]
**due_date** | **\DateTime** | The due date of the task. Format: YYYY-MM-DD. | [optional]
**start_date** | **\DateTime** | The start date of the task. Format: YYYY-MM-DD. | [optional]
**assignee_id** | **int** | The ID of the user assigned to the task. When set, the &#x60;assignee_ids&#x60; field will be overwritten with this value. | [optional]
**assignee_ids** | **int[]** | The IDs of users assigned to the task. When set, the &#x60;assignee_id&#x60; field will be set to the first value in this array, or &#x60;null&#x60; if empty. | [optional]
**priority** | **int** | The priority of the task | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
