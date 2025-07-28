# # ProductVariationRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | The name of the product variation. The maximum length is 255 characters. | [optional]
**prices** | **object[]** | Array of objects, each containing: currency (string), price (number), cost (number, optional), direct_cost (number, optional), notes (string, optional). When prices is omitted altogether, a default price of 0, a default cost of 0, a default direct_cost of 0 and the user&#39;s default currency will be assigned. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
