<?php

/*
Plugin Name: Dominant color async
Plugin URI:
Description:
Author: Johannes Siipola
Version: 0.0.1
Author URI:
Text Domain: dominant-color-async
Domain Path:
*/

require 'vendor/autoload.php';

new \DominantColorAsync\DominantColorAsync(plugin_basename(__FILE__), plugin_dir_path(__FILE__));
