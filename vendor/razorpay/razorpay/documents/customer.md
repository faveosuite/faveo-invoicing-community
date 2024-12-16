## Customer

### Create customer
```php
$api->customer->create(array('name' => 'Razorpay User', 'email' => 'customer@razorpay.com','contact'=>'9123456780','notes'=> array('notes_key_1'=> 'Tea, Earl Grey, Hot','notes_key_2'=> 'Tea, Earl Grey… decaf')));
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| name*          | string      | Name of the customer                        |
| email        | string      | Email of the customer                       |
| contact      | string      | Contact number of the customer              |
| fail_existing | string | If a customer with the same details already exists, the request throws an exception by default. Possible value is `0` or `1`|
| gstin         | string      | Customer's GST number, if available. For example, 29XAbbA4369J1PA  |
| notes         | array      | A key-value pair                            |

**Response:**
```json
{
  "id" : "cust_1Aa00000000004",
  "entity": "customer",
  "name" : "Gaurav Kumar",
  "email" : "gaurav.kumar@example.com",
  "contact" : "9123456780",
  "gstin": "29XAbbA4369J1PA",
  "notes":{
    "notes_key_1":"Tea, Earl Grey, Hot",
    "notes_key_2":"Tea, Earl Grey… decaf."
  },
  "created_at ": 1234567890
}
```

-------------------------------------------------------------------------------------------------------

### Edit customer
```php
$api->customer->fetch($customerId)->edit(array('name' => 'Razorpay User', 'email' => 'customer@razorpay.com', 'contact' => '9999999999'));
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*          | string      | The id of the customer to be updated  |
| email        | string      | Email of the customer                       |
| contact      | string      | Contact number of the customer              |
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

### Fetch all customer
```php
$api->customer->all($options);
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| count | integer   | number of payments to fetch (default: 10)        |
| skip  | integer   | number of payments to be skipped (default: 0)    |

**Response:**
```json
{
  "entity":"collection",
  "count":1,
  "items":[
    {
      "id":"cust_1Aa00000000001",
      "entity":"customer",
      "name":"Gaurav Kumar",
      "email":"gaurav.kumar@example.com",
      "contact":"9876543210",
      "gstin":"29XAbbA4369J1PA",
      "notes":{
        "note_key_1":"September",
        "note_key_2":"Make it so."
      },
      "created_at ":1234567890
    }
  ]
}
```

-------------------------------------------------------------------------------------------------------

### Fetch a customer
```php
$api->customer->fetch($customerId);
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*          | string      | The id of the customer to be fetched  |

**Response:**
```json
{
  "id" : "cust_1Aa00000000001",
  "entity": "customer",
  "name" : "Saurav Kumar",
  "email" : "Saurav.kumar@example.com",
  "contact" : "+919000000000",
  "gstin":"29XAbbA4369J1PA",
  "notes" : [],
  "created_at ": 1234567890
}
```

-------------------------------------------------------------------------------------------------------

### Add Bank Account of Customer

```php
$customerId = "cust_N5mywh91sXB69O"

$api->customer->fetch($customerId)->addBankAccount([
  "ifsc_code" => "UTIB0000194",
  "account_number" => "919999999999",
  "beneficiary_name" => "Pratheek",
  "beneficiary_address1" => "address 1",
  "beneficiary_address2" => "address 2",
  "beneficiary_address3" => "address 3",
  "beneficiary_address4" => "address 4",
  "beneficiary_email" => "random@email.com",
  "beneficiary_mobile" => "8762489310",
  "beneficiary_city" => "Bangalore",
  "beneficiary_state" => "KA",
  "beneficiary_country" => "IN",
]);
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*  | string      | Customer's bank account number  |
| account_number  | integer      | The id of the customer to be fetched  |
| account_number | string      | The name of the beneficiary associated with the bank account.  |
| beneficiary_name | string  | The virtual payment address.  |
| beneficiary_address1 | string      | The id of the customer to be fetched  |
| beneficiary_email  | string      | Email address of the beneficiary.  |
| beneficiary_mobile | integer      | Mobile number of the beneficiary.  |
| beneficiary_city  | string      | The name of the city of the beneficiary.  |
| beneficiary_state | string      | The state of the beneficiary.  |
| beneficiary_pin   | interger    | The pin code of the beneficiary's address.  |
| ifsc_code          | string      | The IFSC code of the bank branch associated with the account.  |

**Response:**
```json
{
  "id" : "cust_1Aa00000000001",
  "entity": "customer",
  "name" : "Saurav Kumar",
  "email" : "Saurav.kumar@example.com",
  "contact" : "+919000000000",
  "gstin":"29XAbbA4369J1PA",
  "notes" : [],
  "created_at ": 1234567890
}
```

-------------------------------------------------------------------------------------------------------

### Delete Bank Account of Customer

```php
$customerId = "cust_N5mywh91sXB69O"

