# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [17.3.0](https://github.com/pipedrive/client-php/compare17.2.1...17.3.0) (2026-04-06)

### Changed
- Bumped minimum PHP version requirement from 8.0 to 8.1

## [17.2.1](https://github.com/pipedrive/client-php/compare17.2.0...17.2.1) (2026-04-06)

### Fixed
- Fixed double-nested response schema in `GET /api/v2/products` — `data` array items were incorrectly typed as a full response wrapper object instead of the product schema
- Added missing fields to v2 product response schema: `add_time`, `update_time`, `description`, and `category` (nullable)
- Added typed properties to `prices` array items in v2 product schema: `product_id`, `price`, `currency`, `cost`, `direct_cost` (nullable), `notes`
- Fixed `BaseProduct.id`, `ProductVariation.id`, and product image `product_id` typed as `number` instead of `integer`
- Documented the `updated_since` query parameter for `GET /api/v2/products` which was supported by the API but absent from the spec
- Fixed `quantity` field type from `integer` to `number` in deal product response schema (`GET /api/v2/deals/{id}/products`) — the API supports decimal values such as `5.5`

## [17.2.0](https://github.com/pipedrive/client-php/compare17.1.0...17.2.0) (2026-04-06)

### Added
- Added `health_status` field to v1 project response schema (`GET /v1/projects`, `GET /v1/projects/{id}`)
- Added `health_status` as a writable field to v2 project request body (`POST /api/v2/projects`, `PATCH /api/v2/projects/{id}`)
### Fixed
- Fixed `health_status` field type from `string` to `integer` in v2 project response schema (`GET /api/v2/projects`, `GET /api/v2/projects/{id}`)

## [17.1.0](https://github.com/pipedrive/client-php/compare17.0.0...17.1.0) (2026-04-06)

### Added
- Added `project_id` parameter to `POST /v1/files` endpoint
- Added `project_id` & `project_name` properties to responses in `/v1/files/*` endpoints

## [17.0.0](https://github.com/pipedrive/client-php/compare16.0.0...17.0.0) (2026-03-06)

### Added
- Added `website`, `linkedin`, `industry`, `annual_revenue`, and `employee_count` fields to v2 organization response schema (`GET /api/v2/organizations`, `GET /api/v2/organizations/{id}`) — all nullable, not included by default
- Added `deal_id`, `person_id`, and `org_id` as optional query filter parameters to `GET /api/v2/projects`
### Changed
- Dropped PHP 7.4 support — minimum required PHP version is now 8.0
### Fixed
- Fixed `deal_ids` query parameter serialization in `GET /api/v2/deals/products` — the array is now correctly encoded as a comma-separated string (e.g. `deal_ids=1,2,3`) to match the expected backend format
- Fixed `BillingStartDate` format from non-standard `'YYYY-MM-DD'` to correct OAS3 `date` format in both v1 and v2 deal product schemas

## [16.0.0](https://github.com/pipedrive/client-php/compare/15.7.0...16.0.0) (2026-05-05)
### Added
- Added projects v2 API
- Added project boards v2 API
- Added project phases v2 API
- Added project tasks v2 API
- Added project search v2 API
- Added project templates v2 API
- Added project fields v2 API
- Added `include_option_labels` parameter for v2 GET deal, person and organization endpoints
- Added `include_labels` parameter to v2 GET deal, person, and organization endpoints
- Added `assignee_ids` field to Tasks endpoints:
  - `GET /v1/tasks`
  - `GET /v1/tasks/{id}`
  - `POST /v1/tasks`
  - `PUT /v1/tasks/{id}`
- Added `task_id` field support to Notes endpoints:
  - `GET /v1/notes` — new `task_id` and `pinned_to_task_flag` query parameters
  - `POST /v1/notes` — new optional `task_id` field in request body
  - `PUT /v1/notes/{id}` — new optional `task_id` field in request body
  - Response schema includes new `task_id`, `task` (with `title`), and `pinned_to_task_flag` fields
