<?php

/*
Plugin Name: Dominant color async
Plugin URI: https://github.com/CreunaFI/dominant-color-async
Description: Calculate the dominant color for every image in WordPress, asynchronously
Author: Johannes Siipola
Version: 1.0.10
Author URI: https://siipo.la
Text Domain: dominant-color-async
*/

// Check if we are using local Composer
if (file_exists(__DIR__ . '/vendor')) {
    require 'vendor/autoload.php';
}

new \DominantColorAsync\DominantColorAsync(plugin_basename(__FILE__), plugin_dir_path(__FILE__));
