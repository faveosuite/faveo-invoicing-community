# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
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

- Added “pipeline_id” query parameter to GET /api/v1/deals/summary endpoint

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
- Added the field “notes” to product prices in the body and response for v1 and v2
- Added the field “overhead_cost” to the product variation prices in the response for v1
- Added the field “direct_cost” to the product variation prices in the response and body for v2
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
