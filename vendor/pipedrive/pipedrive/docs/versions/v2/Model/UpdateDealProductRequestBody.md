# # UpdateDealProductRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**product_id** | **int** | The ID of the product | [optional]
**item_price** | **float** | The price value of the product | [optional]
**quantity** | **float** | The quantity of the product | [optional]
**tax** | **float** | The product tax | [optional] [default to 0]
**comments** | **string** | The comments of the product | [optional]
**discount** | **float** | The value of the discount. The &#x60;discount_type&#x60; field can be used to specify whether the value is an amount or a percentage | [optional] [default to 0]
**is_enabled** | **bool** | Whether this product is enabled for the deal  Not possible to disable the product if the deal has installments associated and the product is the last one enabled  Not possible to enable the product if the deal has installments associated and the product is recurring | [optional] [default to true]
**tax_method** | **string** | The tax option to be applied to the products. When using &#x60;inclusive&#x60;, the tax percentage will already be included in the price. When using &#x60;exclusive&#x60;, the tax will not be included in the price. When using &#x60;none&#x60;, no tax will be added. Use the &#x60;tax&#x60; field for defining the tax percentage amount. By default, the user setting value for tax options will be used. Changing this in one product affects the rest of the products attached to the deal | [optional]
**discount_type** | **string** | The value of the discount. The &#x60;discount_type&#x60; field can be used to specify whether the value is an amount or a percentage | [optional] [default to 'percentage']
**product_variation_id** | **int** | The ID of the product variation | [optional]
**billing_frequency** | [**\Pipedrive\versions\v2\Model\BillingFrequency**](BillingFrequency.md) |  | [optional]
**billing_frequency_cycles** | **int** | Only available in Growth and above plans  The number of times the billing frequency repeats for a product in a deal  When &#x60;billing_frequency&#x60; is set to &#x60;one-time&#x60;, this field must be &#x60;null&#x60;  When &#x60;billing_frequency&#x60; is set to &#x60;weekly&#x60;, this field cannot be &#x60;null&#x60;  For all the other values of &#x60;billing_frequency&#x60;, &#x60;null&#x60; represents a product billed indefinitely  Must be a positive integer less or equal to 208 | [optional]
**billing_start_date** | **string** | Only available in Growth and above plans  The billing start date. Must be between 10 years in the past and 10 years in the future | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
