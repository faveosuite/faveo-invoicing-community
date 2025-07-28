## Invoices

### Create Invoice

Request #1
In this example, an invoice is created using the customer and item details. Here, the customer and item are created while creating the invoice.
```php
$api->invoice->create(array ('type' => 'invoice','description' => 'Invoice for the month of January 2020','partial_payment' => true,'customer' =>array ('name' => 'Gaurav Kumar','contact' => 9999999999,'email' => 'gaurav.kumar@example.com','billing_address' => array ('line1' => 'Ground & 1st Floor, SJR Cyber Laskar','line2' => 'Hosur Road','zipcode' => '560068','city' => 'Bengaluru','state' => 'Karnataka','country' => 'in'),'shipping_address' => array ('line1' => 'Ground & 1st Floor, SJR Cyber Laskar','line2' => 'Hosur Road','zipcode' => '560068','city' => 'Bengaluru','state' => 'Karnataka','country' => 'in')),'line_items' => array (array ('name' => 'Master Cloud Computing in 30 Days','description' => 'Book by Ravena Ravenclaw','amount' => 399,'currency' => 'USD','quantity' => 1)),'sms_notify' => 1,'email_notify' => 1,'currency' => 'USD','expire_by' => 1589765167));
```
**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|type*          | string | entity type (here its invoice)                                               |
|description        | string  | A brief description of the invoice.                      |
|customer_id           | string  | customer id for which invoice need be raised   |
|draft           | string  |  Invoice is created in draft state when value is set to `1`   |
| customer*     | array | All parameters listed [here](https://razorpay.com/docs/api/payments/invoices/#create-an-invoice) are supported           |
| line_items    | array | All parameters listed [here](https://razorpay.com/docs/api/payments/invoices/#create-an-invoice) are supported |
|expire_by           | array  | Details of the line item that is billed in the invoice.  |
|sms_notify           | array  | Details of the line item that is billed in the invoice.  |
|email_notify           | array  | Details of the line item that is billed in the invoice.  |
|partial_payment | boolean  | Indicates whether customers can make partial payments on the invoice . Possible values: true - Customer can make partial payments. false (default) - Customer cannot make partial payments. |
| currency*   | string  | The currency of the payment (defaults to INR)  |


Request #2
In this example, an invoice is created using existing `customer_id` and `item_id`
```php
$api->invoice->create(array ('type' => 'invoice','date' => 1589994898, 'customer_id'=> 'cust_E7q0trFqXgExmT', 'line_items'=>array(array('item_id'=>'item_DRt61i2NnL8oy6'))));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|type*          | string | entity type (here its invoice)                                               |
|description        | string  | A brief description of the invoice.                      |
|customer_id           | string  | customer id for which invoice need be raised                     |
| customer*     | array | All parameters listed [here](https://razorpay.com/docs/api/payments/invoices/#create-an-invoice) are supported           |
| line_items    | array | All parameters listed [here](https://razorpay.com/docs/api/payments/invoices/#create-an-invoice) are supported |
| sms_notify  | boolean  | SMS notifications are to be sent by Razorpay (default : 1)  |
| currency*  (conditionally mandatory) | string  | The 3-letter ISO currency code for the payment. Currently, only `INR` is supported. |
| email_notify | boolean  | Email notifications are to be sent by Razorpay (default : 1)  |
| expire_by    | integer | The timestamp, in Unix format, till when the customer can make the authorization payment. |

**Response:**
For create invoice response please click [here](https://razorpay.com/docs/api/invoices/#create-an-invoice)
-------------------------------------------------------------------------------------------------------

### Fetch all invoices

```php
$api->invoice->all();
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
|type          | string | entity type (here its invoice)                                               |
|payment_id        | string  | The unique identifier of the payment made by the customer against the invoice.                      |
|customer_id           | string  | The unique identifier of the customer.                     |
|receipt           | string  |  The unique receipt number that you entered for internal purposes.                     |

**Response:**
For fetch all invoice response please click [here](https://razorpay.com/docs/api/invoices/#fetch-multiple-invoices)

-------------------------------------------------------------------------------------------------------

### Fetch invoice

```php
$api->invoice->fetch($invoiceId);
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| invoiceId*          | string | The id of the invoice to be fetched                         |

**Response:**
```json
{
    "amount": 1000,
    "amount_due": 1000,
    "amount_paid": 0,
    "billing_end": null,
    "billing_start": null,
    "cancelled_at": null,
    "comment": null,
    "created_at": 1653596202,
    "currency": "INR",
    "currency_symbol": "₹",
    "customer_details": {
        "billing_address": null,
        "contact": "99991111999",
        "customer_contact": "99991111999",
        "customer_email": "gauraa.kumar@example.com",
        "customer_name": "Gauravss Kumar",
        "email": "gauraa.kumar@example.com",
        "gstin": null,
        "id": "cust_JDdNazagOgg9Ig",
        "name": "Gauravss Kumar",
        "shipping_address": null
    },
    "customer_id": "cust_JDdNazagOgg9Ig",
    "date": 1589994898,
    "description": null,
    "email_status": "sent",
    "entity": "invoice",
    "expire_by": null,
    "expired_at": null,
    "first_payment_min_amount": null,
    "gross_amount": 1000,
    "group_taxes_discounts": false,
    "id": "inv_JZz7g9hSZS9IsG",
    "idempotency_key": null,
    "invoice_number": null,
    "issued_at": 1653596202,
    "line_items": [
        {
            "amount": 1000,
            "currency": "INR",
            "description": null,
            "gross_amount": 1000,
            "hsn_code": null,
            "id": "li_JZz7gAI2W3Arg2",
            "item_id": "item_J7lZCyxMVeEtYB",
            "name": "Test item",
            "net_amount": 1000,
            "quantity": 1,
            "ref_id": null,
            "ref_type": null,
            "sac_code": null,
            "tax_amount": 0,
            "tax_inclusive": false,
            "tax_rate": null,
            "taxable_amount": 1000,
            "taxes": [],
            "type": "invoice",
            "unit": null,
            "unit_amount": 1000
        }
    ],
    "notes": [],
    "order_id": "order_JZz7gBTZjtUgBO",
    "paid_at": null,
    "partial_payment": false,
    "payment_id": null,
    "receipt": null,
    "reminder_status": null,
    "short_url": "https://rzp.io/i/DGpanoT",
    "sms_status": "pending",
    "status": "issued",
    "subscription_status": null,
    "supply_state_code": null,
    "tax_amount": 0,
    "taxable_amount": 1000,
    "terms": null,
    "type": "invoice",
    "user_id": null,
    "view_less": true
}
```
-------------------------------------------------------------------------------------------------------

### Update invoice

```php
$api->invoice->fetch($invoiceId)->edit(array('line_items' => array(array('id' => 'li_DAweOizsysoJU6','name' => 'Book / English August - Updated name and quantity','quantity' => 1),array('name' => 'Book / A Wild Sheep Chase','amount' => 200,'currency' => 'INR','quantity' => 1)),'notes' => array('updated-key' => 'An updated note.')));
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| invoiceId*          | string | The id of the invoice to be fetched                         |
| line_items    | array | All parameters listed [here](https://razorpay.com/docs/api/payments/invoices/#update-an-invoice) are supported |
| notes    | array | key value pair |

**Response:**
```json
{
  "id": "inv_DAweOiQ7amIUVd",
  "entity": "invoice",
  "receipt": "#0961",
  "invoice_number": "#0961",
  "customer_id": "cust_DAtUWmvpktokrT",
  "customer_details": {
    "id": "cust_DAtUWmvpktokrT",
    "name": "Gaurav Kumar",
    "email": "gaurav.kumar@example.com",
    "contact": "9977886633",
    "gstin": null,
    "billing_address": {
      "id": "addr_DAtUWoxgu91obl",
      "type": "billing_address",
      "primary": true,
      "line1": "318 C-Wing, Suyog Co. Housing Society Ltd.",
      "line2": "T.P.S Road, Vazira, Borivali",
      "zipcode": "400092",
      "city": "Mumbai",
      "state": "Maharashtra",
      "country": "in"
    },
    "shipping_address": null,
    "customer_name": "Gaurav Kumar",
    "customer_email": "gaurav.kumar@example.com",
    "customer_contact": "9977886633"
  },
  "order_id": null,
  "line_items": [
    {
      "id": "li_DAweOizsysoJU6",
      "item_id": null,
      "name": "Book / English August - Updated name and quantity",
      "description": "150 points in Quidditch",
      "amount": 400,
      "unit_amount": 400,
      "gross_amount": 400,
      "tax_amount": 0,
      "taxable_amount": 400,
      "net_amount": 400,
      "currency": "INR",
      "type": "invoice",
      "tax_inclusive": false,
      "hsn_code": null,
      "sac_code": null,
      "tax_rate": null,
      "unit": null,
      "quantity": 1,
      "taxes": []
    }
  ],
  "payment_id": null,
  "status": "draft",
  "expire_by": 1567103399,
  "issued_at": null,
  "paid_at": null,
  "cancelled_at": null,
  "expired_at": null,
  "sms_status": null,
  "email_status": null,
  "date": 1566891149,
  "terms": null,
  "partial_payment": false,
  "gross_amount": 600,
  "tax_amount": 0,
  "taxable_amount": 600,
  "amount": 600,
  "amount_paid": null,
  "amount_due": null,
  "currency": "INR",
  "currency_symbol": "₹",
  "description": "This is a test invoice.",
  "notes": {
    "updated-key": "An updated note."
  },
  "comment": null,
  "short_url": null,
  "view_less": true,
  "billing_start": null,
  "billing_end": null,
  "type": "invoice",
  "group_taxes_discounts": false,
  "created_at": 1566906474,
  "idempotency_key": null
}
```
-------------------------------------------------------------------------------------------------------

### Issue an invoice

Only an invoice in the `draft` state can be issued. 
```php

$api->invoice->fetch($invoiceId)->issue();
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| invoiceId*          | string | The id of the invoice to be issued                         |

**Response:**
```json
{
  "id": "inv_DAweOiQ7amIUVd",
  "entity": "invoice",
  "receipt": "#0961",
  "invoice_number": "#0961",
  "customer_id": "cust_DAtUWmvpktokrT",
  "customer_details": {
    "id": "cust_DAtUWmvpktokrT",
    "name": "Gaurav Kumar",
    "email": "gaurav.kumar@example.com",
    "contact": "9977886633",
    "gstin": null,
    "billing_address": {
      "id": "addr_DAtUWoxgu91obl",
      "type": "billing_address",
      "primary": true,
      "line1": "318 C-Wing, Suyog Co. Housing Society Ltd.",
      "line2": "T.P.S Road, Vazira, Borivali",
      "zipcode": "400092",
      "city": "Mumbai",
      "state": "Maharashtra",
      "country": "in"
    },
    "shipping_address": null,
    "customer_name": "Gaurav Kumar",
    "customer_email": "gaurav.kumar@example.com",
    "customer_contact": "9977886633"
  },
  "order_id": "order_DBG3P8ZgDd1dsG",
  "line_items": [
    {
      "id": "li_DAweOizsysoJU6",
      "item_id": null,
      "name": "Book / English August - Updated name and quantity",
      "description": "150 points in Quidditch",
      "amount": 400,
      "unit_amount": 400,
      "gross_amount": 400,
      "tax_amount": 0,
      "taxable_amount": 400,
      "net_amount": 400,
      "currency": "INR",
      "type": "invoice",
      "tax_inclusive": false,
      "hsn_code": null,
      "sac_code": null,
      "tax_rate": null,
      "unit": null,
      "quantity": 1,
      "taxes": []
    },
    {
      "id": "li_DAwjWQUo07lnjF",
      "item_id": null,
      "name": "Book / A Wild Sheep Chase",
      "description": null,
      "amount": 200,
      "unit_amount": 200,
      "gross_amount": 200,
      "tax_amount": 0,
      "taxable_amount": 200,
      "net_amount": 200,
      "currency": "INR",
      "type": "invoice",
      "tax_inclusive": false,
      "hsn_code": null,
      "sac_code": null,
      "tax_rate": null,
      "unit": null,
      "quantity": 1,
      "taxes": []
    }
  ],
  "payment_id": null,
  "status": "issued",
  "expire_by": 1567103399,
  "issued_at": 1566974805,
  "paid_at": null,
  "cancelled_at": null,
  "expired_at": null,
  "sms_status": null,
  "email_status": null,
  "date": 1566891149,
  "terms": null,
  "partial_payment": false,
  "gross_amount": 600,
  "tax_amount": 0,
  "taxable_amount": 600,
  "amount": 600,
  "amount_paid": 0,
  "amount_due": 600,
  "currency": "INR",
  "currency_symbol": "₹",
  "description": "This is a test invoice.",
  "notes": {
    "updated-key": "An updated note."
  },
  "comment": null,
  "short_url": "https://rzp.io/i/K8Zg72C",
  "view_less": true,
  "billing_start": null,
  "billing_end": null,
  "type": "invoice",
  "group_taxes_discounts": false,
  "created_at": 1566906474,
  "idempotency_key": null
}
```
-------------------------------------------------------------------------------------------------------

### Delete an invoice

```php
$api->invoice->fetch($invoiceId)->delete();
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| invoiceId*          | string | The id of the invoice to be deleted                         |

**Response:**
```
[]
```
-------------------------------------------------------------------------------------------------------

### Cancel an invoice

```php
$api->invoice->fetch($invoiceId)->cancel();
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| invoiceId*          | string | The id of the invoice to be cancelled                         |

**Response:**
```json
{
    "amount": 1000,
    "amount_due": 1000,
    "amount_paid": 0,
    "billing_end": null,
    "billing_start": null,
    "cancelled_at": 1654159207,
    "comment": null,
    "created_at": 1653596202,
    "currency": "INR",
    "currency_symbol": "₹",
    "customer_details": {
        "billing_address": null,
        "contact": "99991111999",
        "customer_contact": "99991111999",
        "customer_email": "gauraa.kumar@example.com",
        "customer_name": "Gauravss Kumar",
        "email": "gauraa.kumar@example.com",
        "gstin": null,
        "id": "cust_JDdNazagOgg9Ig",
        "name": "Gauravss Kumar",
        "shipping_address": null
    },
    "customer_id": "cust_JDdNazagOgg9Ig",
    "date": 1589994898,
    "description": null,
    "email_status": "sent",
    "entity": "invoice",
    "expire_by": null,
    "expired_at": null,
    "first_payment_min_amount": null,
    "gross_amount": 1000,
    "group_taxes_discounts": false,
    "id": "inv_JZz7g9hSZS9IsG",
    "idempotency_key": null,
    "invoice_number": null,
    "issued_at": 1653596202,
    "line_items": [
        {
            "amount": 1000,
            "currency": "INR",
            "description": null,
            "gross_amount": 1000,
            "hsn_code": null,
            "id": "li_JZz7gAI2W3Arg2",
            "item_id": "item_J7lZCyxMVeEtYB",
            "name": "Test item",
            "net_amount": 1000,
            "quantity": 1,
            "ref_id": null,
            "ref_type": null,
            "sac_code": null,
            "tax_amount": 0,
            "tax_inclusive": false,
            "tax_rate": null,
            "taxable_amount": 1000,
            "taxes": [],
            "type": "invoice",
            "unit": null,
            "unit_amount": 1000
        }
    ],
    "notes": [],
    "order_id": "order_JZz7gBTZjtUgBO",
    "paid_at": null,
    "partial_payment": false,
    "payment_id": null,
    "receipt": null,
    "reminder_status": null,
    "short_url": "https://rzp.io/i/DGpanoT",
    "sms_status": "pending",
    "status": "cancelled",
    "subscription_status": null,
    "supply_state_code": null,
    "tax_amount": 0,
    "taxable_amount": 1000,
    "terms": null,
    "type": "invoice",
    "user_id": null,
    "view_less": true
}
```
-------------------------------------------------------------------------------------------------------

### Send notification

```php
$api->invoice->fetch($invoiceId)->notifyBy($medium);
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| invoiceId*          | string | The id of the invoice to be notified                         |
| medium*          | string | `sms`/`email`, Medium through which notification should be sent.                         |

**Response:**
```json
{
    "success": true
}
```
-------------------------------------------------------------------------------------------------------

**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/api/invoices)**
