=== Dominant color async ===
Contributors: joppuyo
Tags: dominant, color, asynchronous
Requires at least: 4.9.0
Tested up to: 4.9.8
Requires PHP: 5.5.9
License: License: GPLv3 or later

Calculate the dominant color for every image in WordPress, asynchronously

== Description ==
Dominant color async calculates the dominant color for images in your WordPress media gallery. It will also tell you if your image has transparency, or not. You can access this information using the `dominant_color` and `has_transparency` post meta keys. This information will be calculated after you upload images in the media gallery.

== Installation ==
1. Upload the plugin folder to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress

== Frequently Asked Questions ==

= How is this different from Dominant Color plugin? =

[Dominant Color](https://wordpress.org/plugins/dominant-color/) calculates the dominant color synchronously as you are uploading them. This can sometimes take a long time and lead to frustrating experience. Dominant color async will do this processing in the background using [WordPress Background Processing](https://github.com/A5hleyRich/wp-background-processing) library, leading to more fluid admin experience.

Dominant color async will also allow you to calculate color information for images missing it as a batch process. This makes it easy to integrate the plugin into an existing site.

One thing missing from this plugin is the "palette" functionality of Dominant Color which generates multiple dominant colors for single image and allows you to pick from one of these. If you need this functionality, then Dominant Color is a better choice.

= How can I report an issue or contribute code? =

Please report issues and send pull requests on the [GitHub repo](https://github.com/CreunaFI/dominant-color-async).

== Changelog ==

= 1.0.9 =
* Check attachment exists before processing
* Install dependencies according to minimum PHP version requirements

= 1.0.8 =
* Update PHP requirement information

= 1.0.7 =
* Move var-dumper to dev dependencies

= 1.0.6 =
* Add Bedrock support

= 1.0.5 =
* Fix imagick support

= 1.0.4 =
* Use imagick if gd is not loaded

= 1.0.3 =
* Fix settings button path
* Improve dominant color calculation accuracy

= 1.0.2 =
* Update composer.json

= 1.0.1 =
* Update package.json

= 1.0.0 =
* Initial release
