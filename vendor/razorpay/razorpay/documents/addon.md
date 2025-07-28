## Addons

### Create an addon

```php
$api->subscription->fetch($subscriptionId)->createAddon(array('item' => array('name' => 'Extra Chair', 'amount' => 30000, 'currency' => 'INR'), 'quantity' => 2));
```

**Parameters:**

| Name  | Type      | Description                                      |
|-------|-----------|--------------------------------------------------|
| subscriptionId*  | boolean | The subscription ID to which the add-on is being added. |
| items  | array  | All parameters listed [here](https://razorpay.com/docs/api/payments/subscriptions/#create-an-add-on) |
| quantity  | integer | This specifies the number of units of the add-on to be charged to the customer. |

**Response:**
```json
{
  "id":"ao_00000000000001",
  "entity":"addon",
  "item":{
    "id":"item_00000000000001",
    "active":true,
    "name":"Extra appala (papadum)",
    "description":"1 extra oil fried appala with meals",
    "amount":30000,
    "unit_amount":30000,
    "currency":"INR",
    "type":"addon",
    "unit":null,
    "tax_inclusive":false,
    "hsn_code":null,
    "sac_code":null,
    "tax_rate":null,
    "tax_id":null,
    "tax_group_id":null,
    "created_at":1581597318,
    "updated_at":1581597318
  },
  "quantity":2,
  "created_at":1581597318,
  "subscription_id":"sub_00000000000001",
  "invoice_id":null
}
```
-------------------------------------------------------------------------------------------------------

### Fetch all addons

```php
$api->addon->all($options);
```

**Parameters:**

| Name  | Type      | Description                                      |
|-------|-----------|--------------------------------------------------|
| from  | timestamp | timestamp after which the addons were created  |
| to    | timestamp | timestamp before which the addons were created |
| count | integer   | number of addons to fetch (default: 10)        |
| skip  | integer   | number of addons to be skipped (default: 0)    |

**Response:**
```json
{
  "entity": "collection",
  "count": 1,
  "items": [
    {
      "id": "ao_00000000000002",
      "entity": "addon",
      "item": {
        "id": "item_00000000000002",
        "active": true,
        "name": "Extra sweet",
        "description": "1 extra sweet of the day with meals",
        "amount": 90000,
        "unit_amount": 90000,
        "currency": "INR",
        "type": "addon",
        "unit": null,
        "tax_inclusive": false,
        "hsn_code": null,
        "sac_code": null,
        "tax_rate": null,
        "tax_id": null,
        "tax_group_id": null,
        "created_at": 1581597318,
        "updated_at": 1581597318
      },
      "quantity": 1,
      "created_at": 1581597318,
      "subscription_id": "sub_00000000000001",
      "invoice_id": "inv_00000000000001"
    }
  ]
}
```
-------------------------------------------------------------------------------------------------------

### Fetch an addon

```php
$api->addon->fetch($addonId);
```

**Parameters:**

| Name            | Type    | Description                                                                  |
|-----------------|---------|------------------------------------------------------------------------------|
| addonId*          | string | addon id to be fetched                                               |

**Response:**
```json
{
  "id":"ao_00000000000001",
  "entity":"addon",
  "item":{
    "id":"item_00000000000001",
    "active":true,
    "name":"Extra appala (papadum)",
    "description":"1 extra oil fried appala with meals",
    "amount":30000,
    "unit_amount":30000,
    "currency":"INR",
    "type":"addon",
    "unit":null,
    "tax_inclusive":false,
    "hsn_code":null,
    "sac_code":null,
    "tax_rate":null,
    "tax_id":null,
    "tax_group_id":null,
    "created_at":1581597318,
    "updated_at":1581597318
  },
  "quantity":2,
  "created_at":1581597318,
  "subscription_id":"sub_00000000000001",
  "invoice_id":null
}
```
-------------------------------------------------------------------------------------------------------

### Delete an addon

```php
$api->addon->fetch($addonId)->delete();
```

**Parameters:**

| Name            | Type    | Description  |
|-----------------|---------|--------------|
| addonId*        | string | addon id to be deleted |    

**Response:**
```json
[]
```
-------------------------------------------------------------------------------------------------------

**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/api/subscriptions/#add-ons)**
