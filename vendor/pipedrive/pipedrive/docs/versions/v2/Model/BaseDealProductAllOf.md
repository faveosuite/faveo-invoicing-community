# # BaseDealProductAllOf

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the deal-product (the ID of the product attached to the deal) | [optional]
**sum** | **float** | The sum of all the products attached to the deal | [optional]
**tax** | **float** | The product tax | [optional]
**deal_id** | **int** | The ID of the deal | [optional]
**name** | **string** | The product name | [optional]
**product_id** | **int** | The ID of the product | [optional]
**product_variation_id** | **int** | The ID of the product variation | [optional]
**order_nr** | **int** | The order number of the product within the deal | [optional]
**add_time** | **string** | The date and time when the product was added to the deal | [optional]
**update_time** | **string** | The date and time when the deal product was last updated | [optional]
**comments** | **string** | The comments of the product | [optional]
**currency** | **string** | The currency associated with the deal product | [optional]
**discount** | **float** | The value of the discount. The &#x60;discount_type&#x60; field can be used to specify whether the value is an amount or a percentage | [optional] [default to 0]
**discount_type** | **string** | The type of the discount&#39;s value | [optional] [default to 'percentage']
**quantity** | **int** | The quantity of the product | [optional]
**item_price** | **float** | The price value of the product | [optional]
**tax_method** | **string** | The tax option to be applied to the products. When using &#x60;inclusive&#x60;, the tax percentage will already be included in the price. When using &#x60;exclusive&#x60;, the tax will not be included in the price. When using &#x60;none&#x60;, no tax will be added. Use the &#x60;tax&#x60; field for defining the tax percentage amount. By default, the user setting value for tax options will be used. Changing this in one product affects the rest of the products attached to the deal | [optional]
**is_enabled** | **bool** | Whether this product is enabled for the deal | [optional] [default to true]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
