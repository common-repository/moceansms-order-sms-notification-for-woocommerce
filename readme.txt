=== MoceanAPI Order SMS Notification for WooCommerce ===
Contributors: moceanapiplugin
Tags: mocean, sms, woocommerce, multivendor, order, wc, order notification, sms notification, notification,
Requires at least: 3.8
Tested up to: 6.2
WC requires at least: 2.6
WC tested up to: 5.2.2
Stable tag: 1.4.12
Requires PHP: 5.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A plugin to send SMS notification to both buyer and seller after an order is placed in WooCommerce. SMS notification can be sent on all order statuses as well as with customized contents.

== Description ==

### MoceanAPI WooCommerce Order SMS Notification ###

This is a plugin add-on for WooCommerce. But WooCommerce plugin is **not required** to access certain functionalities of our plugin. For example: Send SMS, Send Bulk SMS.

Our WooCommerce Order SMS Notification for Wordpress helps you to get your job done with minimal effort.

With our plugin, you can send SMS Order Notifications to your customers (buyer) and administrators (seller/staff) automatically whenever a new order has been placed.

Try for FREE. 20 trial SMS credits will be given upon registration. Additional SMS credits can be requested and is subject to approval by MoceanAPI.

### Key Features ###

➜ Notify seller whenever a new order is placed.
➜ Inform buyer the current order status / whenever order status is changed.
➜ All WooCommerce order statuses are supported (including Custom WooCommerce Order Statuses)
➜ SMS templates can be saved to reuse in the future.
➜ Placeholder tags are supported to personalize your message. For eg: [shop_name], [order_id], [order_amount], [order_status], [order_product], [payment_method], [bank_details]
➜ Custom checkout field added from Woo Checkout Field Editor Pro is supported.
➜ Notify vendor whenever there’s new order
➜ Notify vendor when sub order status changed
➜ Notify Admin when product stock is low

### Multivendor Plugins Supported / Integrated. ###

