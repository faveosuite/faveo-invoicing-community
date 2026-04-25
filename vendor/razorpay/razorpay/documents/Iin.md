### Iin

### Token IIN API

```php
$tokenIin = "412345";
$api->iin->fetch($tokenIin);
```

**Parameters:**

| Name       | Type   | Description                       |
|------------|--------|-----------------------------------|
| tokenIin* | string | The token IIN. |

**Response:**
```json
{
  "iin": "412345",
  "entity": "iin",
  "network": "Visa",
  "type": "credit",
  "sub_type": "business",
  "issuer_code": "HDFC",
  "issuer_name": "HDFC Bank Ltd",
  "international": false,
  "is_tokenized": true,
  "card_iin": "411111",
  "emi":{
     "available": true
     },
  "recurring": {
     "available": true
     },
  "authentication_types": [
   {
       "type":"3ds"
   },
   {
       "type":"otp"
   }
  ]
}
```
-------------------------------------------------------------------------------------------------------

### Fetch All IINs Supporting Native OTP

```php
$api->iin->all(array("flow" => "otp"));
```

**Response:**
```json
{
  "count": 24,
  "iins": [
    "512967",
    "180005",
    "401704",
    "401806",
    "123456",
    "411111",
    "123512967",
    "180012305",
    "401123704"
  ]
}
```
-------------------------------------------------------------------------------------------------------

### Fetch All IINs with Business Sub-type

```php
$api->iin->all(array("sub_type" => "business"));
```

**Response:**
```json
{
  "count": 24,
  "iins": [
    "512967",
    "180005",
    "401704",
    "401806",
    "607389",
    "652203",
    "414367",
    "787878",
    "123456",
    "411111",
    "123512967",
    "180012305",
    "401123704"
  ]
}
```
-------------------------------------------------------------------------------------------------------

**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/api/payments/cards/iin-api/#iin-entity)**