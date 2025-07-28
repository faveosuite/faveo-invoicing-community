# # SubscriptionRecurringCreateRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**deal_id** | **int** | The ID of the deal this recurring subscription is associated with |
**currency** | **string** | The currency of the recurring subscription. Accepts a 3-character currency code. |
**description** | **string** | The description of the recurring subscription | [optional]
**cadence_type** | **string** | The interval between payments |
**cycles_count** | **int** | Shows how many payments the subscription has. Note that one field must be set: &#x60;cycles_count&#x60; or &#x60;infinite&#x60;. If &#x60;cycles_count&#x60; is set, then &#x60;cycle_amount&#x60; and &#x60;start_date&#x60; are also required. | [optional]
**cycle_amount** | **int** | The amount of each payment |
**start_date** | **\DateTime** | The start date of the recurring subscription. Format: YYYY-MM-DD |
**infinite** | **bool** | This indicates that the recurring subscription will last until it&#39;s manually canceled or deleted. Note that only one field must be set: &#x60;cycles_count&#x60; or &#x60;infinite&#x60;. | [optional]
**payments** | **object[]** | Array of additional payments. It requires a minimum structure as follows: [{ amount:SUM, description:DESCRIPTION, due_at:PAYMENT_DATE }]. Replace SUM with a payment amount, DESCRIPTION with an explanation string, PAYMENT_DATE with a date (format YYYY-MM-DD). | [optional]
**update_deal_value** | **bool** | Indicates that the deal value must be set to recurring subscription&#39;s MRR value | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
