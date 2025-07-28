# # PersonRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | The name of the person | [optional]
**owner_id** | **int** | The ID of the user who owns the person | [optional]
**org_id** | **int** | The ID of the organization linked to the person | [optional]
**add_time** | **string** | The creation date and time of the person | [optional]
**update_time** | **string** | The last updated date and time of the person | [optional]
**emails** | [**\Pipedrive\versions\v2\Model\PersonRequestBodyEmails[]**](PersonRequestBodyEmails.md) | The emails of the person | [optional]
**phones** | [**\Pipedrive\versions\v2\Model\PersonRequestBodyPhones[]**](PersonRequestBodyPhones.md) | The phones of the person | [optional]
**visible_to** | **int** | The visibility of the person | [optional]
**label_ids** | **int[]** | The IDs of labels assigned to the person | [optional]
**marketing_status** | **string** | If the person does not have a valid email address, then the marketing status is **not set** and &#x60;no_consent&#x60; is returned for the &#x60;marketing_status&#x60; value when the new person is created. If the change is forbidden, the status will remain unchanged for every call that tries to modify the marketing status. Please be aware that it is only allowed **once** to change the marketing status from an old status to a new one.&lt;table&gt;&lt;tr&gt;&lt;th&gt;Value&lt;/th&gt;&lt;th&gt;Description&lt;/th&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;no_consent&#x60;&lt;/td&gt;&lt;td&gt;The customer has not given consent to receive any marketing communications&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;unsubscribed&#x60;&lt;/td&gt;&lt;td&gt;The customers have unsubscribed from ALL marketing communications&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;subscribed&#x60;&lt;/td&gt;&lt;td&gt;The customers are subscribed and are counted towards marketing caps&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;archived&#x60;&lt;/td&gt;&lt;td&gt;The customers with &#x60;subscribed&#x60; status can be moved to &#x60;archived&#x60; to save consent, but they are not paid for&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt; | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
