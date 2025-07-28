# # PersonsCollectionResponseObject

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the person |
**active_flag** | **bool** | Whether the person is active or not |
**owner_id** | **int** | The ID of the owner related to the person |
**org_id** | **int** | The ID of the organization related to the person |
**name** | **string** | The name of the person |
**email** | [**\Pipedrive\versions\v1\Model\BasicPersonEmail[]**](BasicPersonEmail.md) | An email address as a string or an array of email objects related to the person. The structure of the array is as follows: &#x60;[{ \&quot;value\&quot;: \&quot;mail@example.com\&quot;, \&quot;primary\&quot;: \&quot;true\&quot;, \&quot;label\&quot;: \&quot;main\&quot; }]&#x60;. Please note that only &#x60;value&#x60; is required. |
**phone** | [**\Pipedrive\versions\v1\Model\BasePersonItemPhone[]**](BasePersonItemPhone.md) | A phone number supplied as a string or an array of phone objects related to the person. The structure of the array is as follows: &#x60;[{ \&quot;value\&quot;: \&quot;12345\&quot;, \&quot;primary\&quot;: \&quot;true\&quot;, \&quot;label\&quot;: \&quot;mobile\&quot; }]&#x60;. Please note that only &#x60;value&#x60; is required. |
**update_time** | **string** | The last updated date and time of the person. Format: YYYY-MM-DD HH:MM:SS |
**delete_time** | **string** | The date and time this person was deleted. Format: YYYY-MM-DD HH:MM:SS |
**add_time** | **string** | The date and time when the person was added/created. Format: YYYY-MM-DD HH:MM:SS |
**visible_to** | **string** | The visibility group ID of who can see the person |
**picture_id** | **int** | The ID of the picture associated with the item |
**label** | **int** | The label assigned to the person. When the &#x60;label&#x60; field is updated, the &#x60;label_ids&#x60; field value will be overwritten by the &#x60;label&#x60; field value. |
**label_ids** | **int[]** | The IDs of labels assigned to the person. When the &#x60;label_ids&#x60; field is updated, the &#x60;label&#x60; field value will be set to the first value of the &#x60;label_ids&#x60; field. |
**cc_email** | **string** | The BCC email associated with the person |

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
