# # RecentDataProduct

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the product | [optional]
**name** | **string** | The name of the product | [optional]
**code** | **string** | The product code | [optional]
**description** | **string** | The description of the product | [optional]
**unit** | **string** | The unit in which this product is sold | [optional]
**tax** | **float** | The tax percentage | [optional] [default to 0]
**category** | **string** | The category of the product | [optional]
**active_flag** | **bool** | Whether this product will be made active or not | [optional]
**selectable** | **bool** | Whether this product can be selected in deals or not | [optional]
**first_char** | **string** | The first letter of the product name | [optional]
**visible_to** | **int** | The visibility of the product. If omitted, the visibility will be set to the default visibility setting of this item type for the authorized user. | [optional]
**owner_id** | **int** | The ID of the user who will be marked as the owner of this product. When omitted, authorized user ID will be used. | [optional]
**files_count** | **int** | The count of files | [optional]
**add_time** | **string** | The date and time when the product was added to the deal | [optional]
**update_time** | **string** | The date and time when the product was updated to the deal | [optional]
**prices** | **object[]** | Array of objects, each containing: &#x60;currency&#x60; (string), &#x60;price&#x60; (number), &#x60;cost&#x60; (number, optional), &#x60;overhead_cost&#x60; (number, optional). Note that there can only be one price per product per currency. When &#x60;prices&#x60; is omitted altogether, a default price of 0 and a default currency based on the company&#39;s currency will be assigned. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
