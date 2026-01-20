## Payments

### Capture payment

```php
$api->payment->fetch($paymentId)->capture(array('amount'=>$amount,'currency' => 'INR'));
```

**Parameters:**

| Name      | Type    | Description                                                                    |
|-----------|---------|--------------------------------------------------------------------------------|
| paymentId* | string  | Id of the payment to capture                                                   |
| amount*    | integer | The amount to be captured (should be equal to the authorized amount, in paise) |
| currency*   | string  | The currency of the payment (defaults to INR)                                  |

**Response:**
```json
{
  "id": "pay_G8VQzjPLoAvm6D",
  "entity": "payment",
  "amount": 1000,
  "currency": "INR",
  "status": "captured",
  "order_id": "order_G8VPOayFxWEU28",
  "invoice_id": null,
  "international": false,
  "method": "upi",
  "amount_refunded": 0,
  "refund_status": null,
  "captured": true,
  "description": "Purchase Shoes",
  "card_id": null,
  "bank": null,
  "wallet": null,
  "vpa": "gaurav.kumar@exampleupi",
  "email": "gaurav.kumar@example.com",
  "contact": "+919999999999",
  "customer_id": "cust_DitrYCFtCIokBO",
  "notes": [],
  "fee": 24,
  "tax": 4,
  "error_code": null,
  "error_description": null,
  "error_source": null,
  "error_step": null,
  "error_reason": null,
  "acquirer_data": {
    "rrn": "033814379298"
  },
  "created_at": 1606985209
}
```
-------------------------------------------------------------------------------------------------------

### Fetch all payments

```php
$api->payment->all($options);
```

**Parameters:**

| Name  | Type      | Description                                      |
|-------|-----------|--------------------------------------------------|
| from  | timestamp | timestamp after which the payments were created  |
| to    | timestamp | timestamp before which the payments were created |
| count | integer   | number of payments to fetch (default: 10)        |
| skip  | integer   | number of payments to be skipped (default: 0)    |
| expand[]   | string    |  Used to retrieve additional information about the payment. Possible value is `cards` or `emi`|

**Response:**
```json
{
  "entity": "collection",
  "count": 2,
  "items": [
    {
      "id": "pay_G8VaL2Z68LRtDs",
      "entity": "payment",
      "amount": 900,
      "currency": "INR",
      "status": "captured",
      "order_id": "order_G8VXfKDWDEOHHd",
      "invoice_id": null,
      "international": false,
      "method": "netbanking",
      "amount_refunded": 0,
      "refund_status": null,
      "captured": true,
      "description": "Purchase Shoes",
      "card_id": null,
      "bank": "KKBK",
      "wallet": null,
      "vpa": null,
      "email": "gaurav.kumar@example.com",
      "contact": "+919999999999",
      "customer_id": "cust_DitrYCFtCIokBO",
      "notes": [],
      "fee": 22,
      "tax": 4,
      "error_code": null,
      "error_description": null,
      "error_source": null,
      "error_step": null,
      "error_reason": null,
      "acquirer_data": {
        "bank_transaction_id": "0125836177"
      },
      "created_at": 1606985740
    }
  ]
}
```
-------------------------------------------------------------------------------------------------------

### Fetch a payment

```php
$api->payment->fetch($paymentId);
```

**Parameters:**

| Name       | Type   | Description                       |
|------------|--------|-----------------------------------|
| paymentId* | string | Id of the payment to be retrieved |

**Response:**
```json
{
  "id": "pay_G8VQzjPLoAvm6D",
  "entity": "payment",
  "amount": 1000,
  "currency": "INR",
  "status": "captured",
  "order_id": "order_G8VPOayFxWEU28",
  "invoice_id": null,
  "international": false,
  "method": "upi",
  "amount_refunded": 0,
  "refund_status": null,
  "captured": true,
  "description": "Purchase Shoes",
  "card_id": null,
  "bank": null,
  "wallet": null,
  "vpa": "gaurav.kumar@exampleupi",
  "email": "gaurav.kumar@example.com",
  "contact": "+919999999999",
  "customer_id": "cust_DitrYCFtCIokBO",
  "notes": [],
  "fee": 24,
  "tax": 4,
  "error_code": null,
  "error_description": null,
  "error_source": null,
  "error_step": null,
  "error_reason": null,
  "acquirer_data": {
    "rrn": "033814379298"
  },
  "created_at": 1606985209
}
```
-------------------------------------------------------------------------------------------------------

