# Change Log

Changelog for Razorpay-PHP SDK. Follows [keepachangelog.com](https://keepachangelog.com/en/1.0.0/) for formatting.

## Unreleased

## [2.9.0] - 2023-12-18
feat: Added new API endpoints

* Added support for `addBankAccount`, `deleteBankAccount`, `requestEligibilityCheck` & `fetchEligibility` on customer
* Added support for `uploadAccountDoc` & `fetchAccountDoc` on account
* Added support for [Dispute](https://razorpay.com/docs/api/disputes/)
* Added support for [Document](https://razorpay.com/docs/api/documents/)
* Added support for fetch all IINs Supporting native otps & fetch all IINs with business sub-type using `all`
* Added support for `viewRtoReview` & `editFulfillment` on order
* Added support for fetch a payment (emi/ offer/ card/ upi) using `expandedDetails` on payments
* Added support for `uploadStakeholderDoc` & `fetchStakeholderDoc` on stakeholder 


## [2.8.7] - 2023-09-11
[#357](https://github.com/razorpay/razorpay-php/pull/357) [`b29754f`](https://github.com/razorpay/razorpay-php/commit/b29754f8892e0c2035055cf73fd7ab132de18e52) Chore: Changed Content-Type `application/json` for `order create` API from default `application/x-www-form-urlencoded`


## [2.8.6] - 2023-06-16
[#348](https://github.com/razorpay/razorpay-php/pull/348) [`68b2028`](https://github.com/razorpay/razorpay-php/commit/68b2028bafda49af970a098d6d11aa8e5a575d40) feat: Added new API endpoints

* Added account onboarding API (create, fetch, edit, delete)
* Added stakeholders API (create, fetch, fetchAll, edit)
* Added product configuration API (requestProductConfiguration, fetch, edit, fetchTnc)
* Added webhooks API (create, fetch, fetchAll, edit, delete)
* Added token sharing API (create, fetch, delete, processPaymentOnAlternatePAorPG)

## [2.8.5] - 2022-10-19

### Added
- Update [Request](https://github.com/WordPress/Requests/tree/v2.0.4) library to v2.0.4

## [2.8.4] - 2022-06-28

- New APIs for Third party validation (createUpi, validateVpa, fetchPaymentMethods)
- Update documentation 

## [2.8.3] - 2022-04-29

- PHP v8.1 is officially supported
- Update [Request](https://github.com/WordPress/Requests/tree/v2.0.0) library to v2.0
- Improve documentation 
- Add PHPUnit v9

## [2.8.2] - 2022-03-08

- Change name convention to standard in Unit test 
- Removed test api key due to security concern from test cases

## [2.8.1] - 2021-11-08

### Added

- Added Item Api
- Added Unit Tests

## [2.8.0][2.8.0] - 2021-10-07

### Added
- QR code end point API [[#235](https://github.com/razorpay/razorpay-php/pull/235)]
- Update, cancel, create subscription link,fetch details of a Pending Update,cancel, pause and resume subscription API[[#236](https://github.com/razorpay/razorpay-php/pull/236)]
- Smart Collect(Virtual Account) TPV API's [[#238](https://github.com/razorpay/razorpay-php/pull/238)]
- Add/Delete TPV Bank Account [[#239](https://github.com/razorpay/razorpay-php/pull/239)]
- Card end point api [[#240](https://github.com/razorpay/razorpay-php/pull/240)]
- Route end point api [[#241](https://github.com/razorpay/razorpay-php/pull/241)]
- Register emandate and charge first payment together [[#245](https://github.com/razorpay/razorpay-php/pull/245)]
- PaperNACH/Register NACH and charge first payment together [[#246](https://github.com/razorpay/razorpay-php/pull/246)]
- Added payment and Settlements methods [[#247](https://github.com/razorpay/razorpay-php/pull/247)]
- Added edit and notify API's for payment links [[#248](https://github.com/razorpay/razorpay-php/pull/248)]
- Added fetch, fetch multiple refund,edit and notify API's for refunds [[#250](https://github.com/razorpay/razorpay-php/pull/250)]
- Added edit order API [[#251](https://github.com/razorpay/razorpay-php/pull/251)]
- Fund API's end point [[#252](https://github.com/razorpay/razorpay-php/pull/252)]
- UPI [[#253](https://github.com/razorpay/razorpay-php/pull/253)]
- Added payment link paymentverification [[#255](https://github.com/razorpay/razorpay-php/pull/255)]
- Update readme file [[#254](https://github.com/razorpay/razorpay-php/pull/254)]

## [2.7.1][2.7.1] - 2021-09-16

### Added

-   Added Payment Link end point API [[#233](https://github.com/razorpay/razorpay-php/pull/233)]

## [2.7.0][2.7.0] - 2021-05-07

### Added

-   Adds support for payment page enity API [[#224](https://github.com/razorpay/razorpay-php/pull/224)]

## [2.6.1][2.6.1] - 2021-04-30

### Changed

-   Upgrades [requests](https://github.com/rmccue/Requests/) to v1.8. [[#221](https://github.com/razorpay/razorpay-php/pull/221)]

## [2.6.0][2.6.0] - 2021-04-05

### Added

-   Adds support for webhook enity API [[#212](https://github.com/razorpay/razorpay-php/pull/212)]

## [2.4.0-beta][2.4.0-beta] - 2018-11-28

### Changed

-   Upgrades [requests](https://github.com/rmccue/Requests/) to v1.7. [[#89](https://github.com/razorpay/razorpay-php/pull/89)]
-   Enforces TLS1.1+ for all requests. Workaround for a bug in RHEL 6. [[#76](https://github.com/razorpay/razorpay-php/pull/76)]

## [2.3.0][2.3.0] - 2018-09-15

### Added

-   Add parameters to Subscription Cancel API
-   Support for fetching Settlements

## [2.2.1][2.2.1] - 2018-05-28

### Added

-   Support for fetching all customer entities

## [2.2.0][2.2.0] - 2017-10-23

### Added

-   Support for VirtualAccount entity
-   Support for Subscriptions

## [2.1.0][2.1.0] - 2017-10-10

### Added

-   Support for new actions(cancel, notifyBy, edit, issue, delete) on invoices
-   Removes PHP 5.3 from list of versions to test build against

## [2.0.2][2.0.2] - 2017-08-03

### Added

-   Support for creating and fetching Transfers
-   Support for creating Reversals on transfers

## [2.0.1][2.0.1] - 2017-07-31

### Fixed

-   Webhook signature verification
-   Conditional require of Request class

## [2.0.0][2.0.0] - 2017-03-07

### Added

-   Support for custom Application header
-   Support for card entity
-   Support for Webhook and Order Signature verification
-   Support for direct refund creation via Razorpay\Api\Refund::create()
-   Support for Utility functions via Razorpay\Api\Utility::verifyPaymentSignature and Razorpay\Api\Utility::verifyWebhookSignature
-   Support for case insensitive error codes
-   Support for 2xx HTTP status codes

### Changed

-   Razorpay\Api\Payment::refunds() now returns a Razorpay\Api\Collection object instead of Razorpay\Api\Refund object
-   Razorpay\Api\Api::$baseUrl, Razorpay\Api\Api::$key and Razorpay\Api\Api::$secret are now `protected` instead of `public`

## [1.2.9][1.2.9] - 2017-01-03

### Added

-   Support for creating and fetching Invoices

## [1.2.8][1.2.8] - 2016-10-12

### Added

-   Support for Customer and Token entities

## [1.2.7][1.2.7] - 2016-09-21

### Added

-   Increases the request timeout to 30 seconds for all requests.

## [1.2.6][1.2.6] - 2016-03-28

### Added

-   Adds better tracing when client is not able to recognize server response.

## [1.2.5][1.2.5] - 2016-03-28

### Added

-   Add support for overriding request headers via setHeader

## [1.2.3][1.2.3] - 2016-02-24

### Added

-   Add support for Orders

## [1.2.2][1.2.2] - 2016-02-17

### Changed

-   Razorpay\Api\Request::checkErrors is now `protected` instead of `private`
-   The final build is now leaner and includes just requests, instead of entire vendor directory

## [1.2.1][1.2.1] - 2016-01-21

### Added

-   Add version.txt in release with current git tag
-   This changelog file
-   `Api\Request::getHeaders()` method

## [1.2.0][1.2.0] - 2015-10-23

### Added

-   Add version string to User Agent

### Changed

-   New release process that pushes pre-packaged zip files to GitHub

## 1.0.0 - 2015-01-18

### Added

-   Initial Release

[unreleased]: https://github.com/razorpay/razorpay-php/compare/2.5.0...HEAD
[1.2.1]: https://github.com/razorpay/razorpay-php/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/razorpay/razorpay-php/compare/1.1.0...1.2.0
[1.2.2]: https://github.com/razorpay/razorpay-php/compare/1.2.1...1.2.2
[1.2.3]: https://github.com/razorpay/razorpay-php/compare/1.2.2...1.2.3
[1.2.4]: https://github.com/razorpay/razorpay-php/compare/1.2.3...1.2.4
[1.2.5]: https://github.com/razorpay/razorpay-php/compare/1.2.4...1.2.5
[1.2.6]: https://github.com/razorpay/razorpay-php/compare/1.2.5...1.2.6
[1.2.7]: https://github.com/razorpay/razorpay-php/compare/1.2.6...1.2.7
[1.2.8]: https://github.com/razorpay/razorpay-php/compare/1.2.7...1.2.8
[1.2.9]: https://github.com/razorpay/razorpay-php/compare/1.2.8...1.2.9
[2.0.0]: https://github.com/razorpay/razorpay-php/compare/1.2.9...2.0.0
[2.0.1]: https://github.com/razorpay/razorpay-php/compare/2.0.0...2.0.1
[2.0.2]: https://github.com/razorpay/razorpay-php/compare/2.0.1...2.0.2
[2.1.0]: https://github.com/razorpay/razorpay-php/compare/2.0.2...2.1.0
[2.2.0]: https://github.com/razorpay/razorpay-php/compare/2.1.0...2.2.0
[2.2.1]: https://github.com/razorpay/razorpay-php/compare/2.2.0...2.2.1
[2.3.0]: https://github.com/razorpay/razorpay-php/compare/2.2.1...2.3.0
[2.4.0-beta]: https://github.com/razorpay/razorpay-php/compare/2.3.0...2.4.0-beta
[2.5.0]: https://github.com/razorpay/razorpay-php/compare/2.4.0-beta...2.5.0
[2.8.0]: https://github.com/razorpay/razorpay-php/compare/2.7.1...2.8.0
[2.8.1]: https://github.com/razorpay/razorpay-php/compare/2.8.0...2.8.1
[2.8.2]: https://github.com/razorpay/razorpay-php/compare/2.8.0...2.8.2
