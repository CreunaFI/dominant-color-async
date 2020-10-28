<?php

/*
Plugin Name: Dominant color async
Plugin URI: https://github.com/CreunaFI/dominant-color-async
Description: Calculate the dominant color for every image in WordPress, asynchronously
Author: Johannes Siipola
Version: 2.0.1
Author URI: https://siipo.la
Text Domain: dominant-color-async
*/

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

$dominant_color_async = new \DominantColorAsync\DominantColorAsync(
    plugin_basename(__FILE__),
    plugin_dir_path(__FILE__),
    __FILE__
);
