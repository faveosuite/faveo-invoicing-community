# Faveo Invoicing

[![StyleCI](https://styleci.io/repos/51429040/shield)](https://styleci.io/repos/51429040) [![License](https://img.shields.io/badge/License-OSL-blue.svg)](https://opensource.org/licenses/OSL)

**Faveo Invoicing is an open-source billing, subscription and software licensing platform for SaaS businesses, software companies and developers.**

It handles the full customer journey in one place: sign-up, checkout, payment, invoicing, license issuing, downloads, updates and renewals.

Developed by [Ladybird Web Solution Pvt. Ltd.](https://www.faveohelpdesk.com/) since **March 2016**.

---

## Key Features

### Billing and Subscriptions
- Products, plans and pricing in multiple currencies
- Cart, checkout, coupons and promotions
- Invoices, payments and account credits
- Recurring subscriptions with optional auto-renewal
- Configurable tax rates by country and state

### Built-in License Manager
Licensing is part of Faveo Invoicing. A separate license manager is no longer needed.
- Licenses are issued automatically when an order is placed
- Plugins (add-ons) can be linked to a product's licenses
- Product downloads and updates are signed and delivered per license
- Your own Laravel products check their license with the [Faveo License Package](https://github.com/faveosuite/faveo-license-package)

### Security
- Two-factor authentication (2FA) with recovery codes
- Google reCAPTCHA on public forms
- Email and mobile (OTP) verification at sign-up
- Social login: Google, GitHub, Twitter and LinkedIn

### Administration
- Customer, order and license management
- Reports with selectable columns and exports
- Activity, mail and error logs
- Available in 23 languages

---

## Integrations

| Category | Service |
|---|---|
| Payment gateways | [Stripe](https://stripe.com/), [Razorpay](https://razorpay.com/) |
| CRM | [Zoho CRM](https://www.zoho.com/crm/), [Pipedrive](https://www.pipedrive.com/) |
| Email marketing | [Zoho Campaigns](https://www.zoho.com/campaigns/), [Mailchimp](https://mailchimp.com/) |
| Email validation | [Reoon](https://emailverifier.reoon.com/) |
| OTP verification | [MSG91](https://msg91.com/) |
| Product releases | [GitHub](https://github.com/) |
| Spam protection | [Google reCAPTCHA](https://www.google.com/recaptcha/) |
| Error monitoring | [Sentry](https://sentry.io/) |

### Developer Resources

| Resource | Use it to |
|---|---|
| [Faveo License Package](https://github.com/faveosuite/faveo-license-package) | Add license checks, entitlements and safe updates to your Laravel product |
| [Faveo Invoicing WordPress Plugin](https://github.com/faveosuite/Faveo-Invoicing-Wordpress-Plugin) | Show Faveo Invoicing products and pricing on your WordPress site |

---

## Who Is It For?

- SaaS businesses running recurring subscriptions
- Software companies selling products and licenses
- Developers who want to license and update their own applications
- Teams that need invoicing, payments and customer management in one place

---

## Requirements

| Component | Version |
|---|---|
| PHP | 8.4+ |
| Laravel | 12+ |
| Database | MySQL 8.0 |
| Web server | Apache, Nginx or IIS, with pretty URLs enabled |
| PHP extensions | curl, gd, intl, mbstring, mysqli, pdo_mysql, xml, zip, sodium, pcntl, posix |

The other extensions Laravel needs are included with PHP by default.

## Installation

Installation is the same as for [Faveo Helpdesk](https://www.faveohelpdesk.com/). Follow the step-by-step [Faveo Installation Guide](https://docs.faveohelpdesk.com/).

## Documentation

- [Faveo Invoicing Wiki](https://github.com/faveosuite/faveo-invoicing-community/wiki): user manual
- [Features](https://github.com/faveosuite/faveo-invoicing-community/wiki/Faveo-Features): full feature list

---

## Error Reporting

Faveo Invoicing uses [Sentry](https://sentry.io/) to monitor stability in production, so we can find and fix issues quickly.

**Customizing in development?** Turn error reporting off under **Error Logs and Debugging** in the admin panel, or set `APP_ENV` to anything other than `production` in your `.env` file.

## Contributing

Report issues or share your changes on [GitHub Issues](https://github.com/faveosuite/faveo-invoicing-community/issues). Changes that follow our [contributing guidelines](https://github.com/faveosuite/faveo-invoicing-community/blob/development/CONTRIBUTING.md) may be merged into the main application.

## Security Policy

### Supported Versions

| Version   | Supported          |
|-----------|--------------------|
| 4.0.2.6   | :white_check_mark: |
| < 4.0.2.6 | :x:                |

Security updates are released monthly, or twice a month for critical issues.

### Reporting a Vulnerability

Email [support@faveohelpdesk.com](mailto:support@faveohelpdesk.com). We reply within 48 hours and, once confirmed, usually release a patch within a few days.

---

## Support

- **Email:** [support@faveohelpdesk.com](mailto:support@faveohelpdesk.com)
- **Custom development and commercial support:** [contact us](https://www.faveohelpdesk.com/contact-us/)

## License

Faveo Invoicing is open-source software licensed under the [Open Software License v3.0](LICENSE).
