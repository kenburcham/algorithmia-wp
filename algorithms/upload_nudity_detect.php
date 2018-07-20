<?php
/*
* Detects (and optionally blocks) uploaded images with nudity
* using https://algorithmia.com/algorithms/sfw/NudityDetection
*/

add_filter('add_attachment', 'algo_nudity_detect_alttext');
add_filter('wp_handle_upload', 'algo_nudity_detect');

function algo_nudity_detect( $in_file ) { 
    
    $file = $in_file;
    
    //is 'nudity detection for uploaded images' enabled?
    if (array_key_exists('algo_field_und_enabled', get_option('algo_options')) && get_option('algo_options')['algo_field_und_enabled'])
    {
        //only check image uploads
        if ( filetype_is_image( $file['type'] )) {

            //check if it is nude
            if ( is_it_nude( $file['file'], $file['url'] ) )
            {
                $blockit = array_key_exists('algo_field_und_block', get_option('algo_options')) && get_option('algo_options')['algo_field_und_block'];

                if($blockit){
                    //nude and blocking
                    $file = array('error' => "Error: Nudity detected in image and blocking is enabled.");
                }
                else{
                    //nude but not blocking, save for our add_attachment handler to add the alttext later
                    $detected_files = get_option('algo_options_und_detected');
                    $detected_files[$file['file']]="true";
                    update_option('algo_options_und_detected',$detected_files);
                }
            }
            //not nude so do nothing!
        } 
    }

    return $file;

}

//this is adds "Nudity Detected" to the alt-text if the upload not blocked but nudity is detected
function algo_nudity_detect_alttext( $in_post_id ){

    //is 'nudity detection for uploaded images' enabled?
    if (array_key_exists('algo_field_und_enabled', get_option('algo_options')) 
        && get_option('algo_options')['algo_field_und_enabled']
        && wp_attachment_is_image( $in_post_id ) )
    {
        //was nudity detected for this file?
        $detected_files = get_option('algo_options_und_detected');
        $image_path = get_attached_file( $in_post_id );

        //if so then add our note to the alttext
        if(array_key_exists($image_path, $detected_files))
        {
            if($detected_files[$image_path]=="true"){
                $current_meta = get_post_meta($in_post_id, '_wp_attachment_image_alt',true );
                update_post_meta( $in_post_id, '_wp_attachment_image_alt', "nudity ". $current_meta );
                unset($detected_files[$image_path]);
                update_option('algo_options_und_detected',$detected_files);
            }
        }

    }
}

//in_image_path like: "/var/www/html/wordpress/wp-content/uploads/2018/07/uploadpic.jpg";
//in_image_url like: "http://localhost/wordpress/wp-content/uploads/2018/07/uploadpic.jpg"
function is_it_nude($in_image_path, $in_image_url)
{
    //setup our Algorithmia client
    $ALGO_APIKEY = get_option('algo_options')['algo_field_api'];
    $client = Algorithmia::client($ALGO_APIKEY);

    //setup some variables
    $algorithm = get_option('algo_options')['algo_field_und_algo'];
    $algo_remote_folder = get_option('algo_options')['algo_field_und_remote_folder'];

    $upload_to_algorithmia = 
        array_key_exists('algo_field_uploadtoalgo', get_option('algo_options')) && get_option('algo_options')['algo_field_uploadtoalgo'];

    if($upload_to_algorithmia)
    {
        $algo_wp = $client->dir($algo_remote_folder);
        if(!$algo_wp->exists()) {
            $algo_wp->create(Algorithmia\ACL::ANYONE); //so that the ObjRecognizer algo can analyze the file (note: NOT public/anonymous available)
        }

        $file = $algo_wp->putFile($in_image_path);

        if($file->response->getStatusCode() !== 200 )
        {
            //something went wrong
            throw new Exception("There was a problem sending this file to Algorithmia.");
        }

        //we will detect the objects in the image file we uploaded
        $input = $file->getDataUrl();

    } else { //otherwise just use the url (doesn't work for localhost because algo can't access the file via the url)
        $input = $in_image_url; //url to the file on our server
    }

    $algo = $client->algo($algorithm);
    $result = $algo->pipe($input)->result;
    
    return $result->nude == "true";
    
}



//admin section for this algorithm
//options = enabled

function algo_upload_nudity_detect_settings_init() {

    // register a new section in the "algo" page
    add_settings_section(
        'algo_section_und',     //id
        'Feature: Detect Nudity in Uploaded Images',      //title
        'algo_section_und_cb',  //callback
        'algo'          //page (menu-slug)
    );


    add_settings_field(
        'algo_field_und_enabled',                       //id
        'Enable this feature',                              //title
        'algo_field_checkbox_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_und',                     //section
        [                                       //args
            'label_for' => 'algo_field_und_enabled',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => false
        ]
    );

    add_settings_field(
        'algo_field_und_block',                       //id
        'Block uploads with detected nudity (otherwise, note in alt-text)',                              //title
        'algo_field_checkbox_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_und',                     //section
        [                                       //args
            'label_for' => 'algo_field_und_block',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => false
        ]
    );

    add_settings_field(
        'algo_field_und_algo',                       //id
        'Nudity Detection Algorithm',                              //title
        'algo_field_text_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_und',                     //section
        [                                       //args
            'label_for' => 'algo_field_und_algo',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => "sfw/NudityDetection/1.1.6"
        ]
    );

    add_settings_field(
        'algo_field_und_remote_folder',                       //id
        'Remote Folder (if Upload to Algorithmia is selected)',                              //title
        'algo_field_text_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_und',                     //section
        [                                       //args
            'label_for' => 'algo_field_und_remote_folder',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => "data://.my/algo_wp"
        ]
    );

    
}

function algo_section_und_cb( $args ) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <?php esc_html_e( 'This algorithm detects nudity in images and optionally blocks the upload.','algo' ); ?>
        </p>
    <?php
   }

add_action( 'admin_init', 'algo_upload_nudity_detect_settings_init' );

//our state for files that are detected
if(!get_option('algo_options_und_detected'))
    add_option('algo_options_und_detected',[]);

//is it an image file?
function filetype_is_image($in_type){
    return ($in_type == "image/jpeg" || $in_type == "image/jpg" || $in_type == "image/gif" || $in_type == "image/png");
}