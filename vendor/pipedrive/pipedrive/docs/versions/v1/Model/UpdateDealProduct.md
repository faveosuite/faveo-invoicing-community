# # UpdateDealProduct

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**product_id** | **int** | The ID of the product to use | [optional]
**item_price** | **float** | The price at which this product will be added to the deal | [optional]
**quantity** | **int** | How many items of this product will be added to the deal | [optional]
**discount** | **float** | The value of the discount. The &#x60;discount_type&#x60; field can be used to specify whether the value is an amount or a percentage | [optional] [default to 0]
**discount_type** | **string** | The type of the discount&#39;s value | [optional] [default to 'percentage']
**product_variation_id** | **int** | The ID of the product variation to use. When omitted, no variation will be used | [optional]
**comments** | **string** | A textual comment associated with this product-deal attachment | [optional]
**tax** | **float** | The tax percentage | [optional] [default to 0]
**tax_method** | **string** | The tax option to be applied to the products. When using &#x60;inclusive&#x60;, the tax percentage will already be included in the price. When using &#x60;exclusive&#x60;, the tax will not be included in the price. When using &#x60;none&#x60;, no tax will be added. Use the &#x60;tax&#x60; field for defining the tax percentage amount | [optional]
**enabled_flag** | **bool** | Whether the product is enabled for a deal or not  This makes it possible to add products to a deal with a specific price and discount criteria, but keep them disabled, which refrains them from being included in the deal value calculation. When omitted, the product will be marked as enabled by default  Not possible to disable the product if the deal has installments associated and the product is the last one enabled  Not possible to enable the product if the deal has installments associated and the product is recurring | [optional] [default to true]
**billing_frequency** | [**\Pipedrive\versions\v1\Model\BillingFrequency**](BillingFrequency.md) |  | [optional]
**billing_frequency_cycles** | **int** | Only available in Growth and above plans  The number of times the billing frequency repeats for a product in a deal  When &#x60;billing_frequency&#x60; is set to &#x60;one-time&#x60;, this field must be &#x60;null&#x60;  When &#x60;billing_frequency&#x60; is set to &#x60;weekly&#x60;, this field cannot be &#x60;null&#x60;  For all the other values of &#x60;billing_frequency&#x60;, &#x60;null&#x60; represents a product billed indefinitely  Must be a positive integer less or equal to 208 | [optional]
**billing_start_date** | **string** | Only available in Growth and above plans  The billing start date. Must be between 10 years in the past and 10 years in the future | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
