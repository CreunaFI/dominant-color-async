<?php

/*
Plugin Name: Dominant color async
Plugin URI: https://github.com/CreunaFI/dominant-color-async
Description: Calculate the dominant color for every image in WordPress, asynchronously
Author: Johannes Siipola
Version: 2.1.0
Author URI: https://siipo.la
Text Domain: dominant-color-async
*/

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    add_action('admin_notices', function () {
        $message = __("Dominant Color Async: Dependencies missing. If you just want to use this plugin, don't clone the Git repository, instead download the latest version on <a href='%s' target='_blank'>GitHub Releases</a>. If you want to develop the plugin, you will need to run <code>composer install</code>, <code>npm install</code> and <code>npx webpack -w</code> to get started.", 'dominant-color-async');
        $message = sprintf($message, 'https://github.com/CreunaFI/dominant-color-async/releases');
        echo "<div class='notice notice-error'><p>$message</p></div>";
    });
    return;
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

$dominant_color_async = new \DominantColorAsync\DominantColorAsync(
    plugin_basename(__FILE__),
    plugin_dir_path(__FILE__),
    __FILE__
);
