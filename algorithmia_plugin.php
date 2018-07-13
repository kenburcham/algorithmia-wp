<?php
/*
Plugin Name: Algorithmia Intelligent Algorithms
Plugin URI: https://algorithmia.com
Description: Use AI to power your WordPress websites.
Text Domain: algo
Version: 1.0
Author: kenburcham
License: MIT
*/

if ( ! defined('ABSPATH')) exit;  // if direct access

define( 'ALGO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ALGO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once(ALGO_PLUGIN_DIR.'admin_page.php');
