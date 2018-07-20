<?php
/*
* Automatically tags uploaded files with the labels from object detection
* using https://algorithmia.com/algorithms/deeplearning/ObjectDetectionCOCO
*/


add_filter('add_attachment', 'algo_set_image_meta');

function algo_set_image_meta( $post_ID ){
    
    if (array_key_exists('algo_field_uat_enabled', get_option('algo_options')) && get_option('algo_options')['algo_field_uat_enabled'])
    {
        if ( wp_attachment_is_image( $post_ID ) ) {
            // Set the image alt-text
            $current_meta = get_post_meta($in_post_id, '_wp_attachment_image_alt',true );
            update_post_meta( $post_ID, '_wp_attachment_image_alt', algo_get_tags_for_attachment($post_ID). " ". $current_meta );
        } 
    }
}

function algo_get_tags_for_attachment($in_post_id)
{
    //setup our Algorithmia client
    $ALGO_APIKEY = get_option('algo_options')['algo_field_api'];
    $client = Algorithmia::client($ALGO_APIKEY);

    //setup some variables
    $object_recognition_algorithm = get_option('algo_options')['algo_field_uat_algo'];
    $algo_remote_folder = get_option('algo_options')['algo_field_uat_remote_folder'];

    $upload_to_algorithmia = 
        array_key_exists('algo_field_uploadtoalgo', get_option('algo_options')) && get_option('algo_options')['algo_field_uploadtoalgo'];

    //fake an external url
    //$fake_input_url = "https://www.kariwhite.net/wp-content/uploads/2011/12/our-camping-trip.jpg";

    if($upload_to_algorithmia)
    {
        $image_path = get_attached_file( $in_post_id );
        //like: "/var/www/html/wordpress/wp-content/uploads/2018/07/uploadpic-7.jpg";

        $algo_wp = $client->dir($algo_remote_folder);
        if(!$algo_wp->exists()) {
            $algo_wp->create(Algorithmia\ACL::ANYONE); //so that the ObjRecognizer algo can analyze the file (note: NOT public/anonymous available)
        }

        $file = $algo_wp->putFile($image_path);

        if($file->response->getStatusCode() !== 200 )
        {
            //something went wrong
            throw new Exception("There was a problem sending this file to Algorithmia.");
        }

        //we will detect the objects in the image file we uploaded
        $input = $file->getDataUrl();

    } else { //otherwise just use the url (doesn't work for localhost because algo can't access the file via the url)
        $input = get_post( $in_post_id )->guid; //url to the file on our server
    }

    $algo = $client->algo($object_recognition_algorithm);
    $result = $algo->pipe($input)->result;
    
    $labels = [];

    foreach($result->boxes as $item) {
        if(!in_array($item->label, $labels))
            $labels[]=$item->label;
    }

    return implode(" ",$labels);

}



//admin section for this algorithm
//options = enabled

function algo_upload_auto_tag_settings_init() {

    // register a new section in the "algo" page
    add_settings_section(
        'algo_section_uat',     //id
        'Feature: Auto-tag Uploads',      //title
        'algo_section_uat_cb',  //callback
        'algo'          //page (menu-slug)
    );


    add_settings_field(
        'algo_field_uat_enabled',                       //id
        'Enable this feature',                              //title
        'algo_field_checkbox_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_uat',                     //section
        [                                       //args
            'label_for' => 'algo_field_uat_enabled',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => false
        ]
    );

    add_settings_field(
        'algo_field_uat_algo',                       //id
        'Object Detection Algorithm',                              //title
        'algo_field_text_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_uat',                     //section
        [                                       //args
            'label_for' => 'algo_field_uat_algo',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => "deeplearning/ObjectDetectionCOCO/0.2.1"
        ]
    );

    add_settings_field(
        'algo_field_uat_remote_folder',                       //id
        'Remote Folder (if Upload to Algorithmia is selected)',                              //title
        'algo_field_text_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_uat',                     //section
        [                                       //args
            'label_for' => 'algo_field_uat_remote_folder',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => "data://.my/algo_wp"
        ]
    );

    
}

function algo_section_uat_cb( $args ) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <?php esc_html_e( 'This algorithm auto-tags uploaded files and adds the AI discovered labels to the alt-text of the image.','algo' ); ?>
        </p>
    <?php
   }

add_action( 'admin_init', 'algo_upload_auto_tag_settings_init' );

