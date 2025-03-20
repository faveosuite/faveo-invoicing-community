# # PaymentItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the payment | [optional]
**subscription_id** | **int** | The ID of the subscription this payment is associated with | [optional]
**deal_id** | **int** | The ID of the deal this payment is associated with | [optional]
**is_active** | **bool** | The payment status | [optional]
**amount** | **double** | The payment amount | [optional]
**currency** | **string** | The currency of the payment | [optional]
**change_amount** | **double** | The difference between the amount of the current payment and the previous payment. The value can be either positive or negative. | [optional]
**due_at** | **\DateTime** | The date when payment occurs | [optional]
**revenue_movement_type** | **string** | Represents the movement of revenue in comparison with the previous payment. Possible values are: &#x60;New&#x60; - first payment of the subscription. &#x60;Recurring&#x60; - no movement. &#x60;Expansion&#x60; - current payment amount &gt; previous payment amount. &#x60;Contraction&#x60; - current payment amount &lt; previous payment amount. &#x60;Churn&#x60; - last payment of the subscription. | [optional]
**payment_type** | **string** | The type of the payment. Possible values are: &#x60;Recurring&#x60; - payments occur over fixed intervals of time, &#x60;Additional&#x60; - extra payment not the recurring payment of the recurring subscription, &#x60;Installment&#x60; - payment of the installment subscription. | [optional]
**description** | **string** | The description of the payment | [optional]
**add_time** | **\DateTime** | The creation time of the payment | [optional]
**update_time** | **\DateTime** | The update time of the payment | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
