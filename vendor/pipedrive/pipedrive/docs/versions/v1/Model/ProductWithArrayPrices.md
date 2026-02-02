# # ProductWithArrayPrices

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **float** | The ID of the product | [optional]
**name** | **string** | The name of the product | [optional]
**code** | **string** | The product code | [optional]
**unit** | **string** | The unit in which this product is sold | [optional]
**tax** | **float** | The tax percentage | [optional] [default to 0]
**active_flag** | **bool** | Whether this product is active or not | [optional] [default to true]
**selectable** | **bool** | Whether this product is selected in deals or not | [optional] [default to true]
**visible_to** | [**VisibleTo**](VisibleTo.md) | Visibility of the product | [optional]
**owner_id** | **object** | Information about the Pipedrive user who owns the product | [optional]
**billing_frequency** | **string** | Only available in Growth and above plans  How often a customer is billed for access to a service or product | [optional] [default to 'one-time']
**billing_frequency_cycles** | **int** | Only available in Growth and above plans  The number of times the billing frequency repeats for a product in a deal  When &#x60;billing_frequency&#x60; is set to &#x60;one-time&#x60;, this field is always &#x60;null&#x60;  When &#x60;billing_frequency&#x60; is set to &#x60;weekly&#x60;, this field cannot be &#x60;null&#x60;  For all the other values of &#x60;billing_frequency&#x60;, &#x60;null&#x60; represents a product billed indefinitely  Must be a positive integer less or equal to 208 | [optional]
**prices** | **object[]** | Array of objects, each containing: currency (string), price (number), cost (number, optional), overhead_cost (number, optional) | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
