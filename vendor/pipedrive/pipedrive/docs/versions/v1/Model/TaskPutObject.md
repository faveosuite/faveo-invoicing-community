# # TaskPutObject

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**title** | **string** | The title of the task | [optional]
**project_id** | **float** | The ID of the project this task is associated with | [optional]
**description** | **string** | The description of the task | [optional]
**parent_task_id** | **float** | The ID of a parent task. Can not be ID of a task which is already a subtask. | [optional]
**assignee_id** | **float** | The ID of the user assigned to the task. When the &#x60;assignee_id&#x60; field is updated, the &#x60;assignee_ids&#x60; field value will be overwritten by the &#x60;assignee_id&#x60; field value. | [optional]
**assignee_ids** | **float[]** | The IDs of users assigned to the task. When the &#x60;assignee_ids&#x60; field is updated, the &#x60;assignee_id&#x60; field value will be set to the first value of the &#x60;assignee_ids&#x60; field, or &#x60;null&#x60; if the list is empty. | [optional]
**done** | [**NumberBoolean**](NumberBoolean.md) | Whether the task is done or not. 0 &#x3D; Not done, 1 &#x3D; Done. | [optional]
**due_date** | **\DateTime** | The due date of the task. Format: YYYY-MM-DD. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