### Fetch payments for an order

```php
$api->order->fetch($orderId)->payments();
```
**Parameters**

| Name     | Type   | Description                         |
|----------|--------|-------------------------------------|
| orderId* | string | The id of the order to be retrieve payment info |

**Response:**
```json
{
  "count": 1,
  "entity": "collection",
  "items": [
    {
      "id": "pay_DovGQXOkPBJjjU",
      "entity": "payment",
      "amount": 600,
      "currency": "INR",
      "status": "captured",
      "order_id": "order_DovFx48wjYEr2I",
      "method": "netbanking",
      "amount_refunded": 0,
      "refund_status": null,
      "captured": true,
      "description": "A Wild Sheep Chase is a novel by Japanese author Haruki Murakami",
      "card_id": null,
      "bank": "SBIN",
      "wallet": null,
      "vpa": null,
      "email": "gaurav.kumar@example.com",
      "contact": "9364591752",
      "fee": 70,
      "tax": 10,
      "error_code": null,
      "error_description": null,
      "error_source": null,
      "error_step": null,
      "error_reason": null,
      "notes": [],
      "acquirer_data": {
        "bank_transaction_id": "0125836177"
      },
      "created_at": 1400826750
    }
  ]
}
```
-------------------------------------------------------------------------------------------------------

### Update a payment

```php
$api->payment->fetch($paymentId)->edit(array('notes'=> array('key_1'=> 'value1','key_2'=> 'value2')));
```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| paymentId* | string  | Id of the payment to update          |
| notes*       | array  | A key-value pair                     |

**Response:**
```json
{
  "id": "pay_CBYy6tLmJTzn3Q",
  "entity": "payment",
  "amount": 1000,
  "currency": "INR",
  "status": "authorized",
  "order_id": null,
  "invoice_id": null,
  "international": false,
  "method": "netbanking",
  "amount_refunded": 0,
  "refund_status": null,
  "captured": false,
  "description": null,
  "card_id": null,
  "bank": "UTIB",
  "wallet": null,
  "vpa": null,
  "email": "testme@acme.com",
  "customer_id": "cust_JR4BVKjKyJ7enk",
  "notes": {
    "key1": "value1",
    "key2": "value2"
  },
  "fee": null,
  "tax": null,
  "error_code": null,
  "error_description": null,
  "error_source": null,
  "error_step": null,
  "error_reason": null,
  "acquirer_data": {
    "bank_transaction_id": "0125836177"
  },
  "created_at": 1553504328
}
```
-------------------------------------------------------------------------------------------------------

### Fetch expanded card or emi details for payments

Request #1: Card

```php
$api->payment->all(array('expand[]'=>'card'));
```

Request #2: EMI

```php
$api->payment->all(array('expand[]'=>'emi'));
```

