<?php
/*
* Automatically tags uploaded files with the labels from object detection
* using https://algorithmia.com/algorithms/deeplearning/ObjectDetectionCOCO
*/


add_filter('add_attachment', 'algo_set_image_meta');

function algo_set_image_meta( $post_ID ){

    //if the setting is turned on for this "feature"...
    if (array_key_exists('algo_field_uat_enabled', get_option('algo_options')) && get_option('algo_options')['algo_field_uat_enabled'])
    {
        //and the attachment is an image
        if ( wp_attachment_is_image( $post_ID ) ) {
            // then pre-pend the AI discovered tags to the alt-text
            $current_meta = get_post_meta($in_post_id, '_wp_attachment_image_alt',true );
            update_post_meta( $post_ID, '_wp_attachment_image_alt', algo_get_tags_for_attachment($post_ID). " ". $current_meta );
        } 
    }
}

//use algorithmia to recognize the objects in the image!
function algo_get_tags_for_attachment($in_post_id)
{
    //setup our Algorithmia client
    $ALGO_APIKEY = get_option('algo_options')['algo_field_api'];
    $client = Algorithmia::client($ALGO_APIKEY);

    //get our options from the settings page
    $object_recognition_algorithm = get_option('algo_options')['algo_field_uat_algo'];
    $algo_remote_folder = get_option('algo_options')['algo_field_uat_remote_folder'];
    $upload_to_algorithmia = 
        array_key_exists('algo_field_uploadtoalgo', get_option('algo_options')) && get_option('algo_options')['algo_field_uploadtoalgo'];

    //if the setting is checked to upload files to algorithmia for processing, then do so. 
    //  this can be necessary if the uploaded file is not accessible to Algorithmia (e.g., localhost or behind a proxy, etc.)
    if($upload_to_algorithmia)
    {
        $image_path = get_attached_file( $in_post_id );    //like: "/var/www/html/wordpress/wp-content/uploads/2018/07/uploadpic-7.jpg";

        //use the Algorithmia PHP client to create the remote folder to store the file if it doesn't already exist
        $algo_wp = $client->dir($algo_remote_folder);
        if(!$algo_wp->exists()) {
            $algo_wp->create(Algorithmia\ACL::ANYONE); //so that the ObjRecognizer algo can analyze the file (note: NOT public/anonymous available)
        }

        //put the file into the folder
        $file = $algo_wp->putFile($image_path);

        //check to see if everything went ok!
        if($file->response->getStatusCode() !== 200 )
        {
            //something went wrong
            throw new Exception("There was a problem sending this file to Algorithmia.");
        }

        //get the remote data url that the object detection algorithm can use to load the file
        //  this will be an algorithmia path like "data://.my/algo_wp/myfile.png"
        $input = $file->getDataUrl();

    } else { //otherwise just use the url (doesn't work for localhost because algo can't access the file via the url)
        $input = get_post( $in_post_id )->guid; //url to the file on our server
    }

    //now run the algorithm and do the magic!
    $algo = $client->algo($object_recognition_algorithm);

    //the results come back as a PHP object. handy!
    $result = $algo->pipe($input)->result;
    
    $labels = [];

    //what objects did the algorithm find? it labels each object and returns the labels in an array called boxes.
    //  each algorithm responds their own way. see the algorithm documentation to determine what you may need to use the results.
    foreach($result->boxes as $item) {
        if(!in_array($item->label, $labels))
            $labels[]=$item->label;
    }

    //now return our labels as a string for our alt-text!
    return implode(" ",$labels);

}



//admin section for this algorithm that appears on the settings page

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

//callback for the section header created above.
function algo_section_uat_cb( $args ) {
    ?>
        <hr/>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <?php esc_html_e( 'This algorithm auto-tags uploaded files and adds the AI discovered labels to the alt-text of the image.','algo' ); ?>
        </p>
    <?php
   }

add_action( 'admin_init', 'algo_upload_auto_tag_settings_init' );