👉 [Woocommerce Product Vendors](https://woocommerce.com/products/product-vendors/)
👉 [MultivendorX formerly WC Marketplace](https://wordpress.org/plugins/dc-woocommerce-multi-vendor/)
👉 [WC Vendors Marketplace](https://wordpress.org/plugins/wc-vendors/)
👉 [WooCommerce Multivendor Marketplace (WCFM Marketplace)](https://wordpress.org/plugins/wc-multivendor-marketplace/)
👉 [Dokan](https://wordpress.org/plugins/dokan-lite/)
👉 [YITH WooCommerce Multi Vendor](https://wordpress.org/plugins/yith-woocommerce-product-vendors/)

You'll be able to Send SMS Order Notification to vendors & buyers

### Integrations Supported ###

* Supported Reservation / Booking / Appointment Plugins:

🥡 [Five Star Restaurant Reservations – WordPress Booking Plugin](https://wordpress.org/plugins/restaurant-reservations/).
🥡 [Booking Calendar | Appointment Booking | BookIt](https://wordpress.org/plugins/bookit/).
🥡 [Quick Restaurant Reservations](https://wordpress.org/plugins/quick-restaurant-reservations/).
🥡 [LatePoint - Appointment Booking & Reservation](https://codecanyon.net/item/latepoint-appointment-booking-reservation-plugin-for-wordpress/22792692)
🥡 [FAT Service Booking](https://codecanyon.net/item/fat-services-booking-automated-booking-and-online-scheduling/24214247)

You'll be able to **send booking notifications** to your customers when booking status changes.

* Supported Membership Plugins:

🧑‍🤝‍🧑 [ARMember – Membership Plugin](https://wordpress.org/plugins/armember-membership/)
🧑‍🤝‍🧑 [MemberMouse](https://membermouse.com/)
🧑‍🤝‍🧑 [MemberPress](https://memberpress.com/)
🧑‍🤝‍🧑 [S2Members](https://wordpress.org/plugins/s2member/)
🧑‍🤝‍🧑 [Simple Membership](https://wordpress.org/plugins/simple-membership)

You'll be able to send membership notification & reminders to your members.

* Supported CRM Plugins:

💰 [Jetpack CRM](https://wordpress.org/plugins/zero-bs-crm/)
💰 [Groundhogg CRM](https://wordpress.org/plugins/groundhogg/)
💰 [Fluent CRM](https://wordpress.org/plugins/fluent-crm/)
💰 [WP ERP CRM](https://wordpress.org/plugins/erp/)

You'll be able to send notifications to your contacts / leads when their status changed.

* Supported Forms Plugins:
📝 [Contact Form 7](https://wordpress.org/plugins/contact-form-7/).

You'll be able to send notifications to customer when a new form is submitted.

* Compatibility

*   [Custom Order Status for WooCommerce](https://wordpress.org/plugins/custom-order-statuses-woocommerce/)
*   [Custom Order Statuses for WooCommerce](https://wordpress.org/plugins/custom-order-statuses-for-woocommerce/)
*   [Ni WooCommerce Custom Order Status](https://wordpress.org/plugins/ni-woocommerce-custom-order-status/)
*   [Ultimate Member – User Profile, User Registration, Login & Membership Plugin](https://wordpress.org/plugins/ultimate-member/)
*   [Members – Membership & User Role Editor Plugin](https://wordpress.org/plugins/members/)
*   [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/)

== Installation ==

1. Search for "MoceanAPI Order SMS Notification for WooCommerce" in "Add Plugin" page and activate it.

2. Configure the settings in Settings > MoceanAPI WooCommerce.

3. Enjoy.

== Screenshots ==

1. MoceanAPI Settings
2. Admin Settings
3. Customer Settings
4. Multivendor Settings

== Changelog ==

= 1.4.12 =
* Added a new keyword [order_latest_cust_note]
* order_latest_cust_note will display the latest customer note (public) from WooCommerce order in SMS content.

= 1.4.11 =
* Added option to send to Shipping recipient

= 1.4.10 =
* Default country will be automatically selected based on IP address if there's no previously selected default country

= 1.4.9 =
* Added validation check for sender id

= 1.4.8 =
* Updated readme

= 1.4.7 =
* Tested on wordpress v6.2
* Minor bugfix

= 1.4.6 =
* Added integration for Contact Form 7
* You can now send SMS to visitor and admin upon contact form submission

= 1.4.5 =
* Reworded WC marketplace to MultivendorX

= 1.4.4 =
* Minor bug fix for WP footer displaying at wrong position

= 1.4.3 =
* Added support for AR Member Premium

= 1.4.2 =
* Added a minor log for debugging purpose

= 1.4.1 =
* Keyword modal clicked but no UI displayed and Woocommerce Multivendor Marketplace automations are working
* Removed a log line
* Added XSS sanitization in Customer Logs
* Combined log file into 1 (MoceanSMS_Multivendor into MoceanSMS)

= 1.4.0 =
* Supports WooCommerce custom order status

= 1.3.13 =
* Changed mocean-medium to follow fr in registration link

= 1.3.12 =
* Added Freemius integration and Yandex analytics

= 1.3.11 =
* Standardized API Endpoint to validate mobile number based on country code

= 1.3.10 =
* Added automation to check scheduled task when updating to new version

= 1.3.9 =
* Fixed timeout when validating phone number with country code when domain is not reachable.

= 1.3.8 =
* Minor fix to get domain reachability

= 1.3.7 =
* Minor fix to get domain reachability

= 1.3.6 =
* Scheduled a task to check for domain reachability every day, will use IP Address if domain reachability failed.
* Settings from previous version will automatically be migrated to new version.
* You no longer need to press "Restore Settings" to restore settings, it will be done automatically when you update the plugin.

= 1.3.5 =
* Added new tab Migration to restore settings from previous version
* Added option to restore settings from previous version when updating plugin prior to version 1.3 to version 1.3

= 1.3.4 =
* Fixed issue when hosting service provider could not resolve our API URL.

= 1.3.3 =
* Fixed compatibility issue when upgrading wordpress to v5.9

= 1.3.2 =
*  Updated readme

= 1.3.1 =
* New - Added integration for new membership plugin
*  In Simple Membership plugin, you can send SMS notification when:
*  Member's membership is cancelled or expired
*  Member's recurring payment is successful

= 1.3.0 =
* New - Added integrations for CRM plugins
*     In Jetpack CRM, you can Send SMS notifications when:
*  There's a new contact with status "Customer"
*  There's a new contact with status "Lead"
*  There's a new contact with status "Refused"
*  There's a new contact with status "Blacklisted"
*     In Fluent CRM, you can Send SMS notifications when:
*  A contact status changed to subscribed
*  A contact status changed to unsubscribed
*  A contact status changed to pending
*     In Groundhogg CRM, you can Send SMS notifications when:
*  A contact status changed to confirmed
*  A contact status changed to unconfirmed
*  A contact status changed to unsubscribed
*     In WP ERP CRM, you can Send SMS notifications when:
*  There's a new contact with status "Customer"
*  There's a new contact with status "Lead"
*  There's a new contact with status "Opportunity"
*  There's a new contact with status "Subscriber"

= 1.2.1 =
*  Bug fix for sending SMS manually
* New - Notify admin when product stock is low

= 1.2.0 =
* New - Send SMS by:
*  all users
*  specific users
*  specific phone numbers
*  specific group of people (filter based on roles & country)
* New - Automatically send sms to user when an event is triggered (eg: a reservation is confirmed)
*  In ARMember, you can Send SMS notifications when:
*  User's subscription cancelled
*  User's membership plan changed
*  User's plan renewed
*     In BookIt, you can Send SMS notifications when:
*  An appointment is approved
*  An appointment is pending
*  An appointment is cancelled
*     In FAT Service Appointment, you can send SMS notification when:
*  An appointment is cancelled
*  An appointment is approved
*  An appointment is pending
*  An appointment is rejected
*     In LatePoint Appointment Booking & Reservation, you can send SMS notifications when:
*  An appointment is approved
*  An appointment is pending approval
*  An appointment is pending payment
*  An appointment is cancelled
*     In MemberMouse, you can send SMS notifications when:
*  Member's membership changed
*  Member's status changed
*  Bundles are added to member
*  Bundles status changed
*  Payment received
*  Payment rebill
*  Payment rebill declined
*  Refunds issued
*     In MemberPress, you can send SMS notifications when:
*  Transaction completed
*  Transaction expired
*  Transaction pending
*  Transaction failed
*  Transaction refunded
*  Subscription paused
*  Subscription resumed
*  Subscription stopped
*     In Quick Restaurant Reservation, you can send SMS notifications when:
*  Reservation is pending
*  Reservation is confirmed
*  Reservation is rejected
*  Reservation is cancelled
*     In Five Star Restaurant Reservation, you can send SMS notifications when:
*  Reservation is pending
*  Reservation is confirmed
*  Reservation is closed
*     In S2Member, you can send SMS notifications when:
*  There's new subscription
*  Payment received
*  Payment modification
*  End of term or expired
*  Refunds or reversal
* New - Send SMS Reminders before reservation date or membership expiry date
* New - Added SMS Outbox
* New - Added Help Page

= 1.1.18 =
* New - admin is able to receive sms notification base on selected order status

= 1.1.17 =
* New - add "Default country" selection in MoceanAPI SMS Settings page. Selected country will be use as default country info for mobile number when country info is not provided.

= 1.1.16 =
* New - add "Enable Suborder SMS Notification" function for customer and admin at WC_Marketplace, Dokan and YITH marketplace
* Fix - Admin, Customer & vendor receive both Main Order & Sub Order notification at Dokan and YITH marketplace

= 1.1.15 =
* Fix - Non multi vendor not able to send sms notification

= 1.1.14 =
* Fix - Admin, Customer & vendor receive both Main Order & Sub Order notification

= 1.1.13 =
* Fix - Multivendor unable to replace custom field

= 1.1.12 =
* Show additional custom billing fields in Keyword table

= 1.1.11 =
* Fix - unable to get correct vendor phone number

= 1.1.10 =
* Fix - dashboard balance widget randomly crash site.

= 1.1.9 =
* Fix - change mocean api url to latest url.

= 1.1.8 =
* Tweaks - add error logging on client side errors.

= 1.1.7 =
* New - admin is now able to customize send notification to multivendor on specific order status. (Default to all order status)

= 1.1.6 =
* Fix - unable to send sms in some case.

= 1.1.5 =
* HotFix - js file which has been cached not being updated end up prevent user from clicking keywords button.

= 1.1.4 =
* New - add new keywords [shop_email], [shop_url], [order_product_with_qty]
* New - add keywords modal for message template compose area

= 1.1.3 =
* Fix - plugin require pluggable.php which might accidentally declare wp_mail() used by other plugin.

= 1.1.2 =
* Changes - customer is not allowed to setup vendor phone number.
* Tweaks - add helper message to multivendor setting for better understanding.
* Tweaks - profile setting phone phone is now listed in MoceanSms Woocommerce section.

= 1.1.1 =
* New - add export log button in setting page.

= 1.1.0 =
* New - this plugin currently included ability to send sms to vendors.
* Tweaks - code improvement, plugin updates and installation is being speed up to 30%.

= 1.0.10 =
* New - add auto detect multivendor extension

= 1.0.9 =
* Fix - users are now not required to press save in multivendor setting for the first time to take effect.

= 1.0.8 =
* New - add abstraction for multivendor extensions.
* New - add moceansms balance widget.
* Fix - api secret field is now using password field.
* Fix - plugin still executing if api secret field is empty.
* Tweaks - code improvement.
* Tweaks - add log if api key or api secret is not defined.

= 1.0.7 =
* Code improvement.

= 1.0.6 =
* Update and improve library.

= 1.0.5 =
* Replace deprecated functions.

= 1.0.4 =
* Rectified warning message: wp_enqueue_script was called incorrectly.

= 1.0.3 =
* Added new tags: Ordered product, payment method, billing first name, last name, phone number, email, company, address, country, city, state, and postcode.
* Added new tags for custom checkout fields (Woo Checkout Field Editor Pro).

= 1.0.2 =
* Added new tag for bank details

= 1.0.1 =
* Added mobile number validation before sending SMS.
* Added form validation.
* Notice about SMS log file for checking when having issues sending SMS.

= 1.0.0 =
* Initial version released

== Upgrade Notice ==

= 1.1.0 =
This plugin included multivendor setting, kinda check on Settings > MoceanSMS WooCommerce > Multivendor Setting
