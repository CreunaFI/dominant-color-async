# Dominant color async

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/CreunaFI/dominant-color-async/Build?logo=github)](https://github.com/CreunaFI/dominant-color-async/actions)
[![GitHub release (latest by date)](https://img.shields.io/github/v/release/CreunaFI/dominant-color-async?logo=github)](https://github.com/CreunaFI/dominant-color-async/releases)


Calculate the dominant color for every image in WordPress, asynchronously

## What does it do?

Dominant color async calculates the dominant color for images in your WordPress media gallery. It will also tell you if your image has transparency, or not. You can access this information using the `dca_get_image_dominant_color` and `dca_get_image_transparency` post meta keys. This information will be calculated after you upload images in the media gallery.

## Getting dominant color

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

## Getting transparency

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

## How is this different from Dominant Color plugin?

[Dominant Color](https://wordpress.org/plugins/dominant-color/) calculates the dominant color synchronously as you are uploading them. This can sometimes take a long time and lead to frustrating experience. Dominant color async will do this processing in the background using [Action Scheduler](https://actionscheduler.org/) library, leading to more fluid admin experience.

Dominant color async will also allow you to calculate color information for images missing it as a batch process. This makes it easy to integrate the plugin into an existing site.

One thing missing from this plugin is the "palette" functionality of Dominant Color which generates multiple dominant colors for single image and allows you to pick from one of these. If you need this functionality, then Dominant Color is a better choice.

## Requirements

* WordPress 5.0+
* PHP 7.0+

## Installation

1. Download latest version on [GitHub releases](https://github.com/CreunaFI/dominant-color-async/releases).
2. Upload zip in WordPress admin plugins page or unzip in the `wp-plugins` directory
3. See the plugin settings page under Media
