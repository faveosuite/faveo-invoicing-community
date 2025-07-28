## Payment Links

### Create payment link

Request #1
Standard Payment Link

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true,
'first_min_partial_amount'=>100,'expire_by' => 1691097057, 'reference_id' => 'TS1989', 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar',
'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,
'reminder_enable'=>true ,'notes'=>array('policy_name'=> 'Jeevan Bima'),'callback_url' => 'https://example-callback-url.com/',
'callback_method'=>'get'));
```

Request #2
UPI Payment Link

```php
$api->paymentLink->create(array('upi_link'=>true,'amount'=>500, 'currency'=>'INR', 'expire_by' => 1691097057, 'reference_id' => 'TS1989','description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar','email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true ,'notes'=>array('policy_name'=> 'Jeevan Bima')));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|upi_link*          | boolean | boolean Must be set to true   //   to creating UPI Payment Link only                                     |
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|accept_partial        | boolean  | Indicates whether customers can make partial payments using the Payment Link. Possible values: true - Customer can make partial payments. false (default) - Customer cannot make partial payments. // UPI Payment Link is not supported partial payment  |
|description           | string  | A brief description of the Payment Link                     |
|first_min_partial_amount           | integer  |Minimum amount, in currency subunits, that must be paid by the customer as the first partial payment. // UPI Payment Link is not supported partial payment  |
|reference_id           | string  | AReference number tagged to a Payment Link.                      |
|customer           | array  | name, email, contact                 |
|expire_by           | integer  | Timestamp, in Unix, at which the Payment Link will expire. By default, a Payment Link will be valid for six months from the date of creation.                     |
|notify           | object  | sms or email (boolean)                     |
|notes           | json object  | Key-value pair that can be used to store additional information about the entity. Maximum 15 key-value pairs, 256 characters (maximum) each. For example, "note_key": "Beam me up Scotty”                     |

**Response:**
For create payment link response please click [here](https://razorpay.com/docs/api/payment-links/#create-payment-link)

-------------------------------------------------------------------------------------------------------

### Fetch all payment link

```php
$api->paymentLink->all();
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|payment_id          | string | Unique identifier of the payment associated with the Payment Link.                                               |
|reference_id        | string  | The unique reference number entered by you while creating the Payment Link.                     |

**Response:**
For fetch all payment link response please click [here](https://razorpay.com/docs/api/payment-links/#all-payment-links)

-------------------------------------------------------------------------------------------------------

### Fetch specific payment link

```php
$api->paymentLink->fetch($paymentLinkId);
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| paymentLinkId*          | string |  Unique identifier of the Payment Link.                         |

**Response:**
For fetch specific payment link response please click [here](https://razorpay.com/docs/api/payment-links/#specific-payment-links-by-id)

-------------------------------------------------------------------------------------------------------

### Update payment link

```php
$api->paymentLink->fetch($paymentLinkId)->edit(array("reference_id"=>"TS42", "expire_by"=>"1640270451" , "reminder_enable"=>0, "notes"=>["policy_name"=>"Jeevan Saral 2"]));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| paymentLinkId*          | string | The unique identifier of the Payment Link that needs to be updated.                         |
| accept_partial         | boolean | Indicates whether customers can make partial payments using the Payment Link. Possible values: true - Customer can make partial payments. false (default) - Customer cannot make partial payments.                         |
| reference_id          | string | Adds a unique reference number to an existing link.                         |
| expire_by         | integer | Timestamp, in Unix format, when the payment links should expire.                         |
| notes          | string | object Key-value pair that can be used to store additional information about the entity. Maximum 15 key-value pairs, 256 characters (maximum) each. For example, "note_key": "Beam me up Scotty”.                         |

**Response:**
For updating payment link response please click [here](https://razorpay.com/docs/api/payment-links/#update-payment-link)

-------------------------------------------------------------------------------------------------------

### Cancel a payment link

```php
$api->paymentLink->fetch($paymentLinkId)->cancel();
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| paymentLinkId*          | string | Unique identifier of the Payment Link.                         |

**Response:**
For canceling payment link response please click [here](https://razorpay.com/docs/api/payment-links/#cancel-payment-link)
-------------------------------------------------------------------------------------------------------

### Send notification

```php
$api->paymentLink->fetch($paymentLinkId)->notifyBy($medium);
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| paymentLinkId*          | string | Unique identifier of the Payment Link that should be resent.                         |
| medium*          | string | `sms`/`email`,Medium through which the Payment Link must be resent. Allowed values are:           |

**Response:**
```json
{
    "success": true
}
```
-------------------------------------------------------------------------------------------------------

### Transfer payments received using payment links

```php
$api->paymentLink->create(array('amount'=>20000, 'currency'=>'INR', 'accept_partial'=>false, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('order'=>array('transfers'=>array('account'=>'acc_CPRsN1LkFccllA', 'amount'=>500, 'currency'=>'INR', 'notes'=>array('branch'=>'Acme Corp Bangalore North', 'name'=>'Bhairav Kumar' ,'linked_account_notes'=>array('branch'))), array('account'=>'acc_CNo3jSI8OkFJJJ', 'amount'=>500, 'currency'=>'INR', 'notes'=>array('branch'=>'Acme Corp Bangalore North', 'name'=>'Saurav Kumar' ,'linked_account_notes'=>array('branch')))))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|options*           | array  |  Options to configure the transfer in the Payment Link. Parent parameter under which the order child parameter must be passed.                     |

**Response:**
```json
{
  "accept_partial": false,
  "amount": 1500,
  "amount_paid": 0,
  "callback_method": "",
  "callback_url": "",
  "cancelled_at": 0,
  "created_at": 1596526969,
  "currency": "INR",
  "customer": {
    "contact": "+919999999999",
    "email": "gaurav.kumar@example.com",
    "name": "Gaurav Kumar"
  },
  "deleted_at": 0,
  "description": "Payment for policy no #23456",
  "expire_by": 0,
  "expired_at": 0,
  "first_min_partial_amount": 0,
  "id": "plink_FMbhpT6nqDjDei",
  "notes": null,
  "notify": {
    "email": true,
    "sms": true
  },
  "payments": null,
  "reference_id": "#aasasw8",
  "reminder_enable": true,
  "reminders": [],
  "short_url": "https://rzp.io/i/ORor1MT",
  "source": "",
  "source_id": "",
  "status": "created",
  "updated_at": 1596526969,
  "user_id": ""
}
```
-------------------------------------------------------------------------------------------------------

### Offers on payment links

```php
$api->paymentLink->create(array('amount'=>20000, 'currency'=>'INR', 'accept_partial'=>false, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>false , 'options'=>array('order'=>array('offers'=>array('offer_I0PqexIiTmMRnA')))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|description           | string  | A brief description of the Payment Link                     |
|reference_id           | string  | AReference number tagged to a Payment Link.                      |
|customer           | array  | name, email, contact                 |
|expire_by           | integer  | Timestamp, in Unix, at which the Payment Link will expire. By default, a Payment Link will be valid for six months from the date of creation.                     |
|notify           | object  | sms or email (boolean)                     |
|options*        | array  | Options to associate the offer_id with the Payment Link. Parent parameter under which the order child parameter must be passed.                     |

**Response:**
```json
{
  "accept_partial": false,
  "amount": 3400,
  "amount_paid": 0,
  "cancelled_at": 0,
  "created_at": 1600183040,
  "currency": "INR",
  "customer": {
    "contact": "+919999999999",
    "email": "gaurav.kumar@example.com",
    "name": "Gaurav Kumar"
  },
  "description": "Payment for policy no #23456",
  "expire_by": 0,
  "expired_at": 0,
  "first_min_partial_amount": 0,
  "id": "plink_FdLt0WBldRyE5t",
  "notes": null,
  "notify": {
    "email": true,
    "sms": true
  },
  "payments": null,
  "reference_id": "#425",
  "reminder_enable": false,
  "reminders": [],
  "short_url": "https://rzp.io/i/CM5ohDC",
  "status": "created",
  "user_id": ""
}
```
-------------------------------------------------------------------------------------------------------

### Managing reminders for payment links

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>false));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|accept_partial        | boolean  |  Indicates whether customers can make partial payments using the Payment Link. Possible values:true - Customer can make partial payments.false (default) - Customer cannot make partial payments.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|description           | string  | A brief description of the Payment Link                     |
|customer           | array  | name, email, contact                 |
|expire_by           | integer  | Timestamp, in Unix, at which the Payment Link will expire. By default, a Payment Link will be valid for six months from the date of creation.                     |
|notify           | object  | sms or email (boolean)                     |
|reminder_enable       | boolean  | To disable reminders for a Payment Link, pass reminder_enable as false                     |

**Response:**
```json
{
  "amount": 340000,
  "amount_due": 340000,
  "amount_paid": 0,
  "billing_end": null,
  "billing_start": null,
  "cancelled_at": null,
  "comment": null,
  "created_at": 1592579126,
  "currency": "INR",
  "currency_symbol": "₹",
  "customer_details": {
    "billing_address": null,
    "contact": "9900990099",
    "customer_contact": "9900990099",
    "customer_email": "gaurav.kumar@example.com",
    "customer_name": "Gaurav Kumar",
    "email": "gaurav.kumar@example.com",
    "gstin": null,
    "id": "cust_F4WNtqj1xb0Duv",
    "name": "Gaurav Kumar",
    "shipping_address": null
  },
  "customer_id": "cust_F4WNtqj1xb0Duv",
  "date": 1592579126,
  "description": "Salon at Home Service",
  "email_status": null,
  "entity": "invoice",
  "expire_by": 1608390326,
  "expired_at": null,
  "first_payment_min_amount": 0,
  "gross_amount": 340000,
  "group_taxes_discounts": false,
  "id": "inv_F4WfpZLk1ct35b",
  "invoice_number": null,
  "issued_at": 1592579126,
  "line_items": [],
  "notes": [],
  "order_id": "order_F4WfpxUzWmYOTl",
  "paid_at": null,
  "partial_payment": false,
  "payment_id": null,
  "receipt": "5757",
  "reminder_enable": false,
  "short_url": "https://rzp.io/i/vitLptM",
  "sms_status": null,
  "status": "issued",
  "tax_amount": 0,
  "taxable_amount": 0,
  "terms": null,
  "type": "link",
  "user_id": "",
  "view_less": true
}
```
-------------------------------------------------------------------------------------------------------

### Rename labels in checkout section

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('partial_payment'=>array('min_amount_label'=>'Minimum Money to be paid', 'partial_amount_label'=>'Pay in parts', 'partial_amount_description'=>'Pay at least ₹100', 'full_amount_label'=>'Pay the entire amount')))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|accept_partial        | boolean  |  Indicates whether customers can make partial payments using the Payment Link. Possible values:true - Customer can make partial payments.false (default) - Customer cannot make partial payments.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|description           | string  | A brief description of the Payment Link                     |
|customer           | array  | name, email, contact                 |
|expire_by           | integer  | Timestamp, in Unix, at which the Payment Link will expire. By default, a Payment Link will be valid for six months from the date of creation.                     |
|notify           | object  | sms or email (boolean)                     |
|reminder_enable       | boolean  | To disable reminders for a Payment Link, pass reminder_enable as false                     |
|options*       | array  | Options to rename the labels for partial payment fields in the checkout form. Parent parameter under which the checkout and partial_payment child parameters must be passed. |

**Response:**
```json
{
  "accept_partial": true,
  "amount": 1000,
  "amount_paid": 0,
  "callback_method": "",
  "callback_url": "",
  "cancelled_at": 0,
  "created_at": 1596193199,
  "currency": "INR",
  "customer": {
    "contact": "+919999999999",
    "email": "gaurav.kumar@example.com",
    "name": "Gaurav Kumar"
  },
  "deleted_at": 0,
  "description": "Payment for policy no #23456",
  "expire_by": 0,
  "expired_at": 0,
  "first_min_partial_amount": 100,
  "id": "plink_FL4vbXVKfW7PAz",
  "notes": null,
  "notify": {
    "email": true,
    "sms": true
  },
  "payments": null,
  "reference_id": "#42321",
  "reminder_enable": true,
  "reminders": [],
  "short_url": "https://rzp.io/i/F4GC9z1",
  "source": "",
  "source_id": "",
  "status": "created",
  "updated_at": 1596193199,
  "user_id": ""
}
```
-------------------------------------------------------------------------------------------------------

### Change Business name

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('name'=>'Lacme Corp'))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|accept_partial        | boolean  |  Indicates whether customers can make partial payments using the Payment Link. Possible values:true - Customer can make partial payments.false (default) - Customer cannot make partial payments.                     |
|first_min_partial_amount        | integer  |                      |
|description           | string  | A brief description of the Payment Link                     |
|customer           | array  | name, email, contact                 |
|notify           | object  | sms or email (boolean)                     |
|reminder_enable       | boolean  | To disable reminders for a Payment Link, pass reminder_enable as false                     |
|options*       | array  | Option to customize the business name. Parent parameter under which the checkout child parameter must be passed.|

**Response:**
```json
{
  "accept_partial": true,
  "amount": 1000,
  "amount_paid": 0,
  "callback_method": "",
  "callback_url": "",
  "cancelled_at": 0,
  "created_at": 1596187657,
  "currency": "INR",
  "customer": {
    "contact": "+919999999999",
    "email": "gaurav.kumar@example.com",
    "name": "Gaurav Kumar"
  },
  "description": "Payment for policy no #23456",
  "expire_by": 0,
  "expired_at": 0,
  "first_min_partial_amount": 100,
  "id": "plink_FL3M2gJFs1Jkma",
  "notes": null,
  "notify": {
    "email": true,
    "sms": true
  },
  "payments": null,
  "reference_id": "#2234542",
  "reminder_enable": true,
  "reminders": [],
  "short_url": "https://rzp.io/i/at2OOsR",
  "source": "",
  "source_id": "",
  "status": "created",
  "updated_at": 1596187657,
  "user_id": ""
}
```
-------------------------------------------------------------------------------------------------------

### Prefill checkout fields

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('prefill'=>array('method'=>'card', 'card[name]'=>'Gaurav Kumar', 'card[number]'=>'4111111111111111', 'card[expiry]'=>'12/21', 'card[cvv]'=>'123')))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|accept_partial        | boolean  |  Indicates whether customers can make partial payments using the Payment Link. Possible values:true - Customer can make partial payments.false (default) - Customer cannot make partial payments.                     |
|first_min_partial_amount        | integer  |                      |
|description           | string  | A brief description of the Payment Link                     |
|customer           | array  | name, email, contact                 |
|notify           | object  | sms or email (boolean)                     |
|reminder_enable       | boolean  | To disable reminders for a Payment Link, pass reminder_enable as false                     |
|options*       | array  | Options to customize Checkout. Parent parameter under which the checkout and prefill child parameters must be passed.|

**Response:**
For prefill checkout fields response please click [here](https://razorpay.com/docs/payment-links/api/new/advanced-options/customize/prefill/)

-------------------------------------------------------------------------------------------------------

### Customize payment methods

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('method'=>array('netbanking'=>'1', 'card'=>'1', 'upi'=>'0', 'wallet'=>'0')))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|accept_partial        | boolean  |  Indicates whether customers can make partial payments using the Payment Link. Possible values:true - Customer can make partial payments.false (default) - Customer cannot make partial payments.                     |
|first_min_partial_amount        | integer  |                      |
|description           | string  | A brief description of the Payment Link                     |
|customer           | array  | name, email, contact                 |
|notify           | object  | sms or email (boolean)                     |
|reminder_enable       | boolean  | To disable reminders for a Payment Link, pass reminder_enable as false                     |
|options*       | array  | Options to display or hide payment methods on the Checkout section. Parent parameter under which the checkout and method child parameters must be passed.|

**Response:**
```json
{
  "accept_partial": true,
  "amount": 1000,
  "amount_paid": 0,
  "callback_method": "",
  "callback_url": "",
  "cancelled_at": 0,
  "created_at": 1596188371,
  "currency": "INR",
  "customer": {
    "contact": "+919999999999",
    "email": "gaurav.kumar@example.com",
    "name": "Gaurav Kumar"
  },
  "deleted_at": 0,
  "description": "Payment for policy no #23456",
  "expire_by": 0,
  "expired_at": 0,
  "first_min_partial_amount": 100,
  "id": "plink_FL3YbdvN2Cj6gh",
  "notes": null,
  "notify": {
    "email": true,
    "sms": true
  },
  "payments": null,
  "reference_id": "#543422",
  "reminder_enable": true,
  "reminders": [],
  "short_url": "https://rzp.io/i/wKiXKud",
  "source": "",
  "source_id": "",
  "status": "created",
  "updated_at": 1596188371,
  "user_id": ""
}
```
-------------------------------------------------------------------------------------------------------

### Set checkout fields as read-only

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('readonly'=>array('email'=>'1','contact'=>'1')))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|accept_partial        | boolean  |  Indicates whether customers can make partial payments using the Payment Link. Possible values:true - Customer can make partial payments.false (default) - Customer cannot make partial payments.                     |
|first_min_partial_amount        | integer  |                      |
|description           | string  | A brief description of the Payment Link                     |
|customer           | array  | name, email, contact                 |
|notify           | object  | sms or email (boolean)                     |
|reminder_enable       | boolean  | To disable reminders for a Payment Link, pass reminder_enable as false                     |
|options*       | array  | Options to set contact and email as read-only fields on Checkout. Parent parameter under which the checkout and readonly child parameters must be passed.|

**Response:**
```json
{
  "accept_partial": true,
  "amount": 1000,
  "amount_paid": 0,
  "callback_method": "",
  "callback_url": "",
  "cancelled_at": 0,
  "created_at": 1596190845,
  "currency": "INR",
  "customer": {
    "contact": "+919999999999",
    "email": "gaurav.kumar@example.com",
    "name": "Gaurav Kumar"
  },
  "deleted_at": 0,
  "description": "Payment for policy no #23456",
  "expire_by": 0,
  "expired_at": 0,
  "first_min_partial_amount": 100,
  "id": "plink_FL4GA1t6FBcaVR",
  "notes": null,
  "notify": {
    "email": true,
    "sms": true
  },
  "payments": null,
  "reference_id": "#19129",
  "reminder_enable": true,
  "reminders": [],
  "short_url": "https://rzp.io/i/QVwUglR",
  "source": "",
  "source_id": "",
  "status": "created",
  "updated_at": 1596190845,
  "user_id": ""
}
```
-------------------------------------------------------------------------------------------------------

### Implement thematic changes in payment links checkout section

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('theme'=>array('hide_topbar'=>'true')))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|accept_partial        | boolean  |  Indicates whether customers can make partial payments using the Payment Link. Possible values:true - Customer can make partial payments.false (default) - Customer cannot make partial payments.                     |
|first_min_partial_amount        | integer  |                      |
|description           | string  | A brief description of the Payment Link                     |
|customer           | array  | name, email, contact                 |
|notify           | object  | sms or email (boolean)                     |
|reminder_enable       | boolean  | To disable reminders for a Payment Link, pass reminder_enable as false                     |
|options*       | array  | Options to show or hide the top bar. Parent parameter under which the checkout and theme child parameters must be passed.|

**Response:**
```json
{
  "accept_partial": true,
  "amount": 1000,
  "amount_paid": 0,
  "callback_method": "",
  "callback_url": "",
  "cancelled_at": 0,
  "created_at": 1596187814,
  "currency": "INR",
  "customer": {
    "contact": "+919999999999",
    "email": "gaurav.kumar@example.com",
    "name": "Gaurav Kumar"
  },
  "description": "Payment for policy no #23456",
  "expire_by": 0,
  "expired_at": 0,
  "first_min_partial_amount": 100,
  "id": "plink_FL3Oncr7XxXFf6",
  "notes": null,
  "notify": {
    "email": true,
    "sms": true
  },
  "payments": null,
  "reference_id": "#423212",
  "reminder_enable": true,
  "reminders": [],
  "short_url": "https://rzp.io/i/j45EmLE",
  "source": "",
  "source_id": "",
  "status": "created",
  "updated_at": 1596187814,
  "user_id": ""
}
```
-------------------------------------------------------------------------------------------------------

### Rename labels in payment details section

```php
$api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'+919999999999'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('hosted_page'=>array('label'=>array('receipt'=>'Ref No.', 'description'=>'Course Name', 'amount_payable'=>'Course Fee Payable', 'amount_paid'=>'Course Fee Paid', 'partial_amount_due'=>'Fee Installment Due', 'partial_amount_paid'=>'Fee Installment Paid', 'expire_by'=>'Pay Before', 'expired_on'=>'1632223497','amount_due'=>'Course Fee Due'), 'show_preferences'=>array('issued_to'=>false)))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|amount*        | integer  | Amount to be paid using the Payment Link.                     |
|currency           | string  |  A three-letter ISO code for the currency in which you want to accept the payment. For example, INR.                     |
|accept_partial        | boolean  |  Indicates whether customers can make partial payments using the Payment Link. Possible values:true - Customer can make partial payments.false (default) - Customer cannot make partial payments.                     |
|first_min_partial_amount        | integer  |                      |
|description           | string  | A brief description of the Payment Link                     |
|customer           | array  | name, email, contact                 |
|notify           | object  | sms or email (boolean)                     |
|reminder_enable       | boolean  | To disable reminders for a Payment Link, pass reminder_enable as false                     |
|options*       | array  | Parent parameter under which the hosted_page and label child parameters must be passed.|

**Response:**
For rename labels in payment details section response please click [here](https://razorpay.com/docs/payment-links/api/new/advanced-options/customize/rename-payment-details-labels/)

-------------------------------------------------------------------------------------------------------

**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/api/payment-links/)**
