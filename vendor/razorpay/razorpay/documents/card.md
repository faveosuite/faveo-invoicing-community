## Cards

### Create customer
```php
$api->customer->create(array(
  'name' => 'Gaurav Kumar', 
  'email' => 'gaurav.kumar@example.com', 
  'fail_existing' => "1", 
  'contact'=>'9000090000',
  'notes'=> array(
    'notes_key_1'=> 'Tea, Earl Grey, Hot',
    'notes_key_2'=> 'Tea, Earl Grey... decaf'
   )
);
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| name*          | string      | Name of the customer                        |
| email        | string      | Email of the customer                       |
| contact      | string      | Contact number of the customer              |
| fail_existing | string | If a customer with the same details already exists, the request throws an exception by default. Possible value is `1` or `0`|
| notes         | array      | A key-value pair                            |

**Response:**
```json
{
  "id": "cust_1Aa00000000003",
  "entity": "customer",
  "name": "Gaurav Kumar",
  "email": "Gaurav.Kumar@example.com",
  "contact": "9000000000",
  "gstin": null,
  "notes": {
    "notes_key_1": "Tea, Earl Grey, Hot",
    "notes_key_2": "Tea, Earl Grey… decaf."
  },
  "created_at": 1582033731
}
```
-------------------------------------------------------------------------------------------------------

### Create Order

```php
$api->order->create(array('amount' => 100, 'currency' => 'INR',  'receipt' => '123', 'customer_id'=> $customerId, 'method'=>'card', 'token'=>array('max_amount'=>'5000', 'expire_at'=>'2709971120', 'frequency'=>'monthly'), 'notes'=> array('key1'=> 'value3','key2'=> 'value2')));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| amount*   | integer      | The amount to be captured (should be equal to the authorized amount, in paise) |
| currency*   | string  | The currency of the payment (defaults to INR)  |
| customerId*   | string      | The id of the customer to be fetched |
| receipt      | string  | Your system order reference id.  |
| method      | string  | Payment method used to make the registration transaction. Possible value is `card`.  |
| token  | array  | All keys listed [here](https://razorpay.com/docs/api/recurring-payments/cards/authorization-transaction/#112-create-an-order) are supported |
| notes | array  | A key-value pair  |

**Response:**
```json
{
   "id":"order_1Aa00000000002",
   "entity":"order",
   "amount":100,
   "amount_paid":0,
   "amount_due":100,
   "currency":"INR",
   "receipt":"Receipt No. 1",
   "method":"card",
   "description":null,
   "customer_id":"cust_4xbQrmEoA5WJ01",
   "token":{
      "max_amount":5000,
      "expire_at":2709971120,
      "frequency":"monthly"
   },
   "offer_id":null,
   "status":"created",
   "attempts":0,
   "notes":{
      "notes_key_1":"Tea, Earl Grey, Hot",
      "notes_key_2":"Tea, Earl Grey… decaf."
   },
   "created_at":1565172642
}
```
-------------------------------------------------------------------------------------------------------

### Create registration link

```php
$api->subscription->createSubscriptionRegistration(array(
    'customer' => array(
        'name'    => 'Gaurav Kumar',
        'email'   => 'gaurav.kumar@example.com',
        'contact' => '9123456780'
    ),
    'type'        => 'link',
    'amount'      => 100,
    'currency'    => 'INR',
    'description' => 'Registration Link for Gaurav Kumar',
    'subscription_registration' => array(
        'method'     => 'emandate',
        'max_amount' => '500',
        'expire_at'  => '1634215992'
        'frequency'  => 'monthly'
    ),
    'receipt'      => 'Receipt No. 5',
    'email_notify' => true,
    'sms_notify'   => true,
    'expire_by'    => 1634215992,
    'notes'        => array(
        'note_key 1' => 'Beam me up Scotty',
        'note_key 2' => 'Tea. Earl Gray. Hot.'
    )
));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| customer   | array      | All parameters listed [here](https://razorpay.com/docs/api/payments/recurring-payments/cards/create-authorization-transaction/#121-create-a-registration-link) are supported |
| type*  | array | the value is `link`. |
| amount*   | integer      | The amount to be captured (should be equal to the authorized amount, in paise) |
| currency*   | string  | The currency of the payment (defaults to INR)  |
| description*  | string      | A brief description of the payment.   |
| subscription_registration   | array  | All keys listed [here](https://razorpay.com/docs/api/recurring-payments/cards/authorization-transaction/#121-create-a-registration-link) are supported  |
| receipt      | string  | Your system order reference id.  |
| sms_notify  | boolean  | SMS notifications are to be sent by Razorpay (default : true)  |
| email_notify | boolean  | Email notifications are to be sent by Razorpay (default : true)  |
| expire_by    | integer | The timestamp, in Unix format, till when the customer can make the authorization payment. |
| notes | array  | A key-value pair  |

**Response:**
```json
{
  "id": "inv_FHrXGIpd3N17DX",
  "entity": "invoice",
  "receipt": "Receipt No. 24",
  "invoice_number": "Receipt No. 24",
  "customer_id": "cust_BMB3EwbqnqZ2EI",
  "customer_details": {
    "id": "cust_BMB3EwbqnqZ2EI",
    "name": "Gaurav Kumar",
    "email": "gaurav.kumar@example.com",
    "contact": "9123456780",
    "gstin": null,
    "billing_address": null,
    "shipping_address": null,
    "customer_name": "Gaurav Kumar",
    "customer_email": "gaurav.kumar@example.com",
    "customer_contact": "9123456780"
  },
  "order_id": "order_FHrXGJNngJyEAe",
  "line_items": [],
  "payment_id": null,
  "status": "issued",
  "expire_by": 4102444799,
  "issued_at": 1595491014,
  "paid_at": null,
  "cancelled_at": null,
  "expired_at": null,
  "sms_status": "pending",
  "email_status": "pending",
  "date": 1595491014,
  "terms": null,
  "partial_payment": false,
  "gross_amount": 100,
  "tax_amount": 0,
  "taxable_amount": 0,
  "amount": 100,
  "amount_paid": 0,
  "amount_due": 100,
  "currency": "INR",
  "currency_symbol": "₹",
  "description": "Registration Link for Gaurav Kumar",
  "notes": {
    "note_key 1": "Beam me up Scotty",
    "note_key 2": "Tea. Earl Gray. Hot."
  },
  "comment": null,
  "short_url": "https://rzp.io/i/VSriCfn",
  "view_less": true,
  "billing_start": null,
  "billing_end": null,
  "type": "link",
  "group_taxes_discounts": false,
  "created_at": 1595491014,
  "idempotency_key": null
}
```
-------------------------------------------------------------------------------------------------------

## Create an order to charge the customer

```php
$api->order->create(array('amount' => '100', 'currency' => 'INR', 'customer_id'=> $customerId, 'method'=>'card',  'receipt' => 'Receipt No. 1', 'token'=>array('max_amount'=>'5000', 'expire_at'=>'2709971120', 'frequency'=>'monthly'), 'notes'=> array('key1'=> 'value3','key2'=> 'value2')));
```
**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| amount*   | integer      | The amount to be captured (should be equal to the authorized amount, in paise) |
| currency*   | string  | The currency of the payment (defaults to INR)  |
| customerId*   | string      | The id of the customer to be fetched |
| method      | string  | Payment method used to make the registration transaction. Possible value is `card`.  |
| receipt      | string  | Your system order reference id.  |
| token  | array  | All keys listed [here](https://razorpay.com/docs/api/recurring-payments/cards/subsequent-payments/#31-create-an-order-to-charge-the-customer) are supported |
| notes | array  | A key-value pair  |

**Response:**
```json
{
   "id":"order_1Aa00000000002",
   "entity":"order",
   "amount":100,
   "amount_paid":0,
   "amount_due":100,
   "currency":"INR",
   "receipt":"Receipt No. 1",
   "method":"card",
   "description":null,
   "customer_id":"cust_4xbQrmEoA5WJ01",
   "token":{
      "max_amount":5000,
      "expire_at":2709971120,
      "frequency":"monthly"
   },
   "offer_id":null,
   "status":"created",
   "attempts":0,
   "notes":{
      "notes_key_1":"Tea, Earl Grey, Hot",
      "notes_key_2":"Tea, Earl Grey… decaf."
   },
   "created_at":1565172642
}
```
-------------------------------------------------------------------------------------------------------

## Create a recurring payment

```php
$api->payment->createRecurring(array(
    'email'       => 'gaurav.kumar@example.com',
    'contact'     => '9000090000',
    'amount'      => 100,
    'currency'    => 'INR',
    'order_id'    => 'order_1Aa00000000002',
    'customer_id' => 'cust_1Aa00000000001',
    'token'       => 'token_1Aa00000000001',
    'recurring'   => true,
    'description' => 'Creating recurring payment for Gaurav Kumar'
));
```
**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| email*   | string      | The customer's email address |
| contact*   | string      | The customer's phone number |
| amount*   | integer      | The amount to be captured (should be equal to the authorized amount, in paise) |
| currency*   | string  | The currency of the payment (defaults to INR)  |
| orderId*   | string      | The id of the order to be fetched |
| customerId*   | string      | The id of the customer to be fetched |
| tokenId*   | string      | The id of the token to be fetched |
| recurring*   | boolean      | Possible values is `true` or `false` |
| description  | string      | A brief description of the payment.   |
| notes | array  | A key-value pair  |

**Response:**
```json
{
  "razorpay_payment_id" : "pay_1Aa00000000001",
  "razorpay_order_id" : "order_1Aa00000000001",
  "razorpay_signature" : "9ef4dffbfd84f1318f6739a3ce19f9d85851857ae648f114332d8401e0949a3d"
}
```
-------------------------------------------------------------------------------------------------------

### Create an Authorization Payment

Please refer this [doc](https://razorpay.com/docs/api/recurring-payments/cards/authorization-transaction/#113-create-an-authorization-payment) for authorization payment

-------------------------------------------------------------------------------------------------------

## Send/Resend notifications

```php
$api->invoice->fetch($invoiceId)->notifyBy($medium);
```
**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| invoiceId*   | string      | The id of the invoice to be fetched |
| medium*   | string      | Possible values are `sms` or `email` |

**Response:**
```json
{
    "success": true
}
```
-------------------------------------------------------------------------------------------------------

## Cancel registration link

```php
$api->invoice->fetch($invoiceId)->cancel();
```
**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| invoiceId*   | string      | The id of the invoice to be fetched |

**Response:**
```json
{
    "id": "inv_FHrfRupD2ouKIt",
    "entity": "invoice",
    "receipt": "Receipt No. 1",
    "invoice_number": "Receipt No. 1",
    "customer_id": "cust_BMB3EwbqnqZ2EI",
    "customer_details": {
        "id": "cust_BMB3EwbqnqZ2EI",
        "name": "Gaurav Kumar",
        "email": "gaurav.kumar@example.com",
        "contact": "9123456780",
        "gstin": null,
        "billing_address": null,
        "shipping_address": null,
        "customer_name": "Gaurav Kumar",
        "customer_email": "gaurav.kumar@example.com",
        "customer_contact": "9123456780"
    },
    "order_id": "order_FHrfRw4TZU5Q2L",
    "line_items": [],
    "payment_id": null,
    "status": "cancelled",
    "expire_by": 4102444799,
    "issued_at": 1595491479,
    "paid_at": null,
    "cancelled_at": 1595491488,
    "expired_at": null,
    "sms_status": "sent",
    "email_status": "sent",
    "date": 1595491479,
    "terms": null,
    "partial_payment": false,
    "gross_amount": 100,
    "tax_amount": 0,
    "taxable_amount": 0,
    "amount": 100,
    "amount_paid": 0,
    "amount_due": 100,
    "currency": "INR",
    "currency_symbol": "₹",
    "description": "Registration Link for Gaurav Kumar",
    "notes": {
        "note_key 1": "Beam me up Scotty",
        "note_key 2": "Tea. Earl Gray. Hot."
    },
    "comment": null,
    "short_url": "https://rzp.io/i/QlfexTj",
    "view_less": true,
    "billing_start": null,
    "billing_end": null,
    "type": "link",
    "group_taxes_discounts": false,
    "created_at": 1595491480,
    "idempotency_key": null
}
```
-------------------------------------------------------------------------------------------------------

## Fetch token by payment id

```php
$api->payment->fetch($paymentId);
```
**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| paymentId*   | string      | The id of the payment to be fetched |

**Response:**
```json
{
  "id": "pay_FHfqtkRzWvxky4",
  "entity": "payment",
  "amount": 100,
  "currency": "INR",
  "status": "captured",
  "order_id": "order_FHfnswDdfu96HQ",
  "invoice_id": null,
  "international": false,
  "method": "card",
  "amount_refunded": 0,
  "refund_status": null,
  "captured": true,
  "description": null,
  "card_id": "card_F0zoXUp4IPPGoI",
  "bank": null,
  "wallet": null,
  "vpa": null,
  "email": "gaurav.kumar@example.com",
  "contact": "+919876543210",
  "customer_id": "cust_DtHaBuooGHTuyZ",
  "token_id": "token_FHfn3rIiM1Z8nr",
  "notes": {
    "note_key 1": "Beam me up Scotty",
    "note_key 2": "Tea. Earl Gray. Hot."
  },
  "fee": 0,
  "tax": 0,
  "error_code": null,
  "error_description": null,
  "error_source": null,
  "error_step": null,
  "error_reason": null,
  "acquirer_data": {
    "auth_code": "541898"
  },
  "created_at": 1595449871
}
```
-------------------------------------------------------------------------------------------------------

## Fetch tokens by customer id

```php
$api->customer->fetch($customerId)->tokens()->all();
```
**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| customerId*   | string      | The id of the customer to be fetched |

**Response:**
```json
{
   "entity":"collection",
   "count":1,
   "items":[
      {
         "id":"token_HouA2OQR5Z2jTL",
         "entity":"token",
         "token":"2JPRk664pZHUWG",
         "bank":null,
         "wallet":null,
         "method":"card",
         "card":{
            "entity":"card",
            "name":"Gaurav Kumar",
            "last4":"8950",
            "network":"Visa",
            "type":"credit",
            "issuer":"STCB",
            "international":false,
            "emi":false,
            "sub_type":"consumer",
            "expiry_month":12,
            "expiry_year":2021,
            "flows":{
               "otp":true,
               "recurring":true
            }
         },
         "recurring":true,
         "recurring_details":{
            "status":"confirmed",
            "failure_reason":null
         },
         "auth_type":null,
         "mrn":null,
         "used_at":1629779657,
         "created_at":1629779657,
         "expired_at":1640975399,
         "dcc_enabled":false,
         "billing_address":null
      }
   ]
}
```
-------------------------------------------------------------------------------------------------------

### Fetch card

```php
$api->card->fetch($cardId);
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| cardId*          | string | card id to be fetched  |

```json
{
    "id": "card_JXPULjlKqC5j0i",
    "entity": "card",
    "name": "Gaurav",
    "last4": "4366",
    "network": "Visa",
    "type": "credit",
    "issuer": "UTIB",
    "international": false,
    "emi": true,
    "sub_type": "consumer",
    "token_iin": null
}
```
-------------------------------------------------------------------------------------------------------

## Delete tokens

```php
$api->customer->fetch($customerId)->tokens()->delete($tokenId);
```
**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| customerId*   | string      | The id of the customer to be fetched |
| tokenId*   | string      | The id of the token to be fetched |

**Response:**
```json
{
    "deleted": true
}
```
-------------------------------------------------------------------------------------------------------

## Using Card Number/ Tokenised Card Number

```php
$api->card->requestCardReference(array("number" =>"4854980604708430"));
```
**Parameters:**

| Name        | Type    | Description                                                                  |
|-------------|---------|------------------------------------------------------------------------------|
| number* | string | The card number whose PAR or network reference id should be retrieved. |
| tokenised  | string | Determines if the card is saved as a token. Possible value is `true` or `false` |

**Response:**
```json
{
  "network": "Visa",
  "payment_account_reference": "V0010013819231376539033235990",
  "network_reference_id": null
}
```
-------------------------------------------------------------------------------------------------------

## Using Razporpay token

```php
$api->card->requestCardReference(array("token" =>"token_4lsdksD31GaZ09"));
```
**Parameters:**

| Name        | Type    | Description                                                                  |
|-------------|---------|------------------------------------------------------------------------------|
| token* | string | The token whose PAR or network reference id should be retrieved.|

**Response:**
```json
{
  "network": "Visa",
  "payment_account_reference": "V0010013819231376539033235990",
  "network_reference_id": null
}
```
-------------------------------------------------------------------------------------------------------
**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/api/recurring-payments/cards/authorization-transaction/)**
