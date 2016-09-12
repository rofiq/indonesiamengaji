<?php

/*
Plugin Name: VideoPro - Sample Data
Description: Import sample data for VideoPro
Author: CactusThemes
Version: 1.1
Author URI: http://cactusthemes.com
*/

/**
 * @package NewsTube
 * @version 1.0
 */
 
defined( 'ABSPATH' ) or die( 'You cannot access this script directly' );

// can we read directory automatically? No, it is considered "unsecured"!!!
$packages = array('default', 'poster', 'cooking');
$base_dir = plugin_dir_path(__FILE__);
$base_uri = plugins_url('videopro-sampledata') . '/';

include 'cactus_importer.php';
$cactus_importer = new cactus_demo_importer($packages, $base_dir, $base_uri);