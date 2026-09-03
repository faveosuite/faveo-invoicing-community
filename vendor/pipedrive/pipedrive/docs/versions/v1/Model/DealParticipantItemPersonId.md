# # DealParticipantItemPersonId

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**active_flag** | **bool** | Whether the person is active or not | [optional]
**name** | **string** | The name of the person | [optional]
**email** | [**\Pipedrive\versions\v1\Model\DealParticipantItemPersonIdEmail[]**](DealParticipantItemPersonIdEmail.md) | An email address as a string or an array of email objects related to the person. The structure of the array is as follows: &#x60;[{ \&quot;value\&quot;: \&quot;mail@example.com\&quot;, \&quot;primary\&quot;: \&quot;true\&quot;, \&quot;label\&quot;: \&quot;main\&quot; }]&#x60;. Please note that only &#x60;value&#x60; is required. | [optional]
**phone** | [**\Pipedrive\versions\v1\Model\BasePersonItemPhone[]**](BasePersonItemPhone.md) | A phone number supplied as a string or an array of phone objects related to the person. The structure of the array is as follows: &#x60;[{ \&quot;value\&quot;: \&quot;12345\&quot;, \&quot;primary\&quot;: \&quot;true\&quot;, \&quot;label\&quot;: \&quot;mobile\&quot; }]&#x60;. Please note that only &#x60;value&#x60; is required. | [optional]
**owner_id** | **int** | The ID of the owner of the person | [optional]
**company_id** | **int** | The ID of the company related to the person | [optional]
**value** | **int** | The ID of the person | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
