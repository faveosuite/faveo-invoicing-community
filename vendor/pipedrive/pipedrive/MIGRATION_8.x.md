# Migration Guide from 7.x to 8.x

## Moving to Version-Specific Namespaces

In the previous version of the PHP SDK, all imports were under the `Pipedrive` namespace. With the introduction of version-specific namespaces, you now need to update your imports and configuration to align with the API version you intend to use.

This guide explains the changes and provides examples to help you migrate your code.

---

## Key Changes

1. **Version-Specific Namespaces**  
   Imports must now include the version (`v1` or `v2`) in the namespace. For example:
    - Old: `use Pipedrive\Configuration;`
    - New: `use Pipedrive\versions\v1\Configuration;`

2. **Configuration is Version-Specific**  
   Each API version has its own `Configuration` class. If you are using multiple versions simultaneously, you must configure each version independently.

---

## Migration Example

Below is an example of how to migrate your existing code:

```diff
<?php

use Pipedrive\Configuration;

require_once('/path/to/client/vendor/autoload.php');

// Configure API key authorization: api_key
- $config = (new Pipedrive\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
+ $config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');

- $apiInstance = new Pipedrive\Api\ActivitiesApi(
-     new GuzzleHttp\Client(),
-     $config
- );

+ $apiInstance = new Pipedrive\versions\v1\Api\ActivitiesApi(
+     new GuzzleHttp\Client(),
+     $config
+ );

try {
    $result = $apiInstance->getActivities();
    echo '<pre>';
    print_r($result);
    echo '</pre>';
} catch (Exception $e) {
    echo 'Exception when calling ActivitiesApi->getActivities: ', $e->getMessage(), PHP_EOL;
}
```