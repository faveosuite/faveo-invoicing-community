# # SubscriptionInstallmentCreateRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**deal_id** | **int** | The ID of the deal this installment subscription is associated with |
**currency** | **string** | The currency of the installment subscription. Accepts a 3-character currency code. |
**payments** | **object[]** | Array of payments. It requires a minimum structure as follows: [{ amount:SUM, description:DESCRIPTION, due_at:PAYMENT_DATE }]. Replace SUM with a payment amount, DESCRIPTION with an explanation string, PAYMENT_DATE with a date (format YYYY-MM-DD). |
**update_deal_value** | **bool** | Indicates that the deal value must be set to the installment subscription&#39;s total value | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
