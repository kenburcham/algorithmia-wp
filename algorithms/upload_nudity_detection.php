<?php
/*
* Detects nudity in uploaded images and takes configured action.
* using https://algorithmia.com/algorithms/
*/

$nudity_detection_algorithm = "deeplearning/ObjectDetectionCOCO/0.2.1";

//add_filter('wp_handle_upload', 'algo_handle_upload');

function algo_handle_upload( $fileinfo ){
    //var_dump($fileinfo);
    //echo("Event: wp_handle_upload");
    //echo("File: " . $fileinfo["file"]);
    //echo("Url: " . $fileinfo["url"]);
    //echo("Type: " . $fileinfo["type"]);
    $file = $fileinfo["file"];
    $url = $fileinfo["url"];
    $type = $fileinfo["type"];
    if ($type == "image/jpeg" || $type == "image/jpg" || $type == "image/gif" || $type == "image/png"){
      //  echo("Review File Start: " . $url);
        $tags = algo_get_tags($url, $file);
        /*
        if ($json->error_code == "0"){
            if ($json->rating_letter == "a") {
                $upload_dir = wp_upload_dir();
                $adult_file = $upload_dir["basedir"] . "/rating_adult_box.png";
                if (!file_exists($adult_file)){
                    copy(algo_DIR . "img/rating_adult_box.png", $adult_file);
                }
                $fileinfo["file"] = $adult_file;
                $fileinfo["url"] = $upload_dir["baseurl"] . "/rating_adult_box.png";
                $fileinfo["type"] = "image/png";        
            } else {
                echo("Approved File:" . $file);
            }
        }
        */
        //echo("Review End: " . $url. " tags are ". implode(",",$tags));
    } else {
        //echo("Not file type: image/jpeg, image/jpg, image/gif, image/png");
    }
    return $fileinfo;
}

function algo_get_tags($url, $file)
{
    return ["a","b","c"];
}


/*
try {
    $algo = $client->algo("demo/Hello/0.1.0");
    echo $algo->pipe("World")->result;
}
catch(Algorithmia\AlgoException $e){
    echo "oops, invalid api key.";
}
*/

//hook into upload process...

//admin section for this algorithm
//options = enabled
// time? also preview pic, upload file to algo






//test our object recognizer

/*
$input = "http://i.imgur.com/k67kjlB.jpg";
$algo = $client->algo($object_recognition_algorithm);
$result = $algo->pipe($input)->result;

$labels = [];
foreach($result->boxes as $item) {
    $labels[]=$item->label;
}

echo implode(",",$labels);

*/