# Dominant color async

Contributors: joppuyo
Tags: dominant, color, asynchronous
Requires at least: 5.0
Tested up to: 5.8
Requires PHP: 7.0.0
License: GPLv2 or later
  
Calculate the dominant color for every image in WordPress, asynchronously  
  
## Description

Dominant color async calculates the dominant color for images in your WordPress media gallery. It will also tell you if your image has transparency, or not. You can access this information using the `dca_get_image_dominant_color` and `dca_get_image_transparency` filters. This information will be calculated after you upload images in the media gallery.  

### Getting dominant color
  
```php  
$image_id = 123;

// Returns a hex code string or null if dominant color hasn't been calculated yet
$dominant_color = apply_filters('dca_get_dominant_color', null, $image_id);

if ($dominant_color !== null) {
    echo "Image dominant color: " . $dominant_color;
} else if ($dominant_color === null) {
    echo "Image dominant color hasn't been calculated yet.";
}
```  
  
### Getting transparency
  
```php  
$image_id = 123;

// Returns either true, false or null if transparency hasn't been calculated yet
$has_transparency = apply_filters('dca_get_transparency', null, $image_id);

// Note: use strict comparison here instead of a shorthand to differentiate between false and null
if ($has_transparency === true) {
    echo "Image has transparency";
} else if ($has_transparency === false) {
    echo "Image doesn't have transparency";
} else if ($has_transparency === null) {
    echo "Image transparency hasn't been calculated yet.";
}
```
  
## Installation

1. Upload the plugin folder to the /wp-content/plugins/ directory  
2. Activate the plugin through the Plugins menu in WordPress  
  
## Frequently Asked Questions 
  
### How is this different from Dominant Color plugin?
  
[Dominant Color](https://wordpress.org/plugins/dominant-color/) calculates the dominant color synchronously as you are uploading them. This can sometimes take a long time and lead to frustrating experience. Dominant color async will do this processing in the background using [Action Scheduler](https://actionscheduler.org/) library, leading to more fluid admin experience.  
  
Dominant color async will also allow you to calculate color information for images missing it as a batch process. This makes it easy to integrate the plugin into an existing site.  
  
One thing missing from this plugin is the "palette" functionality of Dominant Color which generates multiple dominant colors for single image and allows you to pick from one of these. If you need this functionality, then Dominant Color is a better choice.  
  
### How can I report an issue or contribute code?
  
Please report issues and send pull requests on the [GitHub repo](https://github.com/CreunaFI/dominant-color-async).  
  
## Changelog

### 2.1.0

* Feature: Add filters for getting dominant color and transparency  
* Fix: Fix error when ImageMagick is installed but GD is not  
* Fix: Updated compatibility to WordPress 5.8

### 2.0.4

* Fix: Update dependencies, again  
  
### 2.0.3

* Fix: Update dependencies  
  
### 2.0.2

* Fix: Add an admin notice instead of crashing if dependencies are missing  
  
### 2.0.1

* Fix: Fix fatal error due to wrong dependency version  
  
### 2.0.0 

* Breaking change: Minimum supported PHP version is now 7.0  
* Breaking change: Minimum supported WordPress version is now 5.0  
* Breaking change: Support for Packagist has been removed since Packagist does not support compiled assets like JavaScript or CSS unless they are committed to the Git repository. Please download the latest release from GitHub releases. Auto-updater is included in the plugin. If you need install the plugin using Composer, set up your own [SatisPress](https://github.com/cedaro/satispress) repository.  
* Fix: Don't show dominant color field on attachments that are not images  
* Change: Changed library used for async processing to woocommerce/action-scheduler instead of deliciousbrains/wp-background-processing since Action Scheduler is more actively supported and it handles large amount of actions better  
  
### 1.1.3 

* Fix: Make query more efficient  
  
### 1.1.2

* Fix: Fix build  
  
### 1.1.1

* Fix: Fix build  
  
### 1.1.0

* Feature: Save image hash in the database and skip processing the image if it has not changed  
* Fix: Fix issue where image with only one field processed does not show up as unprocessed  
  
### 1.0.11

* Fix: Fix issue where failed image processing can cause an infinite loop  
* Fix: Bump supported WordPress version to 5.1  
  
### 1.0.10 

* When processing all unprocessed images, chunk batches to avoid timeout on some servers  
  
### 1.0.9

* Check attachment exists before processing  
* Install dependencies according to minimum PHP version requirements  
  
### 1.0.8
* Update PHP requirement information  
  
### 1.0.7
* Move var-dumper to dev dependencies  
  
### 1.0.6

* Add Bedrock support  
  
### 1.0.5

* Fix imagick support  
  
### 1.0.4

* Use imagick if gd is not loaded  
  
### 1.0.3

* Fix settings button path  
* Improve dominant color calculation accuracy  
  
### 1.0.2
* Update composer.json  
  
### 1.0.1
* Update package.json  
  
### 1.0.0
* Initial release
