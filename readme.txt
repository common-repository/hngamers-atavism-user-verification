 === HNGamers Atavism User Verification ===
Contributors: thevisad
Tags: user verification, atavism online
Donate link: https://hngamers.com/support-and-thank-you/
Requires at least: 6.0
Tested up to: 6.4.1
Requires PHP: 7.4
Stable tag: 0.0.12
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This is the user verification script for Atavism Online and allows users to verify the username and password and also verify if the user has a subscription. 


== Description ==
This is the user verification script for Atavism Online and allows users to verify the username and password and also verify if the user has a subscription. This requires the HNGamers Atavism Core plugin for user account verification and the Paid Memberships Pro plugin for monthly subscription verification. 

Disable the plugin before updating, then enable it once it's been updated. 

== Frequently Asked Questions ==
How do I use the plugin or setup it up? 
Please follow the course here [Atavism CMS](https://hngamers.com/courses/development/atavism/atavism-wordpress-cms/)
Where do I get support?
All support is through the #cms-dev channel in the [Atavism Online discord](https://discord.gg/sEPQmtjg9N) make sure to ping thevisad in the channel. 

= I found a bug in the plugin. =
Please report all bugs on the #cms-dev channel in the [Atavism Online discord](https://discord.gg/sEPQmtjg9N)

== Screenshots ==


== Changelog ==
= 0.0.12 =
Updated to the new core handler. 

= 0.0.11 =
Correction to the filters
= 0.0.10 =
Removal of upgrade filters

= 0.0.9 =
Removed the test upgrade functionality as it was causing an unintended crash. 

= 0.0.8 =
Verified Woocommerce 8.1 and PHP 8.1 

= 0.0.7 =


= 0.0.6 =
Minor Corrections

= 0.0.5 =
Corrected issue with the mysql query that caused a duplicate call to be made. 

= 0.0.4 =
Adjusted the return values of the verify script to reflect upcoming changes to x.7

= 0.0.3 =
Add IP verification process to ensure that only the Atavism Server can reach out to the verify script. Must add the servers IP to the admin page, that will be calling the script in order to proceed. 

= 0.0.2 =
Minor corrections changes
Add ability to switch between user accounts and user emails for authentication.

= 0.0.1 =
* This is the initial version of the plugin.

== Upgrade Notice ==