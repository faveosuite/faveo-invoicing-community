# # SubscriptionItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the subscription | [optional]
**user_id** | **int** | The ID of the user who created the subscription | [optional]
**deal_id** | **int** | The ID of the deal this subscription is associated with | [optional]
**description** | **string** | The description of the recurring subscription | [optional]
**is_active** | **bool** | The subscription status | [optional]
**cycles_count** | **int** | Shows how many payments a recurring subscription has | [optional]
**cycle_amount** | **int** | The amount of each payment | [optional]
**infinite** | **bool** | Indicates that the recurring subscription will last until it is manually canceled or deleted | [optional]
**currency** | **string** | The currency of the subscription | [optional]
**cadence_type** | **string** | The interval between payments | [optional]
**start_date** | **\DateTime** | The start date of the recurring subscription | [optional]
**end_date** | **\DateTime** | The end date of the subscription | [optional]
**lifetime_value** | **double** | The total value of all payments | [optional]
**final_status** | **string** | The final status of the subscription | [optional]
**add_time** | **\DateTime** | The creation time of the subscription | [optional]
**update_time** | **\DateTime** | The update time of the subscription | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
