=== Gravity Flow Checklists Extension ===
Contributors: stevehenty
Tags: gravity forms, approvals, workflow
Requires at least: 4.0
Tested up to: 5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Group forms into checklists for users to complete in sequence.

== Description ==

The Gravity Flow Checklists Extension is an extension for Gravity Flow.

Gravity Flow is a premium Add-On for [Gravity Forms](https://gravityflow.io/gravityforms)

= Requirements =

1. [Purchase and install Gravity Forms](https://gravityflow.io/gravityforms)
1. [Purchase and install Gravity Flow](https://gravityflow.io)
1. Wordpress 4.3+
1. Gravity Forms 2.1+
1. Gravity Flow 1.8+


= Support =
If you find any that needs fixing, or if you have any ideas for improvements, please get in touch:
https://gravityflow.io/contact/


== Installation ==

1.  Download the zipped file.
1.  Extract and upload the contents of the folder to /wp-contents/plugins/ folder
1.  Go to the Plugin management page of WordPress admin section and enable the 'Gravity Flow Checklists Extension' plugin.

== Frequently Asked Questions ==

= Which license of Gravity Flow do I need? =
The Gravity Flow Checklists Extension will work with any license of [Gravity Flow](https://gravityflow.io).


== ChangeLog ==

= 1.3 =
- Added translations for French, Portuguese, Italian, Swedish, Dutch, Turkish, German and Spanish.
- Fixed the form being marked as complete when a partial entry is saved.
- Fixed the incomplete submissions don't work properly with Gravity Forms 2.4.+.

= 1.2 =
- Added support for the license key constant GRAVITY_FLOW_CHECKLISTS_LICENSE_KEY
- Added the filter gravityflowchecklists_form_title to allow the display of form titles in checklist to be modified.
- Fixed some styles.

= 1.1 =
- Added the gravityflowchecklists_post_add_exemption and gravityflowchecklists_post_remove_exemption actions.
- Added the $user parameter to the gravityflowchecklists_checklists filter
- Fixed an issue where the spinner appears when attempting to exempt a form without permissions.
- Updated the font size of the meta information for submitted forms.
- Updated the checklist to display as complete only when the workflow of the final form is complete.
- Fixed an issue where the checklist does not display as complete if one of the forms is marked as exempt.


= 1.0 =
All new!
