## Tokens

### Fetch token by payment id
```php
$api->payment->fetch($paymentId);
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| paymentId*    | string      | The id of the payment to be fetched |

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

### Fetch tokens by customer id

```php
$api->customer->fetch($customerId)->tokens()->all();
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*          | string      | The id of the customer to be fetched |

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

### Fetch particular token
```php
$api->customer->fetch($customerId)->tokens()->fetch($tokenId);
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*          | string      | The id of the customer to be fetched |
| tokenId*          | string      | The id of the token to be fetched |

**Response:**
```json
{
    "id": "token_Hxe0skTXLeg9pF",
    "entity": "token",
    "token": "F85BgXnGVwcuqV",
    "bank": null,
    "wallet": null,
    "method": "card",
    "card": {
        "entity": "card",
        "name": "ankit",
        "last4": "5449",
        "network": "MasterCard",
        "type": "credit",
        "issuer": "UTIB",
        "international": false,
        "emi": false,
        "sub_type": "consumer",
        "expiry_month": 12,
        "expiry_year": 2024,
        "flows": {
            "recurring": true
        }
    },
    "recurring": true,
    "auth_type": null,
    "mrn": null,
    "used_at": 1632976165,
    "created_at": 1631687852,
    "expired_at": 1634215992,
    "dcc_enabled": false
}
```
-------------------------------------------------------------------------------------------------------

### Delete token

```php
$api->customer->fetch($customerId)->tokens()->delete($tokenId);
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*          | string      | The id of the customer to be fetched |
| tokenId*          | string      | The id of the token to be fetched |

**Response:**
```json
{
    "deleted": true
}
```
-------------------------------------------------------------------------------------------------------

### Fetch VPA tokens of a customer id

```php
$api->customer->fetch($customerId)->tokens()->all();
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*          | string      | The id of the customer to be fetched |

**Response:**
```json
{
  "entity": "collection",
  "count": 1,
  "items": [
    {
      "id": "token_EeroOjvOvorT5L",
      "entity": "token",
      "token": "4ydxm47GQjrIEx",
      "bank": null,
      "wallet": null,
      "method": "card",
      "card": {
        "entity": "card",
        "name": "Gaurav Kumar",
        "last4": "8430",
        "network": "Visa",
        "type": "credit",
        "issuer": "HDFC",
        "international": false,
        "emi": true,
        "expiry_month": 12,
        "expiry_year": 2022,
        "flows": {
          "otp": true,
          "recurring": true
        }
      },
      "vpa": null,
      "recurring": false,
      "auth_type": null,
      "mrn": null,
      "used_at": 1586976724,
      "created_at": 1586976724,
      "expired_at": 1672511399,
      "dcc_enabled": false
    }
  ]
}
```
-------------------------------------------------------------------------------------------------------

### Create a token

```php

$api->token->create(array(
    "customer_id" => "cust_1Aa00000000001",
    "method" => "card",
    "card" => array(
        "number" => "4111111111111111",
        "cvv" => "123",
        "expiry_month" => "12",
        "expiry_year" => "21",
        "name" => "Gaurav Kumar"
    ),
    "authentication" => array(
        "provider" => "razorpay",
        "provider_reference_id" => "pay_123wkejnsakd",
        "authentication_reference_number" => "100222021120200000000742753928"
    ),
    "notes" => array()
));
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*          | string      | The id of the customer to be fetched |
| method*          | string      |  The type of object that needs to be tokenised. Currently, `card` is the only supported value. |
| card*         | object      | All keys listed [here](https://razorpay.com/docs/partners/aggregators/partner-auth/token-sharing/#create-token-on-behalf-of-a-sub-merchant) are supported
|
| authentication         | object      | All keys listed [here](https://razorpay.com/docs/partners/aggregators/partner-auth/token-sharing/#create-token-on-behalf-of-a-sub-merchant) are supported |     

**Response:**
```json
{
  "id": "token_IJmat4GwYATMtx",
  "entity": "token",
  "method": "card",
  "card": {
    "last4": "1111",
    "network": "Visa",
    "type": "credit",
    "issuer": "IDFB",
    "international": false,
    "emi": false,
    "sub_type": "consumer"
  },
  "customer": {
    "id": "cust_1Aa00000000001",
    "entity": "customer",
    "name": "Bob",
    "email": "bob@gmail.com",
    "contact": "9000090000",
    "gstin": null,
    "notes": {
      "notes_key_1": "Tea, Earl Grey, Hot",
      "notes_key_2": "Tea, Earl Grey… decaf."
    },
    "created_at": 1658390470
  },
  "expired_at": 1701368999,
  "customer_id": "cust_1Aa00000000001",
  "compliant_with_tokenisation_guidelines": true,
  "status": "active",
  "notes": []
}
```
-------------------------------------------------------------------------------------------------------

### Fetch token
```php
$api->token->fetch(array("id" => "token_4lsdksD31GaZ09"));
```

**Parameters:**

| Name        | Type        | Description                                 |
|-------------|-------------|---------------------------------------------|
| id* | string      | The unique identifier of a sub-merchant account generated by Razorpay.  |

**Response:**
```json
{
  "id": "token_4lsdksD31GaZ09",
  "entity": "token",
  "customer_id": "cust_1Aa00000000001",
  "method": "card",
  "card": {
    "last4": "3335",
    "network": "Visa",
    "type": "debit",
    "issuer": "HDFC",
    "international": false,
    "emi": true,
    "sub_type": "consumer",
    "token_iin": "453335"
  },
  "compliant_with_tokenisation_guidelines": true,
  "expired_at": 1748716199,
  "status": "active",
  "status_reason": null,
  "notes": []
}
```
-------------------------------------------------------------------------------------------------------
### Delete a token
```php
$api->token->delete(array("id" => "token_4lsdksD31GaZ09"));
```

**Parameters:**

| Name        | Type        | Description                                 |
|-------------|-------------|---------------------------------------------|
| id* | string      | The unique identifier of a sub-merchant account generated by Razorpay.  |

**Response:**
```json
[]
```
-------------------------------------------------------------------------------------------------------

### Process a Payment on another PA/PG with Token
```php
$api->token->processPaymentOnAlternatePAorPG(array("id"=>"spt_4lsdksD31GaZ09"));
```

**Parameters:**

| Name        | Type        | Description                                 |
|-------------|-------------|---------------------------------------------|
| id* | string      | The unique identifier of the token whose details are to be fetched.  |

**Response:**
```json
{ 
  "card": {
      "number": "4016981500100002",
      "expiry_month" : "12",
      "expiry_year" : 2021
  }
}
```
-------------------------------------------------------------------------------------------------------
**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/api/recurring-payments/upi/tokens/)**
