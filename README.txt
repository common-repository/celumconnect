=== celum:connect ===
Contributors: brixcrossmedia
Donate link: https://brix.ch/
Tags: dam, celum, brix, Asset Picker
Requires at least: 4.0
Tested up to: 5.5
Stable tag: 5.5
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https:/www.gnu.org/licenses/gpl-2.0.html

Celum:connect is a WordPress extension which allows you to download assets from CELUM via the CELUM Asset Picker directly into the WordPress filesystem.

== Description ==

WordPress:connect, an extension that integrates an "Asset Picker" allowing the selection of images and other media objects directly from CELUM, embedding them into the post and storing them in the media gallery of WordPress. This also enables you to link them in other posts later on. As an alternative to saving in WordPress, assets can only be linked from CELUM, so that they remain up to date at all times, even if they are adapted in CELUM.

To enable cross-references, the brix extension Direct Download is included as part of WordPress:connect. If an asset from CELUM is used via WordPress:connect, the usage (linked or downloaded assets) can be automatically written to an information field of the asset to mark it in CELUM with a "WordPress bullet", for example. With separate bullets can be distinguished whether an asset is linked in the CMS and deletion in CELUM DAM has an immediate effect, or is simply used there, which has no effect on deletion in CELUM DAM.

The API key of the CELUM user and the ID of the CELUM root node to be used as well as the "Asset Picker" version to be used can be entered via a separate WordPress settings dialog. Just as, the desired download formats for saving assets in WordPress can be defined for each media type. Finally, for the two types of use (download or link), a name can be defined, which will be set as a node-ref value for downloaded resp. linked assets. The related information field is defined in the configuration of the Direct Download extension.

Requires: CELUM CORA, 1 licensed API connection (for CELUM Asset Picker)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/celum_connect` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->celum:connect screen to configure the plugin

== Frequently Asked Questions ==

= Where can I configure the CELUM server I want to use? =

The url to the used CELUM server is encrypted in the license key. To get a license key for your CELUM server you need to contact brix cross media (https://brix.ch)

== Screenshots ==

1. Add a new Post
2. Asset Picker: Search
3. Asset Picker: Load asets from to WordPress or link them from Celum
4. Asset Picker: Select
5. Asset Picker: Set download format

== Changelog ==

= 1.0 =
* Initial verison

= 1.1 =
* Added tracking of downloaded Assets with "Direct Download"

= 1.2 =
* Added Possibility to choose between "Load assets to WordPress" and "Link assets from Celum"

= 1.3 =
* Added Support for different Asset Picker Versions (2.0 - 2.4), Selectable in the celum:connect Options
* Configurable usage for link and for download

= 1.4 =
* Asset Picker versions 2.5 and 2.5.2 added

= 1.4 =
* Asset Picker version 2.5.1 added

= 2.0 =
* Gutenberg block support added

= 2.2 =
* License with expiration date

= 2.3 =
* Asset Picker version 3.0 added

= 2.4 =
* AssetPicker version select bugfixing

= 2.5 =
* AssetPicker 3.0 Support approved (Config creation with no infofield possible)

= 2.6 =
* License need an expiration date

= 2.9 =
* Wordpress 5.5 compatibility
* Asset picker in media module as seperate tab

= 2.14 =
* Asset Picker version 6.8.11 and 6.9.3 added

= 2.17 =
* Asset Picker version 6.9.3 added

= 2.18 =
* Quickfix if Celum not defined (library not loaded)

= 2.19 =
* Fix media tab in custom post types

= 2.20 =
* Fix media tab in custom post types

= 2.21 =
* Featured image fix

= 2.22 =
* Check if WP Error

= 2.23 =
* Improved Error message

== Upgrade Notice ==

= 1.4 =
* Gives CELUM 6 compatibility

= 2.2 =
* Test licenses possible

= 2.3 =
* CELUM 6.2 Support

= 2.4 =
* Bugfixing

= 2.5 =
* AssetPicker 3.0 Support approved

= 2.6 =
* License need an expiration date

= 2.9 =
* Wordpress 5.5 compatibility
* Asset picker in media module as seperate tab

= 2.14 =
* Asset Picker version 6.8.11 and 6.9.3 added

= 2.15 =
* Link table feature in media tab

= 2.17 =
* Asset Picker version 6.9.3 added

= 2.18 =
* Fix if Celum not defined (library not loaded)

= 2.19 =
* Fix media tab in custom post types

= 2.20 =
* Fix media tab in custom post types

= 2.21 =
* Featured image fix

= 2.22 =
* Check if WP Error

= 2.23 =
* Improved Error message

= 2.24 =
* do not send empty consumer parameter in directDownload URL

= 2.25 =
* Use filename if not Content-Disposition

= 2.28 =
* Asset Picker 6.15.12, 6.16.14, 6.17.4, 6.18.4 and 6.19.1 added

= 2.29 =
* Asset Picker 6.11.9, 6.12.4, 6.13.10 and 6.14.12 added