**Response:**<br>
For expanded card or emi details for payments response please click [here](https://razorpay.com/docs/api/payments/#fetch-expanded-card-or-emi-details-for-payments)

-------------------------------------------------------------------------------------------------------

### Fetch card details with paymentId

```php
$api->payment->fetch($paymentId)->fetchCardDetails();
```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| paymentId* | string  | Id of the payment to update          |

**Response:**
```json
{
  "id": "card_6krZ6bcjoeqyV9",
  "entity": "card",
  "name": "Gaurav",
  "last4": "3335",
  "network": "Visa",
  "type": "debit",
  "issuer": "SBIN",
  "international": false,
  "emi": null,
  "sub_type": "business"
}
```
-------------------------------------------------------------------------------------------------------

### Fetch Payment Downtime Details

```php
$api->payment->fetchPaymentDowntime();
```
**Response:** <br>
For payment downtime response please click [here](https://razorpay.com/docs/api/payments/downtime/#fetch-payment-downtime-details)

-------------------------------------------------------------------------------------------------------

### Fetch Payment Downtime

```php
$api->payment->fetchPaymentDowntimeById($downtimeId);

```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| downtimeId* | string  | Id to fetch payment downtime         |

**Response:** <br>
For payment downtime by id response please click [here](https://razorpay.com/docs/api/payments/downtime/#fetch-payment-downtime-details-by-id)
-------------------------------------------------------------------------------------------------------

### Payment capture settings API

```php
$api->order->create(array('amount' => 50000,'currency' => 'INR','receipt' => 'rcptid_11','payment' => array('capture' => 'automatic','capture_options' => array('automatic_expiry_period' => 12,'manual_expiry_period' => 7200,'refund_speed' => 'optimum'))));

```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| amount*          | integer | Amount of the order to be paid                                               |
| currency*        | string  | Currency of the order. Currently only `INR` is supported.       |
| receipt         | string  | Your system order reference id.                                              |
| payment         | array  | please refer this [doc](https://razorpay.com/docs/payments/payments/capture-settings/api/) for params  |

**Response:** <br>
```json
{
  "id": "order_DBJOWzybf0sJbb",
  "entity": "order",
  "amount": 50000,
  "amount_paid": 0,
  "amount_due": 50000,
  "currency": "INR",
  "receipt": "rcptid_11",
  "status": "created",
  "attempts": 0,
  "notes": [],
  "created_at": 1566986570
}
```
-------------------------------------------------------------------------------------------------------

### Create Payment Json

```php
$api->payment->createPaymentJson(array(
    "amount" => 100,
    "currency" => "INR",
    "contact" => "9000090000",
    "email" => "gaurav.kumar@example.com",
    "order_id" => "order_DPzFe1Q1dEOKed",
    "method" => "card",
    "card" => array(
        "number" => "4386289407660153",
        "name" => "Gaurav",
        "expiry_month" => 11,
        "expiry_year" => 30,
        "cvv" => 100
    ),
    "authentication" => array(
        "authentication_channel" => "browser"
    ),
    "browser" => array(
        "java_enabled" => false,
        "javascript_enabled" => false,
        "timezone_offset" => 11,
        "color_depth" => 23,
        "screen_width" => 23,
        "screen_height" => 100
    )
));
```

**Parameters:**
| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| amount*          | integer | Amount of the order to be paid  |
| currency*   | string  | The currency of the payment (defaults to INR)                                  |
| order_id*        | string  | The unique identifier of the order created. |
| email*        | string      | Email of the customer                       |
| contact*      | string      | Contact number of the customer              |
| method*      | string  | Possible value is `card`, `netbanking`, `wallet`,`emi`, `upi`, `cardless_emi`, `paylater`.  |
| card      | array      | All keys listed [here](https://razorpay.com/docs/payments/payment-gateway/s2s-integration/payment-methods/#supported-payment-fields) are supported  |
| bank      | string      | Bank code of the bank used for the payment. Required if the method is `netbanking`.|
| bank_account | array      | All keys listed [here](https://razorpay.com/docs/payments/customers/customer-fund-account-api/#create-a-fund-account) are supported |
| vpa      | string      | Virtual payment address of the customer. Required if the method is `upi`. |
| wallet | string      | Wallet code for the wallet used for the payment. Required if the method is `wallet`. |
| notes | array  | A key-value pair  |

 please refer this [doc](https://razorpay.com/docs/payment-gateway/s2s-integration/payment-methods/) for params

**Response:** <br>
```json
{
  "razorpay_payment_id": "pay_FVmAstJWfsD3SO",
  "next": [
    {
      "action": "redirect",
      "url": "https://api.razorpay.com/v1/payments/FVmAtLUe9XZSGM/authorize"
    },
    {
      "action": "otp_generate",
      "url": "https://api.razorpay.com/v1/payments/pay_FVmAstJWfsD3SO/otp_generate?track_id=FVmAtLUe9XZSGM&key_id=<YOUR_KEY_ID>"
    }
  ]
}
```
-------------------------------------------------------------------------------------------------------

### OTP Generate

```php
$api = new Api("key","");  // Use Only razorpay key

$api->payment->otpGenerate($paymentId);
```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| paymentId*    | integer | Unique identifier of the payment                                               |

**Response:** <br>

```json
{
 "razorpay_payment_id": "pay_FVmAstJWfsD3SO",
 "next": [
  {
   "action": "otp_submit",
   "url": "https://api.razorpay.com/v1/payments/pay_FVmAstJWfsD3SO/otp_submit/ac2d415a8be7595de09a24b41661729fd9028fdc?key_id=<YOUR_KEY_ID>"
  },
  {
   "action": "otp_resend",
   "url": "https://api.razorpay.com/v1/payments/pay_FVmAstJWfsD3SO/otp_resend/json?key_id=<YOUR_KEY_ID>"
  }
 ],
 "metadata": {
  "issuer": "HDFC",
  "network": "MC",
  "last4": "1111",
  "iin": "411111"
 }
}
```

-------------------------------------------------------------------------------------------------------

### OTP Submit

```php
$api->payment->fetch($paymentId)->otpSubmit(array('otp'=> '12345'));
```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| paymentId*    | integer | Unique identifier of the payment  |
| otp*    | string | The customer receives the OTP using their preferred notification medium - SMS or email |

**Response:** <br>
Success
```json
{
 "razorpay_payment_id": "pay_D5jmY2H6vC7Cy3",
 "razorpay_order_id": "order_9A33XWu170gUtm",
 "razorpay_signature": "9ef4dffbfd84f1318f6739a3ce19f9d85851857ae648f114332d8401e0949a3d"
}
```
Failure
```json
{
  "error": {
    "code" : "BAD_REQUEST_ERROR",
    "description": "payment processing failed because of incorrect otp"
  },
  "next": ["otp_submit", "otp_resend"]
}
```
-------------------------------------------------------------------------------------------------------

### Create Payment Json (Third party validation)

```php
$api->payment->createPaymentJson(array(
  'amount' => 100,
  'currency' => 'INR',
  'email' => 'gaurav.kumar@example.com',
  'contact' => '9123456789',
  'order_id' => 'order_I6LVPRQ6upW3uh',
  'method' => 'netbanking', 
  'bank'=>'HDFC'
));
```

**Parameters:**
| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| amount*          | integer | Amount of the order to be paid  |
| currency*   | string  | The currency of the payment (defaults to INR)                                  |
| order_id*        | string  | The unique identifier of the order created. |
| email*        | string      | Email of the customer                       |
| contact*      | string      | Contact number of the customer              |
| method*      | string  | Possible value is `netbanking` |
| bank*      | string      | The customer's bank code.For example, `HDFC`.|

 please refer this [doc](https://razorpay.com/docs/payments/third-party-validation/s2s-integration/netbanking#step-3-create-a-payment) for params

**Response:** <br>
```json
{
  "razorpay_payment_id": "pay_GAWOYqPlvrtPSi",
  "next": [
    {
      "action": "redirect",
      "url": "https://api.razorpay.com/v1/payments/pay_GAWOYqPlvrtPSi/authorize"
    }
  ]
}
```
-------------------------------------------------------------------------------------------------------
### Create Payment UPI s2s / VPA token (Third party validation)

```php
$api->payment->createUpi(array(
    "amount" => 200,
    "currency" => "INR",
    "order_id" => "order_Jhgp4wIVHQrg5H",
    "email" => "gaurav.kumar@example.com",
    "contact" => "9123456789",
    "method" => "upi",
    "customer_id" => "cust_EIW4T2etiweBmG",
    "save" => true,
    "ip" => "192.168.0.103",
    "referer" => "http",
    "user_agent" => "Mozilla/5.0",
    "description" => "Test flow",
    "notes" => array(
        "note_key" => "value1"
    ),
    "upi" => array(
        "flow" => "collect",
        "vpa" => "gauravkumar@exampleupi",
        "expiry_time" => 5
    )
));
```

**Parameters:**
| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| amount*          | integer | Amount of the order to be paid  |
| currency*   | string  | The currency of the payment (defaults to INR)                                  |
| order_id*        | string  | The unique identifier of the order created. |
| email*        | string      | Email of the customer                       |
| customer_id*   | string      | The id of the customer to be fetched |
| contact*      | string      | Contact number of the customer              |
| notes | array  | A key-value pair  |
| description | string  | Descriptive text of the payment. |
| save | boolean  |  Specifies if the VPA should be stored as tokens.Possible value is `true`, `false`  |
| callback_url   | string      | URL where Razorpay will submit the final payment status. |
| ip*   | string      | The client's browser IP address. For example `117.217.74.98` |
| referer*   | string      | Value of `referer` header passed by the client's browser. For example, `https://example.com/` |
| user_agent*   | string      | Value of `user_agent` header passed by the client's browser. For example, `Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36` |
| upi* (for Upi only)  | array      | All keys listed [here](https://razorpay.com/docs/payments/third-party-validation/s2s-integration/upi/collect#step-14-initiate-a-payment) are supported  |

**Response:** <br>
```json
{
  "razorpay_payment_id": "pay_EAm09NKReXi2e0"
}
```
-------------------------------------------------------------------------------------------------------
### Create Payment UPI s2s / VPA token (Third party validation)

```php
$api->payment->createUpi(array(
    'amount' => 200,
    'currency' => 'INR',
    'order_id' => 'order_Jhgp4wIVHQrg5H',
    'email' => 'gaurav.kumar@example.com',
    'contact' => '9123456789',
    'method' => 'upi',
    'customer_id' => 'cust_EIW4T2etiweBmG',
    'ip' => '192.168.0.103',
    'referer' => 'http',
    'user_agent' => 'Mozilla/5.0',
    'description' => 'Test flow',
    'notes' => array(
        'note_key' => 'value1'
    ),
    'upi' => array(
        'flow' => 'intent'
    )
));
```

**Parameters:**
| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| amount*          | integer | Amount of the order to be paid  |
| currency*   | string  | The currency of the payment (defaults to INR)                                  |
| order_id*        | string  | The unique identifier of the order created. |
| email*        | string      | Email of the customer                       |
| customer_id*   | string      | The id of the customer to be fetched |
| contact*      | string      | Contact number of the customer              |
| notes | array  | A key-value pair  |
| description | string  | Descriptive text of the payment. |
| save | boolean  |  Specifies if the VPA should be stored as tokens.Possible value is `0`, `1`  |
| callback_url   | string      | URL where Razorpay will submit the final payment status. |
| ip*   | string      | The client's browser IP address. For example `117.217.74.98` |
| referer*   | string      | Value of `referer` header passed by the client's browser. For example, `https://example.com/` |
| user_agent*   | string      | Value of `user_agent` header passed by the client's browser. For example, `Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36` |
| upi* (for Upi only)  | array      | All keys listed [here](https://razorpay.com/docs/payments/third-party-validation/s2s-integration/upi/intent/#step-2-initiate-a-payment) are supported  |

**Response:** <br>
```json
{
  "razorpay_payment_id": "pay_CMeM6XvOPGFiF",
  "link": "upi://pay?pa=success@razorpay&pn=xyz&tr=xxxxxxxxxxx&tn=gourav&am=1&cu=INR&mc=xyzw"
}
```
-------------------------------------------------------------------------------------------------------

### Valid VPA (Third party validation)

```php
$api->payment->validateVpa(array('vpa'=>'gauravkumar@exampleupi'));
```

**Parameters:**
| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| vpa*          | string | The virtual payment address (VPA) you want to validate. For example,   `gauravkumar@exampleupi`  |

 please refer this [doc](https://razorpay.com/docs/payments/third-party-validation/s2s-integration/upi/collect#step-13-validate-the-vpa) for params

**Response:** <br>
```json
{
  "vpa": "gauravkumar@exampleupi",
  "success": true,
  "customer_name": "Gaurav Kumar"
}
```
-------------------------------------------------------------------------------------------------------

### Fetch payment methods (Third party validation)

```php
$api = new Api("key",""); // // Use Only razorpay key

$api->payment->fetchPaymentMethods();
```

**Response:** <br>
 please refer this [doc](https://razorpay.com/docs/payments/third-party-validation/s2s-integration/methods-api/#fetch-payment-methods) for response

-------------------------------------------------------------------------------------------------------

### OTP Resend

```php
$api->payment->fetch($paymentId)->otpResend();
```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| paymentId*    | integer | Unique identifier of the payment                                               |

Doc reference [doc](https://razorpay.com/docs/payments/payment-methods/cards/authentication/native-otp/#otp-resend)

**Response:** <br>

```json
{
  "next": [
    "otp_submit",
    "otp_resend"
  ],
  "razorpay_payment_id": "pay_JWaNvYmrx75sXo"
}
```

-------------------------------------------------------------------------------------------------------

### Fetch a Payment (With Expanded Card Details)

```php
$paymentId = "pay_MLzFlOC98cJmHQ";

$api->payment->fetch($paymentId)->expandedDetails(["expand[]"=> "card"]);
```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| paymentId*    | integer | Unique identifier of the payment                                               |
| expand[]    | string | Use to expand the card details when the payment method is `card`.   |

**Response:** <br>

```json
{
  "id": "pay_H9oR0gLCaVlV6m",
  "entity": "payment",
  "amount": 100,
  "currency": "INR",
  "status": "failed",
  "order_id": "order_H9o58N6qmLYQKC",
  "invoice_id": null,
  "terminal_id": "term_G5kJnYM9GhhLYT",
  "international": false,
  "method": "card",
  "amount_refunded": 0,
  "refund_status": null,
  "captured": false,
  "description": null,
  "card_id": "card_H9oR0ocen1cmZq",
  "card": {
    "id": "card_H9oR0ocen1cmZq",
    "entity": "card",
    "name": "Gaurav",
    "last4": "1213",
    "network": "RuPay",
    "type": "credit",
    "issuer": "UTIB",
    "international": false,
    "emi": false,
    "sub_type": "business"
  },
  "bank": null,
  "wallet": null,
  "vpa": null,
  "email": "gaurav.kumar@example.com",
  "contact": "+919000090000",
  "notes": {
    "email": "gaurav.kumar@example.com",
    "phone": "09000090000"
  },
  "fee": null,
  "tax": null,
  "error_code": "BAD_REQUEST_ERROR",
  "error_description": "Card issuer is invalid",
  "error_source": "customer",
  "error_step": "payment_authentication",
  "error_reason": "incorrect_card_details",
  "acquirer_data": {
    "auth_code": null,
    "authentication_reference_number": "100222021120200000000742753928"
  },
  "created_at": 1620807547
}
```

-------------------------------------------------------------------------------------------------------

### Fetch a Payment (With Expanded Offers Details)

```php
$paymentId = "pay_MLzFlOC98cJmHQ";

$api->payment->fetch($paymentId)->expandedDetails(["expand[]"=> "emi"]);
```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| paymentId*    | integer | Unique identifier of the payment                                               |
| expand[]    | string | Use to expand the emi details when the payment method is emi.   |

**Response:** <br>

```json
{
  "id": "pay_DG4ZdRK8ZnXC3k",
  "entity": "payment",
  "amount": 200000,
  "currency": "INR",
  "status": "authorized",
  "order_id": null,
  "invoice_id": null,
  "international": false,
  "method": "emi",
  "amount_refunded": 0,
  "refund_status": null,
  "captured": false,
  "description": null,
  "card_id": "card_DG4ZdUO3xABb20",
  "bank": "ICIC",
  "wallet": null,
  "vpa": null,
  "email": "gaurav@example.com",
  "contact": "+919972000005",
  "notes": [],
  "fee": null,
  "tax": null,
  "error_code": null,
  "error_description": null,
  "error_source": null,
  "error_step": null,
  "error_reason": null,
  "emi": {
    "issuer": "ICIC",
    "rate": 1300,
    "duration": 6
  },
  "acquirer_data": {
    "auth_code": "828553"
  },
  "created_at": 1568026077
}
```

-------------------------------------------------------------------------------------------------------

### Fetch a Payment (With Expanded UPI Details)

```php
$paymentId = "pay_MLzFlOC98cJmHQ";

$api->payment->fetch($paymentId)->expandedDetails(["expand[]"=> "upi"]);
```

**Parameters:**

| Name        | Type    | Description                          |
|-------------|---------|--------------------------------------|
| paymentId*    | integer | Unique identifier of the payment                                               |
| expand[]    | string | Use to expand the UPI details when the payment method is upi. |

**Response:** <br>

```json
{
  "id": "pay_DG4ZdRK8ZnXC3k",
  "entity": "payment",
  "amount": 100,
  "currency": "INR",
  "status": "captured",
  "order_id": "order_GjCr5oKh4AVC51",
  "invoice_id": null,
  "international": false,
  "method": "upi",
  "amount_refunded": 0,
  "refund_status": null,
  "captured": true,
  "description": "Payment for Adidas shoes",
  "card_id": null,
  "bank": null,
  "wallet": null,
  "vpa": "gaurav.kumar@upi",
  "email": "gaurav.kumar@example.com",
  "contact": "9000090000",
  "customer_id": "cust_K6fNE0WJZWGqtN",
  "token_id": "token_KOdY$DBYQOv08n",
  "notes": [],
  "fee": 1,
  "tax": 0,
  "error_code": null,
  "error_description": null,
  "error_source": null,
  "error_step": null,
  "error_reason": null,
  "acquirer_data": {
    "rrn": "303107535132"
  },
  "created_at": 1605871409,
  "upi": {
    "payer_account_type": "credit_card",
    "vpa": "gaurav.kumar@upi",
    "flow": "in_app" // appears only for Turbo UPI Payments.
  }
}
```

-------------------------------------------------------------------------------------------------------
**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/api/payments/)**
