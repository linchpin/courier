=== Courier Notices ===
Contributors: linchpin_agency, aware, ncallen, maxinacube, fischfood, desrosj
Tags: notifications, notices, alerts, linchpin, front end, dismissible, gdpr
Requires at least: 5.0
Tested up to: 5.4.2
Stable tag: 1.4.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add dismissible and non-dismissible notices throughout your website.

== Description ==

Add dismissible and non-dismissible notices throughout your website.

* Customize the colors and icons used by your Courier Notices **no coding needed**
* Add new "types" of notices as needed
* Comes with a common library of useful notices (Modal, Header, Footer)
* Place courier notices within the header, footer or any where using CSS selectors (similar to selectors in jQuery)
* Display popover/page take over modal style notices
* Can be used in conjunction with other popular plugins

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `courier-notices` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Does this plugin work with Full Page Caching or WordPress Object Caching? =

Yes, just be sure to enable the option to load Courier Notices using AJAX

= I'm looking for a specific type of notice that courier doesn't have, what's next? =

The great thing is that Courier Notices is highly extendable and our team is adding new features constantly since release. You can always create a pull request in GitHub or add an issue. You can also take a look at the Pro version which also has some great features.

== Screenshots ==

1. List of "Types" of Informational Courier Notices within the WordPress admin
2. Editing colors and icons of a Courier Notice
3. Frontend display of a few Courier Notices, including dismissible notices.
4. Frontend display of a modal Courier Notice.

== Changelog ==

= 1.3.1 =
* Fixed a javascript issue impacting other plugins
* Updated - All javascript dependencies have been updated ot the latest versions
* Updated - Minor security fixes

= 1.3.0 =
* Added - REST endpoint for saving settings changes (preparation for new UI in future versions)
* Added - Settings now utilize ajax/REST for updating settings
* Added - New Feature. You can now choose to display the title of your Courier Notice for all Styles of notices.
* Added - Added some FAQs to the readme.txt
* Added - FAQ.md that is updated and generated from the readme.txt
* Added - Some new screenshots were added so users can get a sense of what Courier Notices look like by default.
* Updated - Popup/Modal notices now only show 1 at a time!
* Fixed - Minor display issue for "Sub Tabs" within the Settings area of the WordPress admin
* Fixed - Cleaned up spacing of Headers and Sub Titles within Settings to make things easier to read.
* Fixed - Numerous nitpicky CSS things that you may or may not notice (margins, column spacing and more)
* Fixed - When adding a new "type" of "Informational" Courier notices, the color pickers were not initializing properly
* Fixed - Quality of life fix: Removed theme based "editor styles" on courier notices

= 1.2.7 =
* Updated - Window load for modal placement

= 1.2.6 =
* Fixed - Fixed issue with modal dialogs displaying multiple times due to intersectObserver

= 1.2.5 =
* Updated - Options namespace

= 1.2.4 =
* Fixed - Issue with 403 error "rest_cookie_invalid_nonce" for logged out users.
* Updated - Banner shown in the WordPress.org plugin directory.

= 1.2.3 =

* Fixed - issue with notice placement (whoops)

= 1.2.1 =

* Updated sanitization to match wordpress.org audit.

= 1.2.0 =

* Updated - Namespace changed from courier to courier-notices due to plugin conflict on wordpress.org
* Fixed   - Duplicate modal/popup issue
* Submission to wordpress.org

= 1.1.4 =

* Fixed - Fatal error when assigning data to a template view

= 1.1.3 =

* Fixed - Icon font specificity

= 1.1.2 =

* Remove - Notice font styles, allow styling to inherit from theme

= 1.1.1 =

* Fixed - Issue with default styles not being created on install
* Fixed - Security updates provided by github audit

= 1.1.0 =

* Fixed - Minor security updates
* Fixed - Minor code cleanup
* Fixed - Link to Types/Design was broken
* Fixed - Link to Settings was broken
* Fixed - Minor updates to strings to allow for translation
* Fixed - Modal notice was not working properly (dismissible)
* Fixed - Error log was being utilized and should not have been
* Fixed - Cron was running hourly and not every 5 minutes
* Fixed - Various typos (We talk pretty one day)
* Fixed - utilizing iris wpColorPicker (For the time being)
* Fixed - Fixed an issue with color changes in the design panel did not show until page refresh
* Added - New UI/UX for creating and styling "Types" of notices
* Added - Courier actually has some branding now
* Added - Default data on plugin activation
* Added - Utility method to sanitize kses content
* Added - Cleaned up CSS across the entire plugin
* Added - New cron schedule (Every 5 minutes)
* Added - New taxonomy for "Style of Notice". This will allow for all different kinds of notices in the future
* Added - Base for CRUD in the future. Mainly just R right now.
* Improved - Added more flexibility to how tabs and subtabs can extend the plugin
* Improved - CSS is only generated and output if CSS is not disabled
* Improved - Placement logic is more flexible now
* Improved - UI/UX to show different notice options depending on other selections
* Improved - How css and javascript is enqueued based on context of admin
* Improved - Code Organization
* Improved - Templates
* Improved - Updated the expiration of notices to increment every 5 minutes for better accuracy and less stress on servers

= 1.0.4 =

* Cleaned up deployment process further.

= 1.0.2 =

* Migrated to using composer as our autoloader instead of a proprietary one
* Added Parsedown dependency for Markdown display within the plugin
* Added a changelog.md display to the settings page as a tab
* Added more automation for release to get releases out the door quicker
* Minor code formatting changes

= 1.0.1 =

* Updated dependencies based on github security notification

= 1.0.0 =

Initial Release

* Cleaned up UI for date and time selection.
* You can no longer select an expiration date from the past.
* Implemented datetimepicker so time selection is easier.
* Minor typo fix in admin area.
* Minor data sanitization/security hardening.

== Shortcodes ==

`[courier_notice id="1"]`

Displays a Courier notice based on the Courier post ID, displays a flag to call out the notice as well.

Flag prepends a span containing the string in the flag parameter, to display no flag set "show_flag" to false.
