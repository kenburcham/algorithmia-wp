<?php
/*
Plugin Name: Algorithmia AI
Plugin URI: https://algorithmia.com
Description: Use Algorithmia Artificial Intelligence in your WordPress.
Text Domain: algo
Version: 1.0
Author: kenburcham@gmail.com
License: MIT
*/

/*

This plugin demonstrates Algorithmia's PHP client (https://algorithmia.com/developers/clients/php/) 
as well as provides a template for integrating any Algorithmia algorithm into your 
WordPress website. 

To add support for a new algorithm, copy/create a file in the /algorithms folder of the plugin
and follow the same pattern you see in our examples. We just create a hook or a feature and then 
fire off a call to do some AI magic on Algorithmia's platform. Files in the /algorithms folder will be
loaded automatically.

*/


if ( ! defined('ABSPATH')) exit;  // if direct access

define( 'ALGO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ALGO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once(ALGO_PLUGIN_DIR.'vendor/autoload.php');    //load algorithmia client
require_once(ALGO_PLUGIN_DIR.'admin_page.php');         //setup settings page

//load the algorithms we have available
foreach (glob(ALGO_PLUGIN_DIR.'algorithms/*.php') as $algo_file)
{
    include_once $algo_file;
}

//check to see if api key is setup... if not, give an admin notice
add_action('admin_notices', 'algo_check_api_key');

function algo_check_api_key(){
    $options = get_option('algo_options');
    
    if(!array_key_exists('algo_field_api',$options) || trim($options['algo_field_api']==""))
    {
        echo '<div class="notice notice-warning is-dismissible">
             <p>Warning: Your Algorithmia plugin is enabled but no API key is set. Please go to Settings->Algorithmia to set your API key.</p>
         </div>';
    }
}

?>
