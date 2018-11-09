# Dominant color async

[![Build Status](https://travis-ci.com/CreunaFI/dominant-color-async.svg?branch=master)](https://travis-ci.com/CreunaFI/dominant-color-async)
 [![Packagist](https://img.shields.io/packagist/v/joppuyo/dominant-color-async.svg)](https://packagist.org/packages/joppuyo/dominant-color-async)

Calculate the dominant color for every image in WordPress, asynchronously

## What does it do?

Dominant color async calculates the dominant color for images in your WordPress media gallery. It will also tell you if your image has transparency, or not. You can access this information using the `dominant_color` and `has_transparency` post meta keys. This information will be calculated after you upload images in the media gallery.

## How is this different from Dominant Color plugin?

[Dominant Color](https://wordpress.org/plugins/dominant-color/) calculates the dominant color synchronously as you are uploading them. This can sometimes take a long time and lead to frustrating experience. Dominant color async will do this processing in the background using [WordPress Background Processing](https://github.com/A5hleyRich/wp-background-processing) library, leading to more fluid admin experience.

Dominant color async will also allow you to calculate color information for images missing it as a batch process. This makes it easy to integrate the plugin into an existing site.

One thing missing from this plugin is the "palette" functionality of Dominant Color which generates multiple dominant colors for single image and allows you to pick from one of these. If you need this functionality, then Dominant Color is a better choice.
