# # PersonListProduct

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the product | [optional]
**company_id** | **int** | The ID of the company | [optional]
**name** | **string** | The name of the product | [optional]
**code** | **string** | The product code | [optional]
**description** | **string** | The description of the product | [optional]
**unit** | **string** | The unit in which this product is sold | [optional]
**tax** | **float** | The tax percentage | [optional] [default to 0]
**category** | **string** | The category of the product | [optional]
**active_flag** | **bool** | Whether this product will be made active or not | [optional] [default to true]
**selectable** | **bool** | Whether this product can be selected in deals or not | [optional] [default to true]
**first_char** | **string** | The first letter of the product name | [optional]
**visible_to** | [**VisibleTo**](VisibleTo.md) | The visibility of the product. If omitted, the visibility will be set to the default visibility setting of this item type for the authorized user.&lt;table&gt;&lt;tr&gt;&lt;th&gt;Value&lt;/th&gt;&lt;th&gt;Description&lt;/th&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;1&#x60;&lt;/td&gt;&lt;td&gt;Owner &amp;amp; followers (private)&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;3&#x60;&lt;/td&gt;&lt;td&gt;Entire company (shared)&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt; | [optional]
**owner_id** | **int** | The ID of the user who will be marked as the owner of this product. When omitted, the authorized user ID will be used | [optional]
**files_count** | **int** | The count of files | [optional]
**add_time** | **string** | The date and time when the product was added to the deal | [optional]
**update_time** | **string** | The date and time when the product was updated to the deal | [optional]
**deal_id** | **int** | The ID of the deal | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
