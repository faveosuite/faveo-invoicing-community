## payment verification

### Verify payment verification

```php
$api->utility->verifyPaymentSignature(array('razorpay_order_id' => $razorpayOrderId, 'razorpay_payment_id' => $razorpayPaymentId, 'razorpay_signature' => $razorpaySignature));
```

**Parameters:**


| Name  | Type      | Description                                      |
|-------|-----------|--------------------------------------------------|
| orderId*  | string | The id of the order to be fetched  |
| paymentId*    | string | The id of the payment to be fetched |
| signature* | string   | Signature returned by the Checkout. This is used to verify the payment. |

-------------------------------------------------------------------------------------------------------
### Verify subscription verification

```php
$api->utility->verifyPaymentSignature(array('razorpay_subscription_id' => $razorpaySubscriptionId, 'razorpay_payment_id' => $razorpayPaymentId, 'razorpay_signature' => $razorpaySignature));
```

**Parameters:**


| Name  | Type      | Description                                      |
|-------|-----------|--------------------------------------------------|
| subscriptionId*  | string | The id of the subscription to be fetched  |
| paymentId*    | string | The id of the payment to be fetched |
| signature* | string   | Signature returned by the Checkout. This is used to verify the payment. |

-------------------------------------------------------------------------------------------------------
### Verify paymentlink verification

```php
$api->utility->verifyPaymentSignature(array('razorpay_payment_link_id' => $razorpayPaymentlinkId, 'razorpay_payment_id' => $razorpayPaymentId, 'razorpay_payment_link_reference_id' => $razorpayPaymentLinkReferenceId, 'razorpay_payment_link_status' => $razorpayPaymentLinkStatus, 'razorpay_signature' => $razorpayPaymentLinkSignature));
```

**Parameters:**


| Name  | Type      | Description                                      |
|-------|-----------|--------------------------------------------------|
| razorpayPaymentlinkId*  | string | The id of the paymentlink to be fetched  |
| razorpayPaymentId*  | string | The id of the payment to be fetched  |
| razorpayPaymentLinkReferenceId*  | string |  A reference number tagged to a Payment Link |
| razorpayPaymentLinkStatus*  | string | Current status of the link  |
| razorpayPaymentLinkSignature*    | string | Signature returned by the Checkout. This is used to verify the payment. |

-------------------------------------------------------------------------------------------------------

### Verify webhook signature

```php
$webhookBody = '{"entity":"event","account_id":"acc_Hn1ukn2d32Fqww","event":"payment.authorized","contains":["payment"],"payload":{"payment":{"entity":{"id":"pay_JTVtDcN1uRYb5n","entity":"payment","amount":22345,"currency":"INR","status":"authorized","order_id":"order_JTVsulofMPyzBY","invoice_id":null,"international":false,"method":"card","amount_refunded":0,"refund_status":null,"captured":false,"description":"#JT8o1jsTyzrywc","card_id":"card_JTVtDjPwZbFbTM","card":{"id":"card_JTVtDjPwZbFbTM","entity":"card","name":"gaurav","last4":"4366","network":"Visa","type":"credit","issuer":"UTIB","international":false,"emi":true,"sub_type":"consumer","token_iin":null},"bank":null,"wallet":null,"vpa":null,"email":"you@example.com","contact":"+919999999999","notes":{"policy_name":"Jeevan Saral"},"fee":null,"tax":null,"error_code":null,"error_description":null,"error_source":null,"error_step":null,"error_reason":null,"acquirer_data":{"auth_code":"472379"},"created_at":1652183214}}},"created_at":1652183218}';

$webhookSignature = "27209ba357bf7b7b461a4c1d7f54d5a8bb6b0b4b2f5fa4aebf1f1c861a05d18a";
$webhookSecret = "test";


$api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $webhookSecret);
```

**PN: * indicates mandatory fields**
<br>
<br>
