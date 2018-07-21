<?php
/*
* Use AI to add a brief abstract to the top of a post
* using https://algorithmia.com/algorithms/nlp/Summarizer
*/

//this is meant to be a simple "hello world!" of Algorithmia + WordPress!

//hook into the save a post event
add_filter('content_save_pre', 'algo_summarize_post');

//summarize the content and add it to the beginning with a big, ugly heading!
// note: we won't run if we detect the heading already in the content... so 
//       if you wanted to re-run the summarizer on your post, just delete the
//       one previously generated.
function algo_summarize_post( $in_content ){
    
    $content = $in_content;

    //if the setting is checked to enable this feature
    if (array_key_exists('algo_field_psum_enabled', get_option('algo_options')) && get_option('algo_options')['algo_field_psum_enabled'])
    {

        $summary_title = "<h3>Algorithmia AI Generated Summary</h3>";

        //bail out if we've been here before!
        if(strpos($content, $summary_title) !== false)
            return $content;

        //setup our Algorithmia client
        $apikey = get_option('algo_options')['algo_field_api'];
        $client = Algorithmia::client($apikey);

        //send our post content to Algorithmia's AI for processing!
        $algo = $client->algo("nlp/Summarizer/0.1.8");
        $result = $algo->pipe($in_content)->result;

        //concatenate our final result
        $content = $summary_title.
                        $result.
                        "<hr/>".
                        $in_content;
    }

    return $content;
}



//admin section for this algorithm that appears on the settings page

function algo_upload_post_summarizer_settings_init() {

    // register a new section in the "algo" page
    add_settings_section(
        'algo_section_psum',     //id
        'Feature: Post Summarizer',      //title
        'algo_section_psum_cb',  //callback
        'algo'          //page (menu-slug)
    );


    add_settings_field(
        'algo_field_psum_enabled',                       //id
        'Enable this feature',                              //title
        'algo_field_checkbox_cb',                    //callback for text fields
        'algo',                         //page (menu-slug)
        'algo_section_psum',                     //section
        [                                       //args
            'label_for' => 'algo_field_psum_enabled',
            'class' => 'algo_row',
            'algo_custom_data' => 'custom',
            'default' => false
        ]
    );
}

//callback for the section header created above.
function algo_section_psum_cb( $args ) {
    ?>
        <hr/>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <?php esc_html_e( 'This algorithm auto-summarizes posts and prepends the AI generated summary to the post.','algo' ); ?>
        </p>
    <?php
   }

add_action( 'admin_init', 'algo_upload_post_summarizer_settings_init' );