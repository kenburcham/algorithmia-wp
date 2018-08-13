<?php
/*
* Detects (and optionally blocks) uploaded images with nudity
* using https://algorithmia.com/algorithms/sfw/NudityDetection
*/

//here we will add two filters that hook into the file upload process.

//wp_handle_upload fires right after a file is uploaded. we get a chance to reject the file if we want to.
add_filter('wp_handle_upload', 'algo_nudity_detect');

//once we've detected nudity in an image but aren't blocking it, we'd like to add "nudity" to the alt-text
add_filter('add_attachment', 'algo_nudity_detect_alttext');

//this function fires when a file is uploaded and sends it to an Algorithmia nudity detection algorithm
function algo_nudity_detect( $in_file ) { 
    
    $file = $in_file;
    
    //is 'nudity detection for uploaded images' enabled in settings?
    if (array_key_exists('algo_field_und_enabled', get_option('algo_options')) && get_option('algo_options')['algo_field_und_enabled'])
    {
        //only check image uploads
        if ( filetype_is_image( $file['type'] )) {

            try{

                //check if it is recognized with nudity
                if ( is_it_nude( $file['file'], $file['url'] ) )
                {
                    //check our setting - are we configured to block the file?
                    $blockit = array_key_exists('algo_field_und_block', get_option('algo_options')) && get_option('algo_options')['algo_field_und_block'];

                    if($blockit){
                        //nude image and blocking
                        $file = array('error' => "Error: Nudity detected in image and blocking is enabled.");
                    }
                    else{
                        //nude image but not blocking, save for our add_attachment handler to add the alttext later
                        $detected_files = get_option('algo_options_und_detected');
                        $detected_files[$file['file']]="true";
                        update_option('algo_options_und_detected',$detected_files);
                    }
                }
                //not nude so do nothing!

            //in the case of an exception, we'll just quietly carry on since we're in an ajax call
            }catch(Exception $e){
                //silence
            }
        } 
    }

    return $file;

}

//this function fires and adds "nudity" note to the alt-text if the upload not blocked but nudity is detected
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

//This function does the magic of sending the file to Algorithmia for detection
function is_it_nude($in_image_path, $in_image_url)
{

    //in_image_path like: "/var/www/html/wordpress/wp-content/uploads/2018/07/uploadpic.jpg";
    //in_image_url like: "http://localhost/wordpress/wp-content/uploads/2018/07/uploadpic.jpg"

    //setup our Algorithmia PHP client
    $ALGO_APIKEY = get_option('algo_options')['algo_field_api'];
    $client = Algorithmia::client($ALGO_APIKEY);

    //setup some variables based on our settings
    $algorithm = get_option('algo_options')['algo_field_und_algo'];
    $algo_remote_folder = get_option('algo_options')['algo_field_und_remote_folder'];
    $upload_to_algorithmia = 
        array_key_exists('algo_field_uploadtoalgo', get_option('algo_options')) && get_option('algo_options')['algo_field_uploadtoalgo'];

    if($upload_to_algorithmia)
    {
        //use the Algorithmia PHP client to create the remote folder to store the file if it doesn't already exist
        $algo_wp = $client->dir($algo_remote_folder);
        if(!$algo_wp->exists()) {
            $algo_wp->create(Algorithmia\ACL::ANYONE); 
        }

        //put the file into the folder
        $file = $algo_wp->putFile($in_image_path);

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
        $input = $in_image_url; //url to the file on our server
    }
    
    //now run the algorithm and do the magic!
    $algo = $client->algo($algorithm);
    
    //the results come back as a PHP object. handy!
    $result = $algo->pipe($input)->result;

    //each algorithm responds with results their own way.
    // see the algorithm documentation to determine what you may need to use the results.
    // for this example, the algorithm returns "nude = true" if it is, in fact, nude.
    return ($result->nude === true || $result->nude === "true");
    
}



//admin section for this algorithm

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
            'default' => "sfw/NudityDetectioni2v/0.2.12"
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
        <hr/>
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