$bankAccountId = "ba_N6aM8uo64IzxHu"

$api->customer->fetch($customerId)->deleteBankAccount($bankAccountId);
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*  | string      | Customer's bank account number  |
| bank_id  | string      | The bank_id that needs to be deleted.  |

**Response:**
```json
{
    "id": "ba_Evg09Ll05SIPSD",
    "ifsc": "ICIC0001207",
    "bank_name": "ICICI Bank",
    "name": "Test R4zorpay",
    "account_number": "XXXXXXXXXXXXXXX0434",
    "status": "deleted"
}
```

-------------------------------------------------------------------------------------------------------

### Eligibility Check API

```php
$api->customer->requestEligibilityCheck(array(
  "inquiry" => "affordability",
  "amount" => 500000,
  "currency" => "INR",
  "customer" => array(
      "id" => "cust_MVSyUEwC4qb5sN",
      "contact" => "+918220276214",
      "ip" => "105.106.107.108",
      "referrer" => "https://merchansite.com/example/paybill",
      "user_agent" => "Mozilla/5.0",
  )
));
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*  | string      | Customer's bank account number  |
| bank_id  | string      | The bank_id that needs to be deleted.  |

**Response:**
```json
{
  "amount": "500000",
  "customer": {
    "id": "KkBhM9EC1Y0HTm",
    "contact": "+918220722114"
  },
  "instruments": [
    {
      "method": "emi",
      "issuer": "HDFC",
      "type": "debit",
      "eligibility_req_id": "elig_KkCNLzlNeMYQyZ",
      "eligibility": {
        "status": "eligible"
      }
    },
    {
      "method": "paylater",
      "provider": "getsimpl",
      "eligibility_req_id": "elig_KkCNLzlNeMYQyZ",
      "eligibility": {
        "status": "eligible"
      }
    },
    {
      "method": "paylater",
      "provider": "icic",
      "eligibility_req_id": "elig_KkCNLzlNeMYQyZ",
      "eligibility": {
        "status": "eligible"
      }
    },
    {
      "method": "cardless_emi",
      "provider": "walnut369",
      "eligibility_req_id": "elig_KkCNLzlNeMYQyZ",
      "eligibility": {
        "status": "ineligible",
        "error": {
          "code": "GATEWAY_ERROR",
          "description": "The customer has not been approved by the partner.",
          "source": "business",
          "step": "inquiry",
          "reason": "user_not_approved"
        }
      }
    },
    {
      "method": "cardless_emi",
      "provider": "zestmoney",
      "eligibility_req_id": "elig_KkCNLzlNeMYQyZ",
      "eligibility": {
        "status": "ineligible",
        "error": {
          "code": "GATEWAY_ERROR",
          "description": "The customer has exhausted their credit limit.",
          "source": "business",
          "step": "inquiry",
          "reason": "credit_limit_exhausted"
        }
      }
    },
    {
      "method": "paylater",
      "provider": "lazypay",
      "eligibility_req_id": "elig_KkCNLzlNeMYQyZ",
      "eligibility": {
        "status": "ineligible",
        "error": {
          "code": "GATEWAY_ERROR",
          "description": "The order amount is less than the minimum transaction amount.",
          "source": "business",
          "step": "inquiry",
          "reason": "min_amt_required"
        }
      }
    }
  ]
}
```

-------------------------------------------------------------------------------------------------------

### Fetch Eligibility by id

```php
$api->customer->fetchEligibility("elig_F1cxDoHWD4fkQt");
```

**Parameters:**

| Name          | Type        | Description                                 |
|---------------|-------------|---------------------------------------------|
| customerId*  | string      | Customer's bank account number  |
| bank_id  | string      | The bank_id that needs to be deleted.  |

**Response:**
```json
{
  "instruments": [
    {
      "method": "paylater",
      "provider": "lazypay",
      "eligibility_req_id": "elig_LBwGKVvS2X48Lq",
      "eligibility": {
        "status": "eligible"
      }
    },
    {
      "method": "paylater",
      "provider": "getsimpl",
      "eligibility_req_id": "elig_LBwGKVvS2X48Lq",
      "eligibility": {
        "status": "ineligible",
        "error": {
          "code": "GATEWAY_ERROR",
          "description": "The customer has exhausted their credit limit",
          "source": "gateway",
          "step": "inquiry",
          "reason": "credit_limit_exhausted"
        }
      }
    }
  ]
}
```

-------------------------------------------------------------------------------------------------------

**PN: * indicates mandatory fields**
<br>
<br>
**For reference click [here](https://razorpay.com/docs/api/customers/)**
