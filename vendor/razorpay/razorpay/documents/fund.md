## Funds

### Create a fund account
```php
$api->fundAccount->create(array('customer_id'=>$customerId,'account_type'=>'bank_account','bank_account'=>array('name'=>'Gaurav Kumar', 'account_number'=>'11214311215411', 'ifsc'=>'HDFC0000053')));
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*   | string      | The id of the customer to be fetched  |
| account_type* | string      | The bank_account to be linked to the customer ID  |
| bank_account* | array      | All keys listed [here](https://razorpay.com/docs/payments/customers/customer-fund-account-api/#create-a-fund-account) are supported |

**Response:**
```json
{
    "id": "fa_JcXaLomo4ck5IY",
    "entity": "fund_account",
    "customer_id": "cust_JZse2vlC5nK9AQ",
    "account_type": "bank_account",
    "bank_account": {
        "ifsc": "HDFC0000053",
        "bank_name": "HDFC Bank",
        "name": "Gaurav Kumar",
        "notes": [],
        "account_number": "11214311215411"
    },
    "batch_id": null,
    "active": true,
    "created_at": 1654154246
}
```
-------------------------------------------------------------------------------------------------------

### Fetch all fund accounts

```php
$api->fundAccount->all(array('customer_id'=>$customerIds));
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*   | string      | The id of the customer to be fetched  |

**Response:**
```json
{
    "entity": "collection",
    "count": 2,
    "items": [
        {
            "id": "fa_JcXYtecLkhW74k",
            "entity": "fund_account",
            "customer_id": "cust_JZse2vlC5nK9AQ",
            "account_type": "bank_account",
            "bank_account": {
                "ifsc": "HDFC0000053",
                "bank_name": "HDFC Bank",
                "name": "Gaurav Kumar",
                "notes": [],
                "account_number": "11214311215411"
            },
            "batch_id": null,
            "active": true,
            "created_at": 1654154163
        }
    ]
}
```
-------------------------------------------------------------------------------------------------------

**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/payments/customers/customer-fund-account-api/)**
