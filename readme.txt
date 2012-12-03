=== Suffusion BuddyPress Pack ===
Contributors: sayontan
Donate link: http://www.aquoid.com/news/plugins/suffusion-buddypress-pack/
Tags: suffusion, buddypress, template
Requires at least: WP 3.1, BP 1.2.6, Suffusion 4.0.0
Tested up to: WP 3.4.2
Stable tag: trunk

A compatibility pack for the Suffusion WordPress theme with the BuddyPress plugin.

== Description ==

This plugin provides compatibility for the <a href='http://www.aquoid.com/news/themes/suffusion'>Suffusion</a> WordPress Theme
with BuddyPress. It is based on the <a href="http://wordpress.org/extend/plugins/bp-template-pack/">BuddyPress template pack</a>
for building the basic templates, except that the template markup has been modified to match Suffusion's markup.

== Installation ==

You can install the plugin through the WordPress installer under <strong>Plugins &rarr; Add New</strong> by searching for it,
or by uploading the file downloaded from here. Alternatively you can download the file from here, unzip it and move the unzipped
contents to the <code>wp-content/plugins</code> folder of your WordPress installation. You will then be able to activate the plugin.

== Frequently Asked Questions ==

= How is this plugin different from the BuddyPress Template Pack? =

The BuddyPress Template Pack is a generic starting point for making any theme compatible with BuddyPress. Since the markup is
significantly different between the BuddyPress Template Pack and the Suffusion theme, this plugin was built as a pre-packaged
extension for users who want to use Suffusion for BuddyPress.

== Changelog ==

= 1.13 =
*	Fixed a bug that was causing pagination issues.

= 1.12 =
*	Removed references to a file that no longer exists in BP 1.6

= 1.11 =
*	Fixed AJAX issues for BP 1.6

= 1.10 =
*	Added support for BP 1.6

= 1.06 =
*	Moved some function calls out of Suffusion to this plugin to keep Suffusion lighter.

= 1.05 =
*	Added a "function_exists" check for bp_is_active, otherwise the code is collapsing for BP 1.2.x.

= 1.04 =
*	Replaced call to deprecated is_site_admin() function with is_super_admin().
*	Fixed a minor bug that was causing member lists to show up with a dark background.
*	Added support for the new Photonique skin in Suffusion.
*	Some buttons were not appearing for member profiles etc. That has been fixed.
*	Some general stylesheet changes have been made to make the design look better, e.g. in the registration page.
*	Some forms were nested several levels deep - this has been fixed.

= 1.03 =
*	<a href='http://wordpress.org/extend/plugins/cubepoints/'>CubePoints</a> is now supported. Note that you also need <a href= 'http://wordpress.org/extend/plugins/cubepoints-buddypress-integration/'>CubePoints BuddyPress Integration</a> for this.
*	Support for BP 1.5 has been added. Note that if you are already using the plugin and you upgrade your BP installation to 1.5, you have to rebuild your files from the Suffusion BuddyPress Pack options.

= 1.01 =
Added support for <a href='http://wordpress.org/extend/plugins/buddypress-links/'>BuddyPress Links</a> and the
<a href='http://wordpress.org/extend/plugins/jet-event-system-for-buddypress/'>Jet Event System for BuddyPress</a> plugins.

= 1.00 =
New version created