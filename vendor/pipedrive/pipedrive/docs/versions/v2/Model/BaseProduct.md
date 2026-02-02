# # BaseProduct

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **float** | The ID of the product | [optional]
**name** | **string** | The name of the product | [optional]
**code** | **string** | The product code | [optional]
**unit** | **string** | The unit in which this product is sold | [optional]
**tax** | **float** | The tax percentage | [optional] [default to 0]
**is_deleted** | **bool** | Whether this product will be made marked as deleted or not | [optional] [default to false]
**is_linkable** | **bool** | Whether this product can be added to a deal or not | [optional] [default to true]
**visible_to** | [**VisibleTo**](VisibleTo.md) | Visibility of the product | [optional]
**owner_id** | **int** | Information about the Pipedrive user who owns the product | [optional]
**custom_fields** | **array<string,object>** | An object where each key represents a custom field. All custom fields are referenced as randomly generated 40-character hashes | [optional]
**billing_frequency** | [**\Pipedrive\versions\v2\Model\BillingFrequency1**](BillingFrequency1.md) |  | [optional]
**billing_frequency_cycles** | **int** | Only available in Growth and above plans  The number of times the billing frequency repeats for a product in a deal  When &#x60;billing_frequency&#x60; is set to &#x60;one-time&#x60;, this field must be &#x60;null&#x60;  When &#x60;billing_frequency&#x60; is set to &#x60;weekly&#x60;, this field cannot be &#x60;null&#x60;  For all the other values of &#x60;billing_frequency&#x60;, &#x60;null&#x60; represents a product billed indefinitely  Must be a positive integer less or equal to 208 | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
