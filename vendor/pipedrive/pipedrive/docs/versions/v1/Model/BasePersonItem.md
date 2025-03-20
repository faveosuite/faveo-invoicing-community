# # BasePersonItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the person | [optional]
**company_id** | **int** | The ID of the company related to the person | [optional]
**active_flag** | **bool** | Whether the person is active or not | [optional]
**phone** | [**\Pipedrive\versions\v1\Model\BasePersonItemPhone[]**](BasePersonItemPhone.md) | A phone number supplied as a string or an array of phone objects related to the person. The structure of the array is as follows: &#x60;[{ \&quot;value\&quot;: \&quot;12345\&quot;, \&quot;primary\&quot;: \&quot;true\&quot;, \&quot;label\&quot;: \&quot;mobile\&quot; }]&#x60;. Please note that only &#x60;value&#x60; is required. | [optional]
**email** | [**\Pipedrive\versions\v1\Model\BasePersonItemEmail[]**](BasePersonItemEmail.md) | An email address as a string or an array of email objects related to the person. The structure of the array is as follows: &#x60;[{ \&quot;value\&quot;: \&quot;mail@example.com\&quot;, \&quot;primary\&quot;: \&quot;true\&quot;, \&quot;label\&quot;: \&quot;main\&quot; } ]&#x60;. Please note that only &#x60;value&#x60; is required. | [optional]
**first_char** | **string** | The first letter of the name of the person | [optional]
**add_time** | **string** | The date and time when the person was added/created. Format: YYYY-MM-DD HH:MM:SS | [optional]
**update_time** | **string** | The last updated date and time of the person. Format: YYYY-MM-DD HH:MM:SS | [optional]
**visible_to** | **string** | The visibility group ID of who can see the person | [optional]
**picture_id** | [**\Pipedrive\versions\v1\Model\PictureDataWithID**](PictureDataWithID.md) |  | [optional]
**label** | **int** | The label assigned to the person. When the &#x60;label&#x60; field is updated, the &#x60;label_ids&#x60; field value will be overwritten by the &#x60;label&#x60; field value. | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the person. When the &#x60;label_ids&#x60; field is updated, the &#x60;label&#x60; field value will be set to the first value of the &#x60;label_ids&#x60; field. | [optional]
**org_name** | **string** | The name of the organization associated with the person | [optional]
**owner_name** | **string** | The name of the owner associated with the person | [optional]
**cc_email** | **string** | The BCC email associated with the person | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
