<?php 

/*
*  Adds the options page that appears on the Settings menu
*/

if ( ! defined('ABSPATH')) exit;  // if direct access

/**
 * Registers an options page under Settings.
 */

 function algo_settings_menuitem() {
    add_options_page( 
        'Algorithmia Options', //page_title
        'Algorithmia',         //menu_title
        'manage_options',      //capability (permission)
        'algo',                 //menu_slug
        'algo_options_page'    //function
    );
}

add_action('admin_menu', 'algo_settings_menuitem');

function algo_options_page()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "algo_options"
            settings_fields('algo');
            // output setting sections and their fields
            // (sections are registered for "algo", each field is registered to a specific section)
            do_settings_sections('algo');
            // output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}


/**
 * register settings fields that will display on our settings page
 */
function algo_settings_init() {
    // register a new setting for "algo" page
    register_setting( 'algo', 'algo_options' );

    if(!get_option('algo_options'))
        add_option('algo_options',[]);
    
    // register a new section in the "algo" page
    add_settings_section(
        'algo_section_api',     //id
        'Algorithmia API',      //title
        'algo_section_api_cb',  //callback
        'algo'          //page (menu-slug)
    );

    // register a new field in the "algo_section_api" section, inside the "algo-" page
    add_settings_field(
        'algo_field_api',                       //id
        'API Key',                              //title
        'algo_field_text_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_api',                     //section
        [                                       //args
            'label_for' => 'algo_field_api',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => ''
        ]
    );

    add_settings_field(
        'algo_field_uploadtoalgo',                       //id
        'Upload files to Algorithmia for processing? (required for localhost)',                              //title
        'algo_field_checkbox_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_api',                     //section
        [                                       //args
            'label_for' => 'algo_field_uploadtoalgo',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => false
        ]
    );

}
 
/**
 * register our algo_settings_init to the admin_init action hook
 */
add_action( 'admin_init', 'algo_settings_init' );


/* -- callbacks -- */

function algo_section_api_cb( $args ) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Add your API key here. If you don\'t have one, visit: https://algorithmia.com to sign up and get one.','algo' ); ?></p>
    <?php
    //echo "currently: ".get_option('algo_options')['algo_field_api'];
   }

function algo_field_text_cb ($args) {

    $options = algo_set_option_default($args); 

    echo '<input type="text" size="50" id="'  . $args['label_for'] . '" name="algo_options['  . $args['label_for'] . ']" value="' . $options[ $args['label_for'] ] . '"></input>';
}

function algo_field_checkbox_cb ($args) {

    $options = algo_set_option_default($args); 

    ?>
    <input type="checkbox" id="<?php echo $args['label_for']?>" name="algo_options[<?php echo $args['label_for'] ?>]" 
        value="1"<?php checked( 1== $options[ $args['label_for'] ] )?> />
    <?php
}

function algo_set_option_default($args){
    $options = get_option('algo_options'); 

    if(!array_key_exists($args['label_for'],$options))
        $options[ $args['label_for'] ] = $args['default'];

    return $options;
}