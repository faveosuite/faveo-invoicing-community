# # NewGoal

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**title** | **string** | The title of the goal | [optional]
**assignee** | **object** | Who this goal is assigned to. It requires the following JSON structure: &#x60;{ \&quot;id\&quot;: \&quot;1\&quot;, \&quot;type\&quot;: \&quot;person\&quot; }&#x60;. &#x60;type&#x60; can be either &#x60;person&#x60;, &#x60;company&#x60; or &#x60;team&#x60;. ID of the assignee person, company or team. |
**type** | **object** | The type of the goal. It requires the following JSON structure: &#x60;{ \&quot;name\&quot;: \&quot;deals_started\&quot;, \&quot;params\&quot;: { \&quot;pipeline_id\&quot;: [1, 2], \&quot;activity_type_id\&quot;: [9] } }&#x60;. Type can be one of: &#x60;deals_won&#x60;, &#x60;deals_progressed&#x60;, &#x60;activities_completed&#x60;, &#x60;activities_added&#x60;, &#x60;deals_started&#x60; or &#x60;revenue_forecast&#x60;. &#x60;params&#x60; can include &#x60;pipeline_id&#x60;, &#x60;stage_id&#x60; or &#x60;activity_type_id&#x60;. &#x60;stage_id&#x60; is related to only &#x60;deals_progressed&#x60; type of goals and &#x60;activity_type_id&#x60; to &#x60;activities_completed&#x60; or &#x60;activities_added&#x60; types of goals. The &#x60;pipeline_id&#x60; and &#x60;activity_type_id&#x60; need to be given as an array of integers. To track the goal in all pipelines, set &#x60;pipeline_id&#x60; as &#x60;null&#x60; and similarly, to track the goal for all activities, set &#x60;activity_type_id&#x60; as &#x60;null&#x60;.” |
**expected_outcome** | **object** | The expected outcome of the goal. Expected outcome can be tracked either by &#x60;quantity&#x60; or by &#x60;sum&#x60;. It requires the following JSON structure: &#x60;{ \&quot;target\&quot;: \&quot;50\&quot;, \&quot;tracking_metric\&quot;: \&quot;quantity\&quot; }&#x60; or &#x60;{ \&quot;target\&quot;: \&quot;50\&quot;, \&quot;tracking_metric\&quot;: \&quot;sum\&quot;, \&quot;currency_id\&quot;: 1 }&#x60;. &#x60;currency_id&#x60; should only be added to &#x60;sum&#x60; type of goals. |
**duration** | **object** | The date when the goal starts and ends. It requires the following JSON structure: &#x60;{ \&quot;start\&quot;: \&quot;2019-01-01\&quot;, \&quot;end\&quot;: \&quot;2022-12-31\&quot; }&#x60;. Date in format of YYYY-MM-DD. \&quot;end\&quot; can be set to &#x60;null&#x60; for an infinite, open-ended goal. |
**interval** | **string** | The interval of the goal |

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
