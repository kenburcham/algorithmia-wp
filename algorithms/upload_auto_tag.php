<?php
/*
* Automatically tags uploaded files with the labels from object detection
* using https://algorithmia.com/algorithms/deeplearning/ObjectDetectionCOCO
*/




add_filter('add_attachment', 'algo_set_image_meta');

function algo_set_image_meta( $post_ID ){
    
	if ( wp_attachment_is_image( $post_ID ) ) {
		// Set the image alt-text
		update_post_meta( $post_ID, '_wp_attachment_image_alt', algo_get_tags_for_attachment($post_ID) );

	} 
}

function algo_get_tags_for_attachment($in_post_id)
{

    //setup our Algorithmia client
    $ALGO_APIKEY = get_option('algo_options')['algo_field_api'];
    $client = Algorithmia::client($ALGO_APIKEY);

    //setup some variables
    $object_recognition_algorithm = "deeplearning/ObjectDetectionCOCO/0.2.1";
    $algo_remote_folder = "data://.my/algo_wp";
    $upload_to_algorithmia = true;

    //fake it for now
    //$fake_input_url = "data://kenburcham/algo_wp/GtvDM8X.jpg";//"https://www.kariwhite.net/wp-content/uploads/2011/12/our-camping-trip.jpg";

    if($upload_to_algorithmia)
    {
        $image_path = get_attached_file( $in_post_id );
        //like: "/var/www/html/wordpress/wp-content/uploads/2018/07/uploadpic-7.jpg";

        $algo_wp = $client->dir($algo_remote_folder);
        if(!$algo_wp->exists()) {
            $algo_wp->create(Algorithmia\ACL::ANYONE); //so that the ObjRecognizer algo can analyze the file
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
        //$image_url = get_post( $post_ID )->guid; //url to the file on our server
        $input = $fake_input_url;

    }
    
    //$input = $fake_input_url;
    $algo = $client->algo($object_recognition_algorithm);
    $result = $algo->pipe($input)->result;

    $labels = [];

    foreach($result->boxes as $item) {
        $labels[]=$item->label;
    }

    return implode(" ",$labels);

}



//admin section for this algorithm
//options = enabled, preview pic, upload file to algo

