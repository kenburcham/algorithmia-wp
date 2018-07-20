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

/*
function load_algo_scripts() {
    exit;
    wp_register_style( 'jquerymodalcss', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css' );
    wp_enqueue_style( 'jquerymodalcss' );
    wp_enqueue_script( 'jquerymodaljs', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', array( 'jquery' ) );
    //wp_enqueue_script( 'algojs', plugin_dir_url(__FILE__) .'algo.js', array( 'jquery' ) );
}

add_action('wp_enqueue_scripts', 'load_algo_scripts');
*/

require_once(ALGO_PLUGIN_DIR.'vendor/autoload.php'); //load algorithmia client
require_once(ALGO_PLUGIN_DIR.'admin_page.php');

$ALGO_APIKEY = get_option('algo_options')['algo_field_api'];
$client = Algorithmia::client($ALGO_APIKEY);

//TODO: check to see if api key is setup... if not, give an admin notice

require_once(ALGO_PLUGIN_DIR.'algorithms/upload_auto_tag.php');

?>