### Removed
- Removed deprecated v1 endpoints that have v2 equivalents. See the [deprecation announcement](https://developers.pipedrive.com/changelog/post/deprecation-of-selected-api-v1-endpoints) for details. Please migrate to the corresponding v2 endpoints listed below:
  - `GET /v1/activities` → `GET /api/v2/activities`
  - `GET /v1/activities/collection` → `GET /api/v2/activities`
  - `GET /v1/activities/{id}` → `GET /api/v2/activities/{id}`
  - `POST /v1/activities` → `POST /api/v2/activities`
  - `PUT /v1/activities/{id}` → `PATCH /api/v2/activities/{id}`
  - `DELETE /v1/activities/{id}` → `DELETE /api/v2/activities/{id}`
  - `GET /v1/deals` → `GET /api/v2/deals`
  - `GET /v1/deals/collection` → `GET /api/v2/deals`
  - `GET /v1/deals/{id}` → `GET /api/v2/deals/{id}`
  - `GET /v1/deals/search` → `GET /api/v2/deals/search`
  - `POST /v1/deals` → `POST /api/v2/deals`
  - `PUT /v1/deals/{id}` → `PATCH /api/v2/deals/{id}`
  - `DELETE /v1/deals/{id}` → `DELETE /api/v2/deals/{id}`
  - `GET /v1/deals/{id}/activities` → `GET /api/v2/activities?deal_id={id}`
  - `GET /v1/deals/{id}/persons` → `GET /api/v2/persons?deal_id={id}`
  - `GET /v1/deals/{id}/products` → `GET /api/v2/deals/{id}/products`
  - `POST /v1/deals/{id}/products` → `POST /api/v2/deals/{id}/products`
  - `PUT /v1/deals/{id}/products/{product_attachment_id}` → `PATCH /api/v2/deals/{id}/products/{product_attachment_id}`
  - `DELETE /v1/deals/{id}/products/{product_attachment_id}` → `DELETE /api/v2/deals/{id}/products/{product_attachment_id}`
  - `GET /v1/itemSearch` → `GET /api/v2/itemSearch`
  - `GET /v1/itemSearch/field` → `GET /api/v2/itemSearch/field`
  - `GET /v1/organizations` → `GET /api/v2/organizations`
  - `GET /v1/organizations/collection` → `GET /api/v2/organizations`
  - `GET /v1/organizations/{id}` → `GET /api/v2/organizations/{id}`
  - `GET /v1/organizations/search` → `GET /api/v2/organizations/search`
  - `POST /v1/organizations` → `POST /api/v2/organizations`
  - `PUT /v1/organizations/{id}` → `PATCH /api/v2/organizations/{id}`
  - `DELETE /v1/organizations/{id}` → `DELETE /api/v2/organizations/{id}`
  - `GET /v1/organizations/{id}/deals` → `GET /api/v2/deals?org_id={id}`
  - `GET /v1/organizations/{id}/activities` → `GET /api/v2/activities?org_id={id}`
  - `GET /v1/organizations/{id}/persons` → `GET /api/v2/persons?org_id={id}`
  - `GET /v1/persons` → `GET /api/v2/persons`
  - `GET /v1/persons/collection` → `GET /api/v2/persons`
  - `GET /v1/persons/{id}` → `GET /api/v2/persons/{id}`
  - `GET /v1/persons/search` → `GET /api/v2/persons/search`
  - `POST /v1/persons` → `POST /api/v2/persons`
  - `PUT /v1/persons/{id}` → `PATCH /api/v2/persons/{id}`
  - `DELETE /v1/persons/{id}` → `DELETE /api/v2/persons/{id}`
  - `GET /v1/persons/{id}/deals` → `GET /api/v2/deals?person_id={id}`
  - `GET /v1/persons/{id}/activities` → `GET /api/v2/activities?person_id={id}`
  - `GET /v1/pipelines` → `GET /api/v2/pipelines`
  - `GET /v1/pipelines/{id}` → `GET /api/v2/pipelines/{id}`
  - `POST /v1/pipelines` → `POST /api/v2/pipelines`
  - `PUT /v1/pipelines/{id}` → `PATCH /api/v2/pipelines/{id}`
  - `DELETE /v1/pipelines/{id}` → `DELETE /api/v2/pipelines/{id}`
  - `GET /v1/products` → `GET /api/v2/products`
  - `GET /v1/products/{id}` → `GET /api/v2/products/{id}`
  - `GET /v1/products/search` → `GET /api/v2/products/search`
  - `POST /v1/products` → `POST /api/v2/products`
  - `PUT /v1/products/{id}` → `PATCH /api/v2/products/{id}`
  - `DELETE /v1/products/{id}` → `DELETE /api/v2/products/{id}`
  - `GET /v1/stages` → `GET /api/v2/stages`
  - `GET /v1/stages/{id}` → `GET /api/v2/stages/{id}`
  - `POST /v1/stages` → `POST /api/v2/stages`
  - `PUT /v1/stages/{id}` → `PATCH /api/v2/stages/{id}`
  - `DELETE /v1/stages/{id}` → `DELETE /api/v2/stages/{id}`
### Fixed
- Made `picture_id`, `org_id`, `label`, `last_name`, `cc_email`, `last_incoming_mail_time`, and `last_outgoing_mail_time` nullable in person and organization schemas to match real API behavior

## [15.7.0](https://github.com/pipedrive/client-php/compare/15.6.0...15.7.0) (2026-05-05)
### Changed
- Changed `board_id` and `phase_id` to be optional in `POST /api/v1/projects`

## [15.6.0](https://github.com/pipedrive/client-php/compare/15.5.0...15.6.0) (2026-05-05)
### Added
- Added `source_deal_id` field to Lead schema to track the deal ID when a lead is converted from a deal
- Added `source_lead_id` field to v2 Deal schema to track the lead ID when a lead is converted from a lead

## [15.5.0](https://github.com/pipedrive/client-php/compare/15.4.0...15.5.0) (2026-04-16)

### Added
- Added `include_field_code` query parameter to Filters endpoints:
  - `GET /v1/filters/{id}`
  - `PUT /v1/filters/{id}`
  - `POST /v1/filters`
  - When set to `true`, each condition in the response includes a `field_code` field identifying the field by its code name. The value is `null` if the field code cannot be resolved.

## [15.4.0](https://github.com/pipedrive/client-php/compare/15.3.0...15.4.0) (2026-02-16)

### Fixed
- Fixed `GET /v1/deals/{id}/participants` and `POST /v1/deals/{id}/participants` response schemas to accurately represent the API response structure. Added `DealParticipantItem` schema with proper nesting of `email` and `phone` fields within `person_id` object, replacing incorrect `PersonItem` reference.

## [15.3.0](https://github.com/pipedrive/client-php/compare/15.2.0...15.3.0) (2026-02-06)

### Added
- Added `custom_fields` property to the Person entity request schema in v2 endpoints that will fix types in endpoints:
    - `POST /api/v2/persons`
    - `PATCH /api/v2/persons/{id}`
- Added `custom_fields` property to the Product entity request schema in v2 endpoints that will fix types in endpoints:
    - `POST /api/v2/products`
    - `PATCH /api/v2/products/{id}`

## [15.2.0](https://github.com/pipedrive/client-php/compare/15.1.1...15.2.0) (2026-02-06)

### Fixed
- Fixed incorrect type definitions for `label` fields in v2 Persons API request schema (`PersonRequestBody`). The `label` field for both `phones` and `emails` arrays was incorrectly typed as `boolean` instead of `string`, causing SDK type generation issues. Labels now correctly accept string values like 'work', 'home', 'mobile', and 'other'.

### Changed
- Removed beta labels from v2 lead/deal conversion endpoints:
    - `GET /api/v2/leads/{id}/convert/status/{conversion_id}`
    - `POST /api/v2/leads/{id}/convert`
    - `GET /api/v2/deals/{id}/convert/status/{conversion_id}`
    - `POST /api/v2/deals/{id}/convert`
- Removed beta labels from v2 deal installments endpoints:
    - `GET /api/v2/deals/installments`
    - `POST /api/v2/deals/{id}/installments`
    - `PATCH /api/v2/deals/{id}/installments/{installment_id}`
    - `DELETE /api/v2/deals/{id}/installments/{installment_id}`
- Removed beta labels from v2 product images endpoints:
    - `GET /api/v2/products/{id}/images`
    - `POST /api/v2/products/{id}/images`
    - `PUT /api/v2/products/{id}/images`
    - `DELETE /api/v2/products/{id}/images`

## [15.1.1](https://github.com/pipedrive/client-php/compare/15.1.0...15.1.1) (2026-02-06)

### Added
- Documented three new OAuth2 scopes for field management in v1 README:
  - `deal-fields:full` - Allows applications to create, read, update and delete deal fields
  - `product-fields:full` - Allows applications to create, read, update and delete product fields
  - `contact-fields:full` - Allows applications to create, read, update and delete person and organization fields

## [15.1.0](https://github.com/pipedrive/client-php/compare/15.0.0...15.1.0) (2026-01-27)

### Added
- Added `updated_since` query parameter to `GET /v1/leads` endpoint to filter leads by their `update_time`
- Added `updated_since` query parameter to `GET /v1/notes` endpoint to filter notes by their `update_time`

## [15.0.0](https://github.com/pipedrive/client-php/compare/14.9.0...15.0.0) (2026-01-27)

### Removed
- Removed deprecated bulk delete endpoints:
  - `DELETE /v1/activities`
  - `DELETE /v1/activityTypes`
  - `DELETE /v1/deals`
  - `DELETE /v1/organizations`
  - `DELETE /v1/persons`
  - `DELETE /v1/stages`
Please use the single delete endpoints instead, for example `DELETE /api/v2/activities/{id}`.

## [14.9.0](https://github.com/pipedrive/client-php/compare/14.8.0...14.9.0) (2026-01-27)

### Added
- Added three new visibility flags to `ui_visibility` in organization fields v2 endpoints: `org_detail_visible_flag`, `person_detail_visible_flag`, and `deal_detail_visible_flag`

## [14.8.0](https://github.com/pipedrive/client-php/compare/14.7.1...14.8.0) (2026-01-27)

### Added
- Added `smart_bcc_email` to optional `include_fields` in Persons and Organizations API v2

## [14.7.1](https://github.com/pipedrive/client-php/compare/14.7.0...14.7.1) (2026-01-27)

### Added
- Added `subpremise` field to address structures in API v2 (`ActivityItemLocation`, `OrganizationItemAddress`, `PersonItemPostalAddress`) to support apartment/suite numbers

## [14.7.0](https://github.com/pipedrive/client-php/compare/14.6.1...14.7.0) (2026-01-27)

### Added
- Added `GET /api/v2/persons/{id}/picture` endpoint to retrieve picture information for a specific person, including ID, file size, and URLs for both 128x128 (thumbnail) and 512x512 (full-size) versions of the person's profile picture

## [14.6.1](https://github.com/pipedrive/client-php/compare/14.6.0...14.6.1) (2026-01-27)

### Fixed
- Removed `is_selected` from `/api/v2/pipelines` response, the field was never returned

## [14.6.0](https://github.com/pipedrive/client-php/compare/14.5.0...14.6.0) (2025-12-05)

### Added
- Added three new OAuth2 scopes for field management:
  - `deal-fields:full` - Allows applications to create, read, update and delete deal fields
  - `product-fields:full` - Allows applications to create, read, update and delete product fields
  - `contact-fields:full` - Allows applications to create, read, update and delete person and organization fields

## [14.5.0](https://github.com/pipedrive/client-php/compare/14.4.0...14.5.0) (2025-12-02)

### Added
- Documented the following new v2 endpoints:
  - `GET /api/v2/activityFields`
  - `GET /api/v2/dealFields`
  - `GET /api/v2/organizationFields`
  - `GET /api/v2/personFields`
  - `GET /api/v2/productFields`
  - `GET /api/v2/activityFields/{field_code}`
  - `GET /api/v2/dealFields/{field_code}`
  - `GET /api/v2/organizationFields/{field_code}`
  - `GET /api/v2/personFields/{field_code}`
  - `GET /api/v2/productFields/{field_code}`
  - `POST /api/v2/dealFields`
  - `POST /api/v2/organizationFields`
  - `POST /api/v2/personFields`
  - `POST /api/v2/productFields`
  - `DELETE /api/v2/dealFields/{field_code}`
  - `DELETE /api/v2/organizationFields/{field_code}`
  - `DELETE /api/v2/personFields/{field_code}`
  - `DELETE /api/v2/productFields/{field_code}`
  - `POST /api/v2/dealFields/{field_code}/options`
  - `POST /api/v2/organizationFields/{field_code}/options`
  - `POST /api/v2/personFields/{field_code}/options`
  - `POST /api/v2/productFields/{field_code}/options`
  - `PATCH /api/v2/dealFields/{field_code}/options`
  - `PATCH /api/v2/organizationFields/{field_code}/options`
  - `PATCH /api/v2/personFields/{field_code}/options`
  - `PATCH /api/v2/productFields/{field_code}/options`
  - `DELETE /api/v2/dealFields/{field_code}/options`
  - `DELETE /api/v2/organizationFields/{field_code}/options`
  - `DELETE /api/v2/personFields/{field_code}/options`
  - `DELETE /api/v2/productFields/{field_code}/options`

## [14.4.0](https://github.com/pipedrive/client-php/compare/14.3.3...14.4.0) (2025-11-25)

### Fixed
- API v2 configuration for the host base path: `/api/v2` instead of `/v2`. With this, API v1 configuration also changed from `/v1` to `/api/v1`, but this does not break backward compatibility as both `/api/v1` and `/v1` paths are supported for API v1.

### Added

- Added `POST /products/{id}/duplicate` endpoint for duplicating an existing product
- Added `deal_id` query parameter to the GET `/v2/persons` endpoint
- Added `GET /v1/leadFields` endpoint for fetching all lead fields

## [14.3.3](https://github.com/pipedrive/client-php/compare/14.3.2...14.3.3) (2025-11-18)

### Fixed
- Prepared `nullable: true` to fields that can return null values in schemas for Field and Roles schemas

## [14.3.2](https://github.com/pipedrive/client-php/compare/14.3.1...14.3.2) (2025-11-18)

### Fixed
- Fixed return type for `filesApi.downloadFile` function

## [14.3.1](https://github.com/pipedrive/client-php/compare/14.3.0...14.3.1) (2025-11-18)

### Fixed
- Corrected `FieldResponse` schema to use `GetField` instead of `Field` to ensure GET responses accurately reflect all field types returned by the API

## [14.3.0](https://github.com/pipedrive/client-php/compare/14.2.1...14.3.0) (2025-10-06)

### Added
- Added `is_archived` field in search response schemas for deals and leads

## [14.2.1](https://github.com/pipedrive/client-php/compare/14.2.0...14.2.1) (2025-10-03)

### Fixed
- Fixed PHP SDK v2 Configuration to use correct `/v2` API endpoints after OAuth token refresh instead of defaulting to `/v1`

## [14.2.0](https://github.com/pipedrive/client-php/compare/14.1.0...14.2.0) (2025-10-03)

## [14.1.0](https://github.com/pipedrive/client-php/compare/14.0.0...14.1.0) (2025-10-03)

### Added
-   Added `POST /deals/{id}/products/bulk` endpoint for creating multiple deal products at once (max 100 per request)
-   Added `DELETE /deals/{id}/products` endpoint for deleting multiple deal products at once (max 100 per request)

## [14.0.0](https://github.com/pipedrive/client-php/compare/13.2.5...14.0.0) (2025-10-03)

### Changed
- Removed deprecated `person_name`, `organization_name`, and `email` lead fields from itemSearch endpoints

## [13.2.5](https://github.com/pipedrive/client-php/compare/13.2.3...13.2.5) (2025-10-03)

### Fixed
- Fixed incorrect description for max `limit` parameter in itemSearch v2 endpoint

## [13.2.3](https://github.com/pipedrive/client-php/compare/13.2.2...13.2.3) (2025-10-03)

## [13.2.2](https://github.com/pipedrive/client-php/compare/13.2.1...13.2.2) (2025-09-08)

### Fixed
- Fixed incorrect `custom_fields` schema implementations by using the proper shared definition from `custom-fields.yaml`

## [13.2.1](https://github.com/pipedrive/client-php/compare/13.2.0...13.2.1) (2025-09-08)

### Added
- Documented the `custom_fields` in /v2 api for create organization and update deal

## [13.2.0](https://github.com/pipedrive/client-php/compare/13.1.0...13.2.0) (2025-09-08)

### Added
- Documented the `name` property in the Webhooks API for the following endpoints:
  - `GET /v1/webhooks`
  - `POST /v1/webhooks`

## [13.1.0](https://github.com/pipedrive/client-php/compare/13.0.0...13.1.0) (2025-09-08)

### Added
- Add documentation for product images functionality:
  - `GET` `/api/v2/products/{id}/images` endpoint
  - `POST` `/api/v2/products/{id}/images` endpoint
  - `PUT` `/api/v2/products/{id}/images` endpoint
  - `DELETE` `/api/v2/products/{id}/images` endpoint

## [13.0.0](https://github.com/pipedrive/client-php/compare/12.0.2...13.0.0) (2025-09-08)

### Removed
- Removed all endpoints of the Subscriptions feature
  - GET /v1/subscriptions/{id} 
  - GET /v1/subscriptions/find/{dealId}
  - GET /v1/subscriptions/{id}/payments
  - POST /v1/subscriptions/recurring
  - POST /v1/subscriptions/installment
  - PUT /v1/subscriptions/recurring/{id}
  - PUT /v1/subscriptions/installment/{id}
  - PUT /v1/subscriptions/recurring/{id}/cancel
  - DELETE /v1/subscriptions/{id}

## [12.0.2](https://github.com/pipedrive/client-php/compare/12.0.1...12.0.2) (2025-07-07)

### Added
- Added `custom_fields` type to `deal`, `person`, `organization`, `product` schemas

## [12.0.1](https://github.com/pipedrive/client-php/compare/12.0.0...12.0.1) (2025-06-25)

### Fixed
- Removed not supported `add_time`, `update_time` and `stage_change_time` from `PATCH /v2/deals/:id` request body

## [12.0.0](https://github.com/pipedrive/client-php/compare/11.1.1...12.0.0) (2025-06-17)

### Removed
- Removed `deals_summary` from `GET /v1/stages/:id` and `GET /v1/pipelines/:id`
- Removed `totals_convert_currency` query parameter from `GET /v1/pipelines/:id`
- Removed `everyone` query parameter from `GET /v1/stages/:id`

## [11.1.1](https://github.com/pipedrive/client-php/compare/11.1.0...11.1.1) (2025-06-09)

### Added
- Documented `address` property in the Organization v2 API for add and update endpoints:
  - `POST /v2/organizations`
  - `PATCH /v2/organizations/{id}`

## [11.1.0](https://github.com/pipedrive/client-php/compare/11.0.0...11.1.1) (2025-05-26)
### Fixed
- Fixed the OAuth scopes of Deal to Lead conversion and Lead to Deal conversion endpoints
### Added
- Added `smart_bcc_email` to optional `include_fields` in Deals API v2
- Added `is_archived` to request bodies of POST `deals` endpoints and PUT/PATCH `/deals/:id` endpoints
- Added `archive_time` to request bodies of POST `deals` endpoints

## [11.0.0](https://github.com/pipedrive/client-php/compare/10.0.0...11.0.0) (2025-05-09)

### Changed
- **BREAKING CHANGE.** The API token is sent in the `x-api-token` header instead of `api_token` query parameter.
The API key configuration must be updated as follows:
```php
// previous versions
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// starting from this version
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');
```

## [10.0.0](https://github.com/pipedrive/client-php/compare/9.0.0...10.0.0) (2025-05-06)

### Fixed
- Fixed the OAuth scope of "Add a follower to a product" endpoint in API v2
### Added
- Added `creator_user_id` to responses of Activities v2 endpoints
- Added `marketing_status` documentation to Persons v2 endpoints
- Added archived deals/leads endpoints:
  - `GET /v1/deals/archived`
  - `GET /v2/deals/archived`
  - `GET /v1/deals/timeline/archived`
  - `GET /v1/deals/summary/archived`
  - `GET /v1/leads/archived`
- Added `is_archived` and `archive_time` properties to deals endpoints response
- Added option to use `sort_by=due_date` sorting for `GET /api/v2/activities`
- Added option to use `done=true/false` quick filter for `GET /api/v2/activities`
- Added `project_id` and `pinned_to_project_flag` query parameter to the GET `/notes` endpoint
- Added `project_id` and `pinned_to_project_flag` request bodies to the POST/PUT `/notes` endpoint
- Added `project_id` and `pinned_to_project_flag` to success responses of GET/POST/PUT
### Changed
- Deprecated `GET /v1/activities` in favor of `GET /api/v2/activities`
- Deprecated `GET /v1/activities/collection` in favor of `GET /api/v2/activities`
- Deprecated `GET /v1/activities/{id}` in favor of `GET /api/v2/activities/{id}`
- Deprecated `POST /v1/activities` in favor of `POST /api/v2/activities`
- Deprecated `PUT /v1/activities/{id}` in favor of `PATCH /api/v2/activities/{id}`
- Deprecated `DELETE /v1/activities/{id}` in favor of `DELETE /api/v2/activities/{id}`
- Deprecated `DELETE /v1/activities` in favor of `DELETE /api/v2/activities/{id}`
- Deprecated `GET /v1/deals` in favor of `GET /api/v2/deals`
- Deprecated `GET /v1/deals/collection` in favor of `GET /api/v2/deals`
- Deprecated `GET /v1/deals/{id}` in favor of `GET /api/v2/deals/{id}`
- Deprecated `GET /v1/deals/search` in favor of `GET /api/v2/deals/search`
- Deprecated `POST /v1/deals` in favor of `POST /api/v2/deals`
- Deprecated `PUT /v1/deals/{id}` in favor of `PATCH /api/v2/deals`
- Deprecated `DELETE /v1/deals/{id}` in favor of `DELETE /api/v2/deals/{id}`
- Deprecated `DELETE /v1/deals` in favor of `DELETE /api/v2/deals/{id}`
- Deprecated `GET /v1/deals/{id}/activities` in favor of `GET /api/v2/activities?deal_id={id}`
- Deprecated `GET /v1/deals/{id}/persons` in favor of `GET /api/v2/persons?deal_id={id}`
- Deprecated `GET /v1/persons` in favor of `GET /api/v2/persons`
- Deprecated `GET /v1/persons/collection` in favor of `GET /api/v2/persons`
- Deprecated `GET /v1/persons/{id}` in favor of `GET /api/v2/persons/{id}`
- Deprecated `GET /v1/persons/search` in favor of `GET /api/v2/persons/search`
- Deprecated `POST /v1/persons` in favor of `POST /api/v2/persons`
- Deprecated `PUT /v1/persons/{id}` in favor of `PATCH /api/v2/persons/{id}`
- Deprecated `DELETE /v1/persons/{id}` in favor of `DELETE /api/v2/persons/{id}`
- Deprecated `DELETE /v1/persons` in favor of `DELETE /api/v2/persons/{id}`
- Deprecated `GET /v1/persons/{id}/deals` in favor of `GET /api/v2/deals?person_id={id}`
- Deprecated `GET /v1/persons/{id}/activities` in favor of `GET /api/v2/activities?person_id={id}`
- Deprecated `GET /v1/organizations` in favor of `GET /api/v2/organizations`
- Deprecated `GET /v1/organizations/collection` in favor of `GET /api/v2/organizations`
- Deprecated `GET /v1/organizations/{id}` in favor of `GET /api/v2/organizations/{id}`
- Deprecated `GET /v1/organizations/search` in favor of `GET /api/v2/organizations/search`
- Deprecated `POST /v1/organizations` in favor of `POST /api/v2/organizations`
- Deprecated `PUT /v1/organizations/{id}` in favor of `PATCH /api/v2/organizations/{id}`
- Deprecated `DELETE /v1/organizations/{id}` in favor of `DELETE /api/v2/organizations/{id}`
- Deprecated `DELETE /v1/organizations` in favor of `DELETE /api/v2/organizations/{id}`
- Deprecated `GET /v1/organizations/{id}/deals` in favor of `GET /api/v2/deals?org_id={id}`
- Deprecated `GET /v1/organizations/{id}/activities` in favor of `GET /api/v2/activities?org_id={id}`
- Deprecated `GET /v1/organizations/{id}/persons` in favor of `GET /api/v2/persons?org_id={id}`
- Deprecated `GET /v1/products` in favor of `GET /api/v2/products`
- Deprecated `GET /v1/products/{id}` in favor of `GET /api/v2/products/{id}`
- Deprecated `GET /v1/products/search` in favor of `GET /api/v2/products/search`
- Deprecated `POST /v1/products` in favor of `POST /api/v2/products`
- Deprecated `PUT /v1/products/{id}` in favor of `PATCH /api/v2/products/{id}`
- Deprecated `DELETE /v1/products/{id}` in favor of `DELETE /api/v2/products/{id}`
- Deprecated `GET /v1/pipelines` in favor of `GET /api/v2/pipelines`
- Deprecated `GET /v1/pipelines/{id}` in favor of `GET /api/v2/pipelines/{id}`
- Deprecated `POST /v1/pipelines` in favor of `POST /api/v2/pipelines`
- Deprecated `PUT /v1/pipelines/{id}` in favor of `PATCH /api/v2/pipelines/{id}`
- Deprecated `DELETE /v1/pipelines/{id}` in favor of `DELETE /api/v2/pipelines/{id}`
- Deprecated `GET /v1/stages` in favor of `GET /api/v2/stages`
- Deprecated `GET /v1/stages/{id}` in favor of `GET /api/v2/stages/{id}`
- Deprecated `POST /v1/stages` in favor of `POST /api/v2/stages`
- Deprecated `PUT /v1/stages/{id}` in favor of `PATCH /api/v2/stages/{id}`
- Deprecated `DELETE /v1/stages/{id}` in favor of `DELETE /api/v2/stages/{id}`
- Deprecated `DELETE /v1/stages` in favor of `DELETE /api/v2/stages/{id}`
- Deprecated `GET /v1/itemSearch` in favor of `GET /api/v2/itemSearch`
- Deprecated `GET /v1/itemSearch/field` in favor of `GET /api/v2/itemSearch/field`
- Updated non archived deals/leads endpoint description to specify that following endpoints do not return archived items:
  - `GET /v1/deals`
  - `GET /v2/deals`
  - `GET /v1/deals/timeline`
  - `GET /v1/deals/summary`
  - `GET /v1/leads`
- Removed deprecated `archived_status` query parameter from leads endpoints

[9.0.0](https://github.com/pipedrive/client-php/compare/8.1.5...9.0.0) (2025-03-31)

### Changed
- Updated `item_price` field data type in products from integer -> number for products and deal products

### Fixed
- Added missing `partnership` value for user access apps list

### Added
- Notice informing the users of the upcoming Subscriptions API deprecation:
  - `GET /v1/subscriptions/{id}`
  - `GET /v1/subscriptions/find/{dealId}`
  - `GET /v1/subscriptions/{id}/payments`
  - `POST /v1/subscriptions/recurring`
  - `POST /v1/subscriptions/installment`
  - `PUT /v1/subscriptions/recurring/{id}`
  - `PUT /v1/subscriptions/installment/{id}`
  - `PUT /v1/subscriptions/recurring/{id}/cancel`
  - `DELETE /v1/subscriptions/{id}`

### Changed
- Updated Webhooks endpoints to v2, including available event_action and event_object properties

### Added
- Added `GET /v2/deals/installments` endpoint to fetch all installments added to a list of deals with cursor pagination
- Added `POST /v2/deals/{id}/installments` endpoint to add an installment to a deal
- Added `PATCH /v2/deals/{id}/installments/{installment_id}` endpoint to edit an installment
- Added `DELETE /v2/deals/{id}/installments/{installment_id}` endpoint to delete an installment

[8.1.5](https://github.com/pipedrive/client-php/compare/8.1.4...8.1.5) (2025-03-17)

### Added
- Added deal_id, person_id, org_id and lead_id quick filters for GET /api/v2/activities endpoint.
- Added AdditionalData to the v2 schemas:
  - deal
  - stage
  - pipeline
  - activity

## [8.1.4](https://github.com/pipedrive/client-php/compare/8.1.3...8.1.4) (2025-03-11)

### Added
-  Added AdditionalData object schema to GetOrganizations and GetPersons data

## [8.1.3](https://github.com/pipedrive/client-php/compare/8.1.2...8.1.3) (2025-03-05)

### Fixed
-  Fixed AdditionalData object schema

## [8.1.2](https://github.com/pipedrive/client-php/compare/8.0.1...8.1.2) (2025-02-10)

### Fixed
- Updated supported enum values for webhook types in the `GET /webhooks` endpoint

## [8.1.0](https://github.com/pipedrive/client-php/compare/8.0.0...8.1.0) (2025-02-06)

### Added

- Added "pipeline_id" query parameter to GET /api/v1/deals/summary endpoint

### Changed

- Updated list of lead label colors to include 'brown', 'dark-gray', 'orange', 'pink'

## [8.0.0](https://github.com/pipedrive/client-php/compare/7.1.0...8.0.0) (2025-01-30)

### Added

- Added compatibility for both API v1 and v2 endpoints. See migration guide for more details
- Add documentation for installments functionality:
  - `POST` `/v1/deals/{id}/products` endpoint
  - `PUT` `/v1/deals/{id}/products/{product_attachment_id}` endpoint
  - `DELETE` `/v1/deals/{id}/products/{product_attachment_id}` endpoint
  - `POST` `/v2/deals/{id}/products` endpoint
  - `PATCH` `/v2/deals/{id}/products/{product_attachment_id}` endpoint
  - `DELETE` `/v2/deals/{id}/products/{product_attachment_id}` endpoint
- Added the field "notes" to product prices in the body and response for v1 and v2
- Added the field "overhead_cost" to the product variation prices in the response for v1
- Added the field "direct_cost" to the product variation prices in the response and body for v2
- Add "custom_fields" query paremeter to GET /api/v2/products

### Changed

- Reduce maximum `limit` query param to 100 for the following endpoints
  - GET v1/files
  - GET v1/deals/{id}/files
  - GET v1/organizations/{id}/files
  - GET v1/persons/{id}/files
  - GET /v1/products/{id}/files

## [7.1.0](https://github.com/pipedrive/client-php/compare/7.0.0...7.1.0) (2024-09-12)

### Added
- Added `notes` field to Prices in:
  - `GET` and `POST` `/v2/products/{productId}/variations` endpoints
  - `PATCH` `/api/v2/products/{productId}/variations/{productVariationId}` endpoint
- Notice informing the users of the upcoming Activity Invites feature deprecation:
  - `POST` and `PUT` `/v1/activities` endpoints

### Fixed
- Fixed response schemas for GET `/v1/<entity>Fields` requests
- Added missing "description" parameter to "Add a product" and "Update a product" endpoints
- Added the missing "label_ids" field to the contacts endpoints.
- Added "filter_id" to GET /api/v2/deals documentation
- Updated default values for "billing_frequency_cycles", "billing_start_date" fields

## [7.0.0](https://github.com/pipedrive/client-php/compare/6.10.1...7.0.0) (2024-09-11)


### BREAKING CHANGE
- Removed request and response fields `duration` and `duration_unit` for all endpoints of deals and products using them

## [6.10.1](https://github.com/pipedrive/client-php/compare/6.10.0...6.10.1) (2024-06-07)

### Removed
- Cleanup unused company settings

## [6.10.0](https://github.com/pipedrive/client-php/compare/6.9.0...6.10.0) (2024-06-07)

### Added
Added `acv`, `mrr`, `arr`, `acv_currency`, `mrr_currency`, and `arr_currency` to the BaseDeal entity and the following endpoints' response:
- `GET`, `POST` `/v1/deals`
- `GET`, `PUT` `/v1/deals/{id}`
- `GET` `/v1/deals/timeline`
- `PUT` `/v1/deals/{id}/merge`
- `POST` `/v1/deals/{id}/duplicate`

## [6.9.0](https://github.com/pipedrive/client-php/compare/6.8.1...6.9.0) (2024-05-30)

### Fixed
- Fixed documentation: property `value` of Lead is nullable.

## [6.8.1](https://github.com/pipedrive/client-php/compare/6.8.0...6.8.1) (2024-05-29)

### Removed
- Removed mention of address autocompletion by Google in Add Activities endpoint and other entities(organization, person, deal) fields.

## [6.8.0](https://github.com/pipedrive/client-php/compare/6.7.0...6.8.0) (2024-05-22)

### Added
- Added `origin`, `origin_id`, `channel` and `channel_id` parameters representing the Source for Lead and Deal entity.

## [6.7.0](https://github.com/pipedrive/client-php/compare/6.6.0...6.7.0) (2024-05-17)

### Added

- Added documentation for new endpoints `/deals/{id}/changelog`, `/persons/{id}/changelog` and `/organizations/{id}/changelog`.
- Added `is_deleted` parameter for `/v1/users/*` responses.
- Added `billing_frequency`, `billing_frequency_cycles` and `billing_start_date` fields to
  - `GET`, `POST` and `PUT` `/v1/deals/{id}/products` endpoints
  - `GET`, `POST` and `PATCH` `/v2/deals/{id}/products` endpoints
  - `GET` `/v2/deals/products` endpoint
- Added `billing_frequency`, `billing_frequency_cycles` fields to
  - `GET`, `POST` and `PUT` `/v1/products` endpoints
  - `GET` `/v1/products/{id}` endpoint
  - `GET`, `POST` and `PATCH` `/v2/products` endpoints
  - `GET` `/v2/products/{id}` endpoints
- Added RawData trait to models to allow access to all response data objects

## [6.6.0](https://github.com/pipedrive/client-php/compare/6.5.1...6.6.0) (2024-04-02)

### Added
- Documentation for new endpoint `/deals/{id}/participantsChangelog`

## [6.5.1](https://github.com/pipedrive/client-php/compare/6.5.0...6.5.1) (2024-04-02)

### Added
- `won_time`, `lost_time` and `close_time` parameters for the `POST /v1/deals` and `PUT /v1/deals/{id}` endpoints

## [6.5.0](https://github.com/pipedrive/client-php/compare/6.4.0...6.5.0) (2024-04-02)

### Added
- Documentation for `/meetings/userProviderLinks` endpoints

## [6.4.0](https://github.com/pipedrive/client-php/compare/6.3.1...6.4.0) (2024-04-02)

### Added
- `lead_id` as an acceptable body parameter for the `POST /v1/callLogs` endpoint

## [6.3.1](https://github.com/pipedrive/client-php/compare/6.3.0...6.3.1) (2024-04-02)

### Fixed
- Mismatches of YAML responses in related_objects of some deal endpoints

## [6.3.0](https://github.com/pipedrive/client-php/compare/6.2.0...6.3.0) (2023-10-16)

- Add `Projects`, `ProjectTemplates` and `Tasks` public routes.

## [6.2.0](https://github.com/pipedrive/client-php/compare/6.1.3...6.2.0) (2023-10-11)

### Added
- Document `label` parameter for the deal, person, org entities.

## [6.1.3](https://github.com/pipedrive/client-php/compare/6.1.2...6.1.3) (2023-10-06)

### Fixed
- Incorrect type of `options` for the `POST and PUT /dealFields, /personFields and /organizationFields` endpoints

## [6.1.2](https://github.com/pipedrive/client-php/compare/6.1.1...6.1.2) (2023-09-12)

### Fixed
- Fixed incorrect response schema for the `GET /deals/{id}/products` endpoint

## [6.1.1](https://github.com/pipedrive/client-php/compare/6.1.0...6.1.1) (2023-09-12)

### Changed
- Added further details on behavior of `end_date` for `PUT /v1/subscriptions/recurring/{id}/cancel`

## [6.1.0](https://github.com/pipedrive/client-php/compare/6.0.0...6.1.0) (2023-09-05)

### Fixed

- Resolved namespace issues in `$openAPITypes` for `ProductAttachmentDetails`, `AddProductAttachmentDetails`, `BaseMailThread`, and `FieldTypeAsString` (#91) models

## [6.0.0](https://github.com/pipedrive/client-php/compare/5.1.0...6.0.0) (2023-08-30)

### Removed

- Removed request and response fields `sum_no_discount` for `GET /deals/{id}/products`, `POST /deals/{id}/products` and `PUT /deals/{id}/products/{product_attachment_id}`

## [5.1.0](https://github.com/pipedrive/client-php/compare/5.0.0...5.1.0) (2023-08-30)

### Fixed
- Fixed incorrect parameter descriptions for the `PUT /deals/{id}` endpoint

## [5.0.0](https://github.com/pipedrive/client-php/compare/5.0.0-beta.1...5.0.0) (2023-08-16)

Release a stable version of 5.0.0 which is a complete rewrite of the client-php.
With version 5 of the SDK, we have moved to an open-source SDK generator called [OpenAPI Generator](https://openapi-generator.tech/).

## [5.0.0-beta.1](https://github.com/pipedrive/client-php/compare/5.0.0-beta.0...5.0.0-beta.1) (2023-07-24)

### Changed
- Added list of searchable custom field types to persons, organizations, products and deals search endpoints descriptions.
- Changed `product_id` body parameter to be optional for Update Deal Product endpoint
- Schemas for `call-logs` as they only included `base-response` without additional properties in the response schema

## [5.0.0-beta.0](https://github.com/pipedrive/client-php/compare/4.0.10...5.0.0-beta.0) (2023-06-14)

### Changed
- Breaking switch to SDK generated with [openapi-generator](https://openapi-generator.tech/)
