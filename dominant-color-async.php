<?php

/*
Plugin Name: Dominant color async
Plugin URI: https://github.com/CreunaFI/dominant-color-async
Description: Calculate the dominant color for every image in WordPress, asynchronously
Author: Johannes Siipola
Version: 1.0.3
Author URI: https://siipo.la
Text Domain: dominant-color-async
*/

require 'vendor/autoload.php';

new \DominantColorAsync\DominantColorAsync(plugin_basename(__FILE__), plugin_dir_path(__FILE__));
