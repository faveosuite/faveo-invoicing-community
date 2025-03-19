# # BasicDealProductAllOf

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**product_id** | **int** | The ID of the product to use | [optional]
**item_price** | **float** | The price at which this product will be added to the deal | [optional]
**quantity** | **int** | Quantity – e.g. how many items of this product will be added to the deal | [optional]
**discount** | **float** | The value of the discount. The &#x60;discount_type&#x60; field can be used to specify whether the value is an amount or a percentage | [optional] [default to 0]
**discount_type** | **string** | The type of the discount&#39;s value | [optional] [default to 'percentage']
**product_variation_id** | **int** | The ID of the product variation to use. When omitted, no variation will be used | [optional]
**comments** | **string** | A textual comment associated with this product-deal attachment | [optional]
**tax** | **float** | The tax percentage | [optional] [default to 0]
**tax_method** | **string** | The tax option to be applied to the products. When using &#x60;inclusive&#x60;, the tax percentage will already be included in the price. When using &#x60;exclusive&#x60;, the tax will not be included in the price. When using &#x60;none&#x60;, no tax will be added. Use the &#x60;tax&#x60; field for defining the tax percentage amount. By default, the user setting value for tax options will be used. Changing this in one product affects the rest of the products attached to the deal | [optional]
**enabled_flag** | **bool** | Whether the product is enabled for a deal or not. This makes it possible to add products to a deal with a specific price and discount criteria, but keep them disabled, which refrains them from being included in the deal value calculation. When omitted, the product will be marked as enabled by default | [optional] [default to true]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
