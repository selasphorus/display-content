<?php
/**
 * @package Display_Content
 * @version 0.1
 */

/*
Plugin Name: Birdhive Display Content
Version: 0.1
Plugin URI: 
Description: Display content of all types in a variety of formats using shortcodes.
Author: Alison C.
Author URI: http://birdhive.com
Text Domain: dsplycntnt
*/

/*********
Copyright (c) 2022, Alison Cheeseman/Birdhive Development & Design

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*********/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

$plugin_path = plugin_dir_path( __FILE__ );

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

// TODO: build in plugin dependency on SDG

// Register our sdg_settings_init to the admin_init action hook.
//add_action( 'admin_init', 'dsplycntnt_settings_init' );

/**
 * Custom option and settings
 */
function dsplycntnt_settings_init() {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );

	// Register a new setting for "dsplycntnt" page.
	register_setting( 'dsplycntnt', 'dsplycntnt_settings' );

	// Register a new section in the "dsplycntnt" page.
	add_settings_section(
		'dsplycntnt_settings',
		__( 'Display Content Plugin Settings', 'dsplycntnt' ), 'dsplycntnt_settings_section_callback',
		'dsplycntnt'
	);
	
	/*
	// Checkbox to designate dev site
	add_settings_field(
        'is_dev_site',
        esc_attr__('Dev Site', 'dsplycntnt'),
        'dsplycntnt_devsite_field_cb',
        'dsplycntnt',
        'dsplycntnt_settings',
        array( 
            'type'         => 'checkbox',
            //'option_group' => 'dsplycntnt_settings', 
            'name'         => 'is_dev_site',
            'label_for'    => 'is_dev_site',
            'value'        => (empty(get_option('dsplycntnt_settings')['is_dev_site'])) ? 0 : get_option('dsplycntnt_settings')['is_dev_site'],
            'description'  => __( 'This is a dev site.', 'dsplycntnt' ),
            'checked'      => (!isset(get_option('dsplycntnt_settings')['is_dev_site'])) ? 0 : get_option('dsplycntnt_settings')['is_dev_site'],
            // Used 0 in this case but will still return Boolean not[see notes below] 
            ///'tip'          => esc_attr__( 'Use if plugin fields drastically changed when installing this plugin.', 'wpdevref' ) 
            )
    );
    
    // Checkbox to determine whether or not to use custom capabilities
	add_settings_field(
        'use_custom_caps',
        esc_attr__('Capabilities (Permissions)', 'dsplycntnt'),
        'dsplycntnt_caps_field_cb',
        'dsplycntnt',
        'dsplycntnt_settings',
        array( 
            'type'         => 'checkbox',
            'name'         => 'use_custom_caps',
            'label_for'    => 'use_custom_caps',
            'value'        => (empty(get_option('dsplycntnt_settings')['use_custom_caps'])) ? 0 : get_option('dsplycntnt_settings')['use_custom_caps'],
            'description'  => __( 'Use custom capabilities.', 'dsplycntnt' ),
            'checked'      => (!isset(get_option('dsplycntnt_settings')['use_custom_caps'])) ? 0 : get_option('dsplycntnt_settings')['use_custom_caps'],
            )
    ); 
	
	// Register a new section in the "dsplycntnt" page.
	add_settings_section(
		'dsplycntnt_modules',
		__( 'Display Content Modules', 'dsplycntnt' ), 'dsplycntnt_modules_section_callback',
		'dsplycntnt'
	);
	
	// Register a new field in the "dsplycntnt_modules" section, inside the "dsplycntnt" page.
	add_settings_field(
		'dsplycntnt_modules', // As of WP 4.6 this value is used only internally.
		__( 'Active Modules', 'dsplycntnt' ),
		'dsplycntnt_modules_field_cb',
		'dsplycntnt',
		'dsplycntnt_modules',
		array(
			'label_for'         => 'dsplycntnt_modules',
			//'value'        		=> (empty(get_option('dsplycntnt_settings')['dsplycntnt_modules'])) ? 0 : get_option('dsplycntnt_settings')['dsplycntnt_modules'],
			'class'             => 'dsplycntnt_row',
			'dsplycntnt_custom_data' 	=> 'custom',
		)
	);
	*/
	// TODO: new section/field(s) geared toward individual artist site -- see "artiste" plugin posttypes draft
	
	
}


// Include custom post type (collection)
$posttypes_filepath = $plugin_path . 'inc/posttypes.php';
if ( file_exists($posttypes_filepath) ) { include_once( $posttypes_filepath ); } else { echo "no $posttypes_filepath found"; }

// Add custom image sizes
// TODO: build in option to customize dimensions per site
add_image_size( 'grid_crop_square', 600, 600, true ); // for stc: 600x600; for general use: 400x400. WIP: build in option via plugin settings
add_image_size( 'grid_crop_rectangle', 534, 300, true );
add_filter( 'image_size_names_choose', 'birdhive_custom_image_sizes' );
function birdhive_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'grid_crop_square' => __( 'Grid Crop (square)' ),
        'grid_crop_rectangle' => __( 'Grid Crop (rectangle)' ),
    ) );
}

// Enqueue scripts and styles -- WIP
add_action( 'wp_enqueue_scripts', 'dsplycntnt_scripts_method' );
function dsplycntnt_scripts_method() {
    
    $ver = "0.1";
    wp_enqueue_style( 'dsplycntnt-style', plugin_dir_url( __FILE__ ) . 'display-content.css', NULL, $ver );
    
    wp_register_script('dsplycntnt-js', plugin_dir_url( __FILE__ ) . 'js/dc.js', array( 'jquery' ) );
	wp_enqueue_script('dsplycntnt-js');
	
	// Enqueue Font Awesome 5
	// WIP

}

// The following filter function to be removed altogether after testing -- replaced by version of same fcn in sdg.php
// TODO: build in plugin dependency on SDG
// Facilitate search by str in post_title (as oppposed to built-in search by content or by post name, aka slug)
//add_filter( 'posts_where', 'birdhive_posts_where', 10, 2 );
/*function birdhive_posts_where( $where, $wp_query ) {
    
    global $wpdb;
    
    if ( $search_term = $wp_query->get( '_search_title' ) ) {
        $search_term = $wpdb->esc_like( $search_term );
        $search_term = esc_sql($search_term);
        //$search_term = '\'%' . $search_term . '%\'';
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $search_term. '%\'';
        //$where .= " AND " . $wpdb->posts . ".post_title LIKE '" . esc_sql( $wpdb->esc_like( $title ) ) . "%'";
    }
    
    // Get query vars
    $tax_args = isset( $wp_query->query_vars['tax_query'] ) ? $wp_query->query_vars['tax_query'] : null;
    $meta_args   = isset( $wp_query->query_vars['meta_query'] ) ? $wp_query->query_vars['meta_query'] : null;
    $meta_or_tax = isset( $wp_query->query_vars['_meta_or_tax'] ) ? wp_validate_boolean( $wp_query->query_vars['_meta_or_tax'] ) : false;

    // Construct the "tax OR meta" query
    if( $meta_or_tax && is_array( $tax_args ) && is_array( $meta_args )  ) {

        // Primary id column
        $field = 'ID';

        // Tax query
        $sql_tax  = get_tax_sql( $tax_args, $wpdb->posts, $field );

        // Meta query
        $sql_meta = get_meta_sql( $meta_args, 'post', $wpdb->posts, $field );

        // Modify the 'where' part
        if( isset( $sql_meta['where'] ) && isset( $sql_tax['where'] ) ) {
            $where  = str_replace( [ $sql_meta['where'], $sql_tax['where'] ], '', $where );
            $where .= sprintf( ' AND ( %s OR  %s ) ', substr( trim( $sql_meta['where'] ), 4 ), substr( trim( $sql_tax['where']  ), 4 ) );
        }
    }
    
    // Filter query results to enable searching per ACF repeater fields
    // See https://www.advancedcustomfields.com/resources/repeater/#faq "How is the data saved"
	// meta_keys for repeater fields are named according to the number of rows
	// ... e.g. item_0_description, item_1_description, so we need to adjust the search to use a wildcard for matching
	// Replace comparision operator "=" with "LIKE" and replace the wildcard placeholder "XYZ" with the actual wildcard character "%"
	$pattern = '/meta_key = \'([A-Za-z_]+)_XYZ/i';
	if ( preg_match("/meta_key = '[A-Za-z_]+_XYZ/i", $where) ) {
		$where = preg_replace($pattern, "meta_key LIKE '$1_%", $where); //$where = str_replace("meta_key = 'program_items_XYZ", "meta_key LIKE 'program_items_%", $where);
	}
    
    return $where;
}
*/

/*** MISC ***/

// Add custom query vars
// TBD -- IMPORTANT: will this cause issues with EM?
add_filter( 'query_vars', 'dsplycntnt_query_vars' );
function dsplycntnt_query_vars( $qvars ) {
	$qvars[] = 'scope';
    return $qvars;
}

// Hide everything within and including the square brackets
// e.g. for titles matching the pattern "{Whatever} [xxx]" or "[xxx] {Whatever}"
/*if ( !function_exists( 'remove_bracketed_info' ) ) {
    function remove_bracketed_info ( $str ) {

        if (strpos($str, '[') !== false) { 
            $str = preg_replace('/\[[^\]]*\]([^\]]*)/', trim('$1'), $str);
            $str = preg_replace('/([^\]]*)\[[^\]]*\]/', trim('$1'), $str);
        }

        return $str;
    }
}*/

/*** IMAGE FUNCTIONS ***/

// Extract first image from post content
function get_first_image_from_post_content( $post_id ) {
    
    if ( empty($post_id) ) { return false; }
    
    // Init vars
    $info = array();
    $first_image = null;
    $first_image_id = null;
    $first_image_url = null;
    
    $post = get_post( $post_id );
    
    //ob_start();
    //ob_end_clean();
    
    // Find all the image tags in the post content
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
    //$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
    //echo "Matches for post_id: $post_id => <pre>".print_r($matches, true)."</pre>"; // tft
    
    //if ( !empty($matches) ) { 
    if ( isset($matches[1][0]) ) {
        $first_image = $matches[0][0];
        $first_image_url = $matches[1][0];
    } else {
        //echo "Matches for post_id: $post_id => <pre>".print_r($matches, true)."</pre>"; // tft
    }

    /*if ( empty($first_image) ){ // Defines a default placeholder image
        // Set the default image if there are no image available inside post content
        if ( function_exists( 'get_placeholder_img' ) ) { $first_image = get_placeholder_img(); }
        //$first_image = "/img/default.jpg";
    }*/
    
    //return $first_image;
    
    if ( !empty($first_image) ) {
        
        if ( preg_match('/(wp-image-)([0-9]+)/', $first_image, $matches) ) {
            //echo print_r($matches, true); // tft
            $first_image_id = $matches[2];
        }
    }
    
    $info['img'] = $first_image;
    $info['id'] = $first_image_id;
    $info['url'] = $first_image_url;
    
    //return $first_image_id;
    return $info;
    
}

/*** EXCERPTS AND ENTRY META ***/

// Allow select HTML tags in excerpts
// https://wordpress.stackexchange.com/questions/141125/allow-html-in-excerpt
function dsplycntnt_allowedtags() {
    return '<style>,<br>,<em>,<strong>'; 
}

function dsplycntnt_custom_wp_trim_excerpt($excerpt) {
        
	global $post;
	
	$raw_excerpt = $excerpt;
	if ( '' == $excerpt ) {

		$excerpt = get_the_content('');
		$excerpt = strip_shortcodes( $excerpt );
		$excerpt = apply_filters('the_content', $excerpt);
		$excerpt = str_replace(']]>', ']]&gt;', $excerpt);
		$excerpt = strip_tags($excerpt, dsplycntnt_allowedtags()); // IF you need to allow just certain tags. Delete if all tags are allowed

		//Set the excerpt word count and only break after sentence is complete.
		$excerpt_word_count = 75;
		$excerpt_length = apply_filters('excerpt_length', $excerpt_word_count); 
		$tokens = array();
		$excerptOutput = '';
		$count = 0;

		// Divide the string into tokens; HTML tags, or words, followed by any whitespace
		preg_match_all('/(<[^>]+>|[^<>\s]+)\s*/u', $excerpt, $tokens);

		foreach ($tokens[0] as $token) { 

			if ($count >= $excerpt_length && preg_match('/[\,\;\?\.\!]\s*$/uS', $token)) { 
			// Limit reached, continue until , ; ? . or ! occur at the end
				$excerptOutput .= trim($token);
				break;
			}

			// Add words to complete sentence
			$count++;

			// Append what's left of the token
			$excerptOutput .= $token;
		}

		$excerpt = trim(force_balance_tags($excerptOutput));
		
		// After the content
		//$excerpt .= atc_excerpt_more( '' );

		return $excerpt;   

	} else if ( has_excerpt( $post->ID ) ) {
		//$excerpt .= atc_excerpt_more( '' );
		//$excerpt .= "***";
	}
	return apply_filters('dsplycntnt_custom_wp_trim_excerpt', $dsplycntnt_excerpt, $raw_excerpt);
}

// Replace trim_excerpt function -- temp disabled for troubleshooting
//remove_filter('get_the_excerpt', 'wp_trim_excerpt');
//add_filter('get_the_excerpt', 'dsplycntnt_custom_wp_trim_excerpt'); 

/* Function to allow for multiple different excerpt lengths as needed
 * Call as follows:
 * Adapted from https://www.wpexplorer.com/custom-excerpt-lengths-wordpress/
 *
 */

function dsplycntnt_get_excerpt( $args = array() ) {
	
	// init vars
	$info = "";
	$text = "";	
	//$info .= "args (initial): <pre>".print_r($args, true)."</pre>";
	
	// Defaults
	$defaults = array(
		'post'            	=> '',
		'post_id'         	=> null,
		'preview_length'  	=> 55, // num words to display as preview text
		'readmore'        	=> false,
		'readmore_text'   	=> esc_html__( 'Read more...', 'dsplycntnt' ),
		'readmore_after'  	=> '',
		'custom_excerpts' 	=> true,
		'disable_more'    	=> false,
		'expandable'		=> false,
		'text_length'		=> 'excerpt',
	);

	// Apply filters
	//$defaults = apply_filters( 'dsplycntnt_get_excerpt_defaults', $defaults );

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );	
	//$info .= "args: <pre>".print_r($args, true)."</pre>";

	if ( $post_id ) {
		$post = get_post( $post_id );
	} else {
		if ( ! $post ) { global $post; } // Get global post data
		$post_id = $post->ID; // Get post ID
	}
	
	// Set up the "Read more" link
	$readmore_link = '&nbsp;<a href="' . get_permalink( $post_id ) . '" class="readmore"><em>' . $readmore_text . $readmore_after . '</em></a>'; // todo -- get rid of em, use css
	 
	// Get the text, based on args
	if ( $text_length == "full" ) {
		// Full post content
		$text = $post->post_content;
	} else if ( $custom_excerpts && has_excerpt( $post_id ) ) {
		// Check for custom excerpt
		$text = $post->post_excerpt;
	} else if ( ! $disable_more && strpos( $post->post_content, '<!--more-->' ) ) {
		// Check for "more" tag and return content, if it exists
		$text = apply_filters( 'the_content', get_the_content( $readmore_text . $readmore_after ) );
	} else {	
		// No "more" tag defined, so generate excerpt using wp_trim_words
		$text = wp_trim_words( strip_shortcodes( $post->post_content ), $preview_length );
	}
	
	if ( $expandable ) {
		$exp_args = array( 'text' => $text, 'post_id' => $post_id, 'text_length' => $text_length, 'preview_length' => $preview_length );
		$info .= expandable_text( $exp_args );
	} else {
		$info .= $text;
	}		
	
	// Add readmore to excerpt if enabled
	if ( $readmore ) {
		$info .= apply_filters( 'dsplycntnt_readmore_link', $readmore_link );
	}

	// Apply filters and echo
	//return apply_filters( 'dsplycntnt_get_excerpt', $info );
	return $info;

}

// WIP
// see https://developer.wordpress.org/reference/functions/get_the_excerpt/
// TODO: pare down number of args -- simplify
// TODO: build in option to submit $preview_text and $full_text as vars to be formatted for display
function expandable_text( $args = array() ) {
	
	// Init	
	$info = "";
	$full_text = "";
	
    // Defaults
	$defaults = array(
		'post_id'         	=> null,
		'text'         		=> null,
		'preview_text'      => null,
		'text_length'		=> "excerpt",
		'preview_length'	=> null, //55,
		'readmore'        	=> false,
	);
	
    // Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	//$info = "extxt args: <pre>".print_r($args, true)."</pre>";
	
	if ( empty($post_id) && empty($text) ) { return false; } // nothing to work with
	
	// Get the text and preview_text
	if ( $text ) {
	
		$info .= "<!-- extxt set full_text to text -->";
		$full_text = $text;
		
		// If no preview_text was provided, make an excerpt
		if ( empty($preview_text) ) {
			$info .= "<!-- extxt preview_text is empty -->";
			//
		}
	
	} else if ( $post_id ) {
	
		$post = get_post( $post_id );
		if ( has_excerpt( $post_id ) ) { 
			$excerpt = $post->post_excerpt; // ??
		} else {
			$excerpt = get_the_excerpt($post_id);
		}
		$preview_text = $excerpt;
		if ( $text_length == "full_text" ) {
			$full_text = $post->post_content;			
		} else {
			$full_text = $excerpt;
		}	
		
		// If a preview_length has been set, adjust the preview_text as needed
		// WIP! Needs to be tested.
		if ( $preview_length  ) {
		
			// TODO fix the following in terms of handling html tags within the text
			//$stripped_text = wp_strip_all_tags($text);
			$split = explode(" ", $preview_text); // convert string to array
			$len = count($split); // get number of words in text
			//$info .= "<pre>".print_r($split, true)."</pre>";
		
			if ( $len > $preview_length ) {
			
				// The excerpt-as-preview_text is longer than the set preview length, so we need to truncate it
				$info .= "<!-- extxt len > preview_length -->";
		
				$firsthalf = array_slice($split, 0, $preview_length);
				$secondhalf = array_slice($split, $preview_length, $len - 1);
		
				$preview_text = implode(' ', $firsthalf) . '<span class="extxt spacer">&nbsp;</span><span class="extxt more-text readmore">more</span>';
				$full_text = implode(' ', $secondhalf);
			
			}		
		
		}
	}
	
	// Check to be sure the full_text is actually longer than the preview_text (if it's not there's no point in expanding/collapsing)
	if ( strlen(wp_strip_all_tags($preview_text)) != strlen(wp_strip_all_tags($full_text)) ) {
	
		$info .= "<!-- extxt preview_text not same as full_text -->";
		
		//$info = '<p class="extxt expandable-text">'.$text.'</p>';
		$info .= '<p class="expandable-text" >';
		$info .= '<span class="extxt text-preview" >';
		$info .= $preview_text;
		$info .= '</span>';
		$info .= '<span class="extxt spacer">&nbsp;</span><span class="extxt more-text readmore">more</span>';
		$info .= '<span class="extxt text-full hide">';
		$info .= $full_text;
		$info .= '</span>';
		$info .= '<span class="extxt spacer hide">&nbsp;</span><span class="extxt less-text readmore hide">less</span>';
		$info .= '</p>';
	
	} else if ( strlen($full_text) > 0 ) {
	
		$info = '<p class="extxt">'.$full_text.'</p>';
		
	}
	
	return $info;
}

//}

/**
 * Prints HTML with meta information for the categories, tags.
 * This function is a version of twentysixteen_entry_meta
 */
function birdhive_entry_meta() {
	
    $format = get_post_format();
    if ( current_theme_supports( 'post-formats', $format ) ) {
        printf(
            '<span class="entry-format">%1$s<a href="%2$s">%3$s</a></span>',
            sprintf( '<span class="screen-reader-text">%s </span>', _x( 'Format', 'Used before post format.', 'twentysixteen' ) ),
            esc_url( get_post_format_link( $format ) ),
            get_post_format_string( $format )
        );
    }

    if ( 'post' === get_post_type() ) {
        //birdhive_entry_taxonomies(); // tmp disabled until fcn has been created based on twentysixteen version
    }

}


/*** TAXONOMY-RELATED FUNCTIONS ***/

// Function to determine default taxonomy for a given post_type, for use with display_posts shortcode, &c.
function birdhive_get_default_taxonomy ( $post_type = null ) {
    switch ($post_type) {
        case "post":
            return "category";
        case "page":
            return "page_tag"; // ??
        case "event":
            return "event-categories";
        case "product":
            return "product_cat";
        case "repertoire":
            return "repertoire_category";
        case "person":
            return "person_category";
        case "sermon":
            return "sermon_topic";
        default:
            return "category"; // default -- applies to type 'post'
    }
}

// Function to determine default category for given page, for purposes of Recent Posts &c.
function birdhive_get_default_category () {
	
	$default_cat = "";
	
    if ( is_category() ) {
    
        $category = get_queried_object();
        $default_cat = $category->name;
        
    } else if ( is_single() ) {
    
        $categories = get_the_category();
        $post_id = get_the_ID();
        $parent_id = wp_get_post_parent_id( $post_id );
        //$parent = $post->post_parent;
        // WIP...
        
    }
	
	if ( ! empty( $categories ) ) {
		
		//echo esc_html( $categories[0]->name );	
		// WIP...
			 
	} else if ( empty($default_cat) ) {
        
		// TODO: generalize so that this isn't so STC-specific
		// TODO: make this more efficient by simply checking to see if name of Page is same as name of any Category
		// Get page slug
		if ( is_page() ) {
			global $post;
			$page_slug = $post->post_name;
			// Does a taxonomy term exist matching this slug?
			$default_cat = term_exists( $page_slug );
		}
		// Get default cat as per plugin options? WIP
		
		/*
		if ( is_page('Families') ) {
			$default_cat = "Families";
		} else if (is_page('Giving')) {
			$default_cat = "Giving";
		} else if (is_page('Music')) {
			$default_cat = "Music";
		} else if (is_page('Outreach')) {
			$default_cat = "Outreach";
		} else if (is_page('Parish Life')) {
			$default_cat = "Parish Life";
		} else if (is_page('Rector')) {
			$default_cat = "Rector";
		} else if (is_page('Theology')) {
			$default_cat = "Theology";
		} else if (is_page('Worship')) {
			$default_cat = "Worship";
		} else if (is_page('Youth')) {
			$default_cat = "Youth";
		} else {
			$default_cat = "Latest News";
		}
		*/
	}
	//$info .= "default_cat: $default_cat<br />";
	//echo "default_cat: $default_cat<br />";
	
	return $default_cat;
}


/*** Post Extras ***/

function get_post_links( $post_id = null ) {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );

	if ( empty($post_id) ) { return false; }
	
	// Init vars
	$info = "";
	//$ts_info = "";
	
	$related_links = get_field( 'related_links', $post_id );
	if ( $related_links ) {
		$info .= '<div class="related_links">';
		foreach ( $related_links as $link_id ) {
			$show = get_field( 'show_in_grid', $link_id );
			if ( !$show ) {
				continue;
			}
			// get terms for $link_id
			$terms = get_the_terms( $link_id, 'link_category' );
            //$info .= "<!-- terms: ".print_r($terms, true)." -->"; // tft
            $icon = null;
            if ( $terms ) {
                foreach ( $terms as $term ) {
                	// Does this term have a dashicon assigned?
                    $icon = get_term_meta($term->term_id, 'dashicon', true);
                    //$info .= "<!-- term: ".$term->slug." :: dashicon: ".$dashicon." -->"; // tft
                    //$info .= "term: ".print_r($term, true)." "; // tft
                    //$info .= $term->name;
                    if ( !empty($icon) ) {
                    	// TBD: fas/far? for font awesome
                    	$icon = '<span class="icons '.$icon.'"></span>'; 
                    	break;
                    }
                }
            }
			$url = get_field( 'url', $link_id );
			$text = get_field( 'link_text', $link_id );
			if ( $text ) { $title = $text; } else { $title = get_the_title( $link_id ); $text = $title; } // wip
			$class = "related"; // tft
			$target = "_blank";
			if ( $icon ) { $text = $icon; $class .= " icon"; }
			$info .= make_link( $url, $text, $title, $class, $target); // This is an SDG fcn -- TODO: check to make sure fcn exists // set up plugin dependency
		}
		$info .= '</div>';
	}
	
	return $info;
		
}

/*** RETRIEVE & DISPLAY POSTS with complex queries &c. ***/

//
// item_email
// media_file
// event_category
// post_category
//
// Perhaps rework all of this to make it object-oriented, with an "item" class of objects?

//function display_item ( $display_format = "links", $item_arr = array(), $display_atts = null, $table_fields = null, $item_ts_info = null ) {
function display_item ( $arr_item = array(), $arr_styling = array() ) {

	$info = "";
	
	extract( $arr_styling );
	
	if ( $display_format == "links" ) {	
		$info .= display_link_item($arr_item);
	} else if ( $display_format == "list" ) {
		$info .= display_list_item($arr_item);
	} else if ( $display_format == "excerpts" || $display_format == "archive" ) {
		$info .= display_post_item($arr_item);
	} else if ( $display_format == "table" ) {
		$info .= display_table_row($arr_item, $arr_styling);
	} else if ( $display_format == "grid" ) {
		$info .= display_grid_item($arr_item, $arr_styling);
	}
	
	return $info;

}

function display_link_item ( $arr_item = array() ) {
	
	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
	$info = "";
	extract( $arr_item );
	
	// Date_Str
	$info .= $item_date_str;
	
	// Item Title
	$info .= $item_title;
	
	// TODO: fine-tune text -- option to show excerpts or not -- e.g. ago board page
	if ( $item_text )  { $info .= '&nbsp;&mdash;&nbsp;<span class="description">'.$item_text.'</span>'; }
	if ( $info ) { $info = '<div class="cc_item">'.$info.'</div>'; }
	
	//if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	return $info;
			
}

function display_list_item ( $arr_item = array() ) {
	
	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
	$info = "";
	extract( $arr_item );
	
	$info .= '<li class="cc_item">';
	$info .= $item_title;
	$info .= '</li>';
	
	return $info;
			
}

function display_post_item ( $arr_item = array() ) {
	
	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );

	// Init vars
	$info = "";
	$ts_info = "";
	$item_content = "";
	$item_meta = null;
	$player_status = null;
	extract( $arr_item );
	
	// WIP
	if ( $post_id ) {
		$post_type = get_post_type($post_id);
		$ts_info .= "post_id: ".$post_id."<br />";
		$ts_info .= "post_type: ".$post_type."<br />";
	} else {
		$post_type = null;
	}
	
	$ts_info .= ">>> display_post_item <<<<br />";
	$ts_info .= "show_content: ".$show_content."<br />";
	//$ts_info .= "arr_item: <pre>".print_r($arr_item,true)."</pre>";
	
	if ( $post_id && $show_content == "full" ) {
		//$ts_info .= "Show full content<br />";
		$full_content = true;
		$post = get_post($post_id);		
		$item_content .= apply_filters('the_content', $post->post_content);
		// For event posts, get date/location info for header
		if ( $post_type == 'event' ) {
			$item_meta = do_shortcode('[event post_id="'.$post_id.'"]#_EVENTDATES<br /><span class="event_time">#_EVENTTIMES</span>[/event]');
		}
	} else {
		//$info .= $item_image;
		//$info .= $item_text; // old -- incorrect -- ??
		$item_content  = $item_text; // 240828
	}

	// This is temporary! Show email addresses until the vestry form is approved
	if ( $post_type == "person" && !is_dev_site() ) {
		$email_address = get_field( 'email_address', $post_id );
		$first_name = get_field( 'first_name', $post_id );
		if ( $email_address ) {
			$ts_info .= 'email_address: '.$email_address.'<br />';
			$item_content .= '<a class="button" href="mailto:'.$email_address.'">Email '.$first_name.'</a>';
		} else {
			$ts_info .= 'email_address: None found<br />';
		}
	}
	
	// TODO: bring this more in alignment with theme template display? e.g. content-excerpt, content-sermon, content-event...
	
	$article_class = 'cc_item';
	if ( $show_content == "full" ) {
		$article_class .= "full";
	}
	
	$info .= '<article id="post-'.$post_id.'" class="display_post_item '.$article_class.'">'; // post_class()
	$info .= '<header class="entry-header">';
	if ( isset($item_title) ) { $info .= '<h2 class="entry-title">'.$item_title.'</h2>'; }
	if ( isset($item_meta) ) { $info .= '<div class="entry-meta">'.$item_meta.'</div>'; }
	// TODO: add subtitle?
	$info .= '</header><!-- .entry-header -->';
	if ( empty($item_image) && $show_content == "full" ) {
		$img_args = array( 'post_id' => $post_id, 'img_size' => "full", 'sources' => array("featured", "gallery"), 'echo' => false );
		$info .= sdg_post_thumbnail( $img_args );
	}
	$info .= '<div class="entry-content">';
	//if ( $post_type == 'event' ) { $info .= do_shortcode('[event post_id="'.$post_id.'"]#_EVENTDATES<br /><span class="event_time">#_EVENTTIMES</span>[/event]'); }
	//if ( $post_type == 'event' ) { $info .= display_media_player( 'post_id' => $post_id, 'position' => 'above' ); }
	if ( $post_id ) { 
		$mp_info = display_media_player( array('post_id' => $post_id, 'position' => 'above', 'return' => 'array' ) );
		$info .= $mp_info['info'];
		$player_status = $mp_info['player_status'];
	}
	if ( $item_image && $player_status != "ready" ) {
		$info .= $item_image;
	}
	$info .= $item_content;
	if ( $post_id ) { $info .= display_media_player( array('post_id' => $post_id, 'position' => 'below' ) ); }
	$info .= '</div><!-- .entry-content -->';
	$info .= '<footer class="entry-footer">';
	$info .= birdhive_entry_meta( $post_id );
	$info .= '</footer><!-- .entry-footer -->';
	$info .= '</article><!-- #post-'.$post_id.' -->';

	// WIP is it possible to use template parts in this context?
	//$info .= get_template_part( 'template-parts/content', 'excerpt', array('post_id' => $post_id ) ); // 
	//$post_type_for_template = birdhive_get_type_for_template();
	//get_template_part( 'template-parts/content', $post_type_for_template );
	//$info .= get_template_part( 'template-parts/content', $post_type );
	
	if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	return $info;
			
}

function display_event_list_item ( $EM_Event ) {
	
	// Use category list item version so as to include date (as opposed to grouped version) -- see EM settings in CMS
	$info = do_shortcode( $EM_Event->output(get_option('dbem_category_event_list_item_format')) ); //$info = $EM_Event->output(get_option('dbem_event_list_item_format'));
	return $info;
			
}

function display_table_row ( $arr_item = array(), $arr_styling = array() ) {
	
	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: display_table_row", $do_log );
    
	// Init vars
	$info = "";
	$ts_info = "";
	$fields = null;
	
	extract( $arr_item );
	extract( $arr_styling );
	
	// Get/set item vars
	//$ts_info .= "<!-- item: ".print_r($item, true)."; fields: ".print_r($fields, true)." -->";
	
	// TBD: build in possibility that one field value might be item_image?
	// TBD: build in possibility that one field value might be item_text?
	
	// Start building the rows
	$info .= '<tr class="cc_item">';
	
	// Make sure we've got a proper array of fields and then loop through them to accumulate the info for display
	if ( !is_array($table_fields) ) { $arr_fields = explode(",",$table_fields); } else { $arr_fields = $table_fields; }
	
	if ( $arr_fields ) {
	
		//if ( !is_array($field_values) ) { $arr_field_values = explode(",",$field_values); } else { $arr_field_values = $field_values; }
		
		foreach ( $arr_fields as $field_name ) {
		
			$field_name = trim($field_name);
			
			if ( !empty($field_name) ) {
				
				$info .= '<td>';				
				//$info .= "[".$field_name."] "; // tft
				
				// Check for corresponding field value passed with item
				if ( isset($field_values[$field_name]) ) {
					$field_value = $field_values[$field_name];
				} else if ( $field_name == "title" && !empty($item_title) ) {
					$field_value = $item_title; // WIP!!!
				} else {
					$field_value = get_post_meta( $post_id, $field_name, true );
				}
				
				if ( is_array($field_value) ) {
					
					if ( count($field_value) == 1 ) {
						
						//$info .= "<pre>".print_r($field_value,true)."</pre>"; // tft
						
						if ( is_numeric($field_value[0]) ) {
							
							//$info .= $field_value[0]; // tft
							
							// Get post_title
							if ( function_exists( 'sdg_post_title' ) ) {
								$title_args = array( 'post' => $field_value[0], 'line_breaks' => false, 'show_subtitle' => false, 'hlevel' => 0, 'echo' => false, 'do_ts' => $do_ts );
								$value = sdg_post_title( $title_args );
								if ( empty($value) ) {
									$info .= "no title found using sdg_post_title with title_args: <pre>".print_r($title_args, true)."</pre>";
								}
							} else {
								$value = get_the_title($field_value[0]);
							}
							$info .= $value;
						} else {
							$info .= "Not is_numeric: ".$field_value[0];
						}
						
					} else {
						$info .= count($field_value).": <pre>".print_r($field_value, true)."</pre>";
					}
					
				} else {
					if ( is_numeric($field_value) && !strpos( $field_name, "year" ) ) {
						$field_value = number_format_i18n($field_value);
					}
					$info .= $field_value;
				}
				
				$info .= '</td>';
			}
		}
		
	}
	
	$info .= '</tr>';
	
	//if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }

	return $info;
	
}

function display_grid_item ( $arr_item = array(), $arr_styling = array() ) {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: display_grid_item", $do_log );
    
	// Init vars
	$info = "";
	$item_info = "";
	$ts_info = "";
	//
	$display_format = null;
	$aspect_ratio = 'square';
	//
	extract( $arr_item );
	extract( $arr_styling );
	
	//$ts_info .= "item: <pre>".print_r($arr_item, true)."</pre>";
	//$ts_info .= "arr_styling: <pre>".print_r($arr_styling, true)."</pre>";
	
	// Post Type?
	if ( $post_id ) { $post_type = get_post_type($post_id); }
	if ( isset($overlay) ) { $ts_info .= "<!-- overlay: $overlay -->"; } else { $overlay = null; }
	
	// Begin building item_info
	if ( $aspect_ratio != "square" ) {
		$hclass = "grid_rect";
		$item_title = '<h3 class="'.$hclass.'">'.$item_title.'</h3>';
		if ( !empty($item_subtitle) ) { $hclass .= " with-subtitle"; $item_subtitle = '<h4 class="subtitle">'.$item_subtitle.'</h4>'; }
	}
	$item_info .= $item_title;
	
	// Date_Str?
	if ( $item_date_str ) {
		if ( $aspect_ratio == "square" ) { $item_info .="<br />"; }
		$item_info .= $item_date_str;
	}
	
	// Subtitle?
	if ( !empty($item_subtitle) ) { $item_info .= $item_subtitle; } //"<br />".
	
	// Text/Content
	if ( !empty($item_text) ) { $item_info .= $item_text; }
	
	// Links?
	$links = get_post_links( $post_id );
	if ( $links ) { $item_info .= $links; }
	
	$flex_box_classes = "flex-box ".$aspect_ratio;
	if ( !empty($spacing) ) { $flex_box_classes .= " ".$spacing; }
	if ( $overlay == "true" || $overlay == "fullover" ) {
		$overclass = "overlay";
		$flex_box_classes .= " overlaid";
		if ( $overlay == "fullover" ) { $overclass .= " fullover"; }
	} else {
		$overclass = null;
	}
	$info .= '<div class="'.$flex_box_classes.'">';
	//
	if ( !($overlay) && $aspect_ratio != "square" ) {
		$info .= '<div class="item_info">'.$item_info.'</div>';
	}
	// Show the item image
	$info .= '<div class="flex-img">';
	$info .= $item_image;
	$info .= '</div>';
	//
	if ( $overclass ) {
		$info .= '<div class="'.$overclass.'">'.$item_info.'</div>';
	} else if ( $aspect_ratio == "square" ) {
		$info .= '<div class="item_info">'.$item_info.'</div>';
	}
	$info .= '</div>';
	
	// Troubleshooting info
	if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	return $info;
	
}

// TODO:
// * separate process of assembling item arr (somewhat different for collection vs. display_posts) from fcn to display items in collection
// * 

// WIP build item_arr
// TODO: streamline and generalize so that it's possible (e.g.) to pass just item_title, item_text for grid display
// TODO: simplify -- item atts, display atts -- don't need all of them for every display_format etc.
//function build_item_arr ( $item = array(), $item_type = null, $display_format = "list", $aspect_ratio = null, $collection_id = null ) {
function build_item_arr ( $item, $arr_styling = array() ) { // TODO: come up with better name for $arr_styling -- which also includes atts like collection_id, not just styling atts like aspect_ratio
	
	// Init vars
	$arr_item = array();
	$ts_info = "";
	//
	$link_posts = "true"; // default in case it's not set by arr_styling
	$display_format = null;
	$aspect_ratio = 'square';
	extract( $arr_styling );
	//
	$post = null; // is this necessary?
	$post_id = null;
	//	
	$item_id = null; // If this is a header or subheader, there should be an item_id to use for internal page links
	$item_title = null;
	$item_subtitle = null;
	$item_text = null;
	$item_image = null;
	$item_date_str = null;
	$field_values = array();
	//
	$item_url = null;
	$item_link_target = null;
	$image_id = null;
	$hlevel = null;
	//
	$set_anchors = false; // wip
	
	if ( !isset($show_content) ) { $show_content = null; }
	if ( !isset($aspect_ratio) ) { $aspect_ratio = "square"; }
	
	// Is the $item a post object, an array, or simply an ID?
	if ( is_object($item) ) { // item is post object, e.g. when called via display_posts shortcode
	
		$post = $item;
		
	} else if ( is_array($item) ) {
	
		$ts_info .= '[bia] extract item: <pre>'.print_r($item,true).'</pre>';
		extract( $item );
	
		if ( isset($post_object) && isset($post_object[0]) ) {
			$post = $post_object[0];
		} else if ( $post_id ) {
			$post = get_post( $post_id );
		} else if ( is_numeric($item) ) {
			$post = get_post( $item );
		}
		
	} else if ( is_numeric($item) ) {
	
		$post = get_post( $item );
		
	}
	
	// Item Image
	// If this is a post via a collection, check to see if there's an image override
	if ( !empty($item_image) ) { $image_id = $item_image; }
	//
	
	//$ts_info .= 'BIA -- item: <pre>'.print_r($item, true).'</pre><br />';
	//$ts_info .= 'BIA -- item: '.print_r($item, true).'<br />';
	$ts_info .= '[bia] item_type: '.$item_type.'<br />';
	
	// WIP -- image sizes
	if ( $display_format == "excerpts" || $display_format == "archive" ) {
		if ( $show_content == "full" ) {
			$img_size = "full";
		} else {
			$img_size = array( 250, 250 ); //$img_size = "post-thumbnail";
		}		
	} else if ( $aspect_ratio == "square" ) {
		$img_size = "grid_crop_square";
	} else {
		$img_size = "grid_crop_rectangle";
	}
	
	if ( $post && ( $item_type == "post" || $item_type == "event" ) ) {

		//$ts_info .= '<!-- post: <pre>'.print_r($post, true).'</pre> -->';
		$post_type = $post->post_type;
		$post_id = $post->ID;
		$ts_info .= '[bia] post_type: '.$post_type.' / post_id: '.$post_id."<br />";
		
		// Item Title
		// If there was no title override set via collection, then get a title
		// TODO: deal w/ prefix/suffix options?
		if ( empty($item_title) ) {
			$ts_info .= '[bia] get item_title<br />';
			// If a short_title is set, use it. If not, use the post_title
			$short_title = get_post_meta( $post_id, 'short_title', true );
			if ( !empty($short_title) ) {
				$ts_info .= ' >> use short_title: '.$short_title.'<br />';
				$item_title = $short_title;
			} else if ( $post_type == "person" ) {
				$title_args = array( 'person_id' => $post_id, 'override' => 'post_title', 'show_job_title' => true );
				$item_title = get_person_display_name($title_args)['info'];
			} else if ( function_exists( 'sdg_post_title' ) ) {
				$ts_info .= ' >> sdg_post_title<br />';
				if ( !isset($show_subtitle) ) { $show_subtitle = true; }
				$title_args = array( 'post' => $post_id, 'line_breaks' => true, 'show_subtitle' => $show_subtitle, 'echo' => false, 'hlevel' => 0, 'hlevel_sub' => 0 ); //, 'do_ts' => $do_ts
				$item_title = sdg_post_title( $title_args );
			} else {
				$ts_info .= ' >> use get_the_title()<br />';
				$item_title = get_the_title($post_id);// Retrieve and style the subtitle
				if ( empty($item_subtitle) ) { $item_subtitle = get_post_meta( $post_id, 'subtitle', true ); }
				if ( !empty($item_subtitle) ) { $item_subtitle = '<span class="item_subtitle">'.$item_subtitle.'</span>'; }
			}
		}
		
		// Item URL
		// TODO: deal w/ possibility of multiple external URLs
		// WIP 231127, 240828
		if ( empty($item_url) && $link_posts !== "false" ) {
			$item_url = get_the_permalink( $post_id ); //if ( empty($item_url) ) { $item_url = get_the_permalink( $post_id ); }
		} else {
			$ts_info .= '[bia] link_posts: '.$link_posts."<br />";
		}
		$ts_info .= '[bia] item_url: '.$item_url."<br />";
		
		// +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+
		
			
		// No collection image? Then look for a image via the post record
		if ( ! $image_id && $show_image !== 'false' ) {
			// WIP
			//img_size if show_content = full...
			$img_args = array( 'post_id' => $post_id, 'format' => 'excerpt', 'img_size' => $img_size, 'sources' => "all", 'echo' => false, 'return_value' => 'id' );
			$image_id = sdg_post_thumbnail ( $img_args );
			$ts_info .= '[bia] sdg_post_thumbnail -> image_id: '.$image_id.'<br />';		
		}
		// +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+
		
		// Item Excerpt/Text
		if ( empty($item_text) ) {		
			if ( isset($show_content) && $show_content == 'excerpts' ) {
				$item_text = get_the_excerpt( $post_id );
			}
			// WIP
			if ( $post_type == "link" ) {
				$item_text = get_field( 'description', $post_id );
				if ( empty($item_text) ) {
					$item_text = get_field( 'location', $post_id );
				}
			}
		}
		/*if ( function_exists('is_dev_site') && is_dev_site() ) {
			$exp_args = array( 'post_id' => $post_id ); // $exp_args = array( 'text' => $text, 'post_id' => $post_id, 'text_length' => $text_length, 'preview_length' => $preview_length );
			$item_text = expandable_text( $exp_args );
			//$info .= dsplycntnt_get_excerpt( array('post_id' => $post_id, 'expandable' => $expandable, 'text_length' => $text_length, 'preview_length' => $preview_length ) );				
		} else {
			$item_text = get_the_excerpt( $post_id ); //$info .= $post->post_excerpt;
		}*/
		
		// Item Date -- for events
		if ( post_type_exists('event') && $post_type == 'event' ) { 
			$event_start_datetime = get_post_meta( $post_id, '_event_start_local', true );
			if ( $event_start_datetime ) {
				$item_date_str = date_i18n( "l, F d, Y \@ g:i a", strtotime($event_start_datetime) );
				$item_date_str = str_replace(array('am','pm'),array('a.m.','p.m.'),$item_date_str); // STC >> TODO: generalize formatting options
			}
		}
	
	} else if ( $item_type == "tax_term" || $item_type == "event_category" || $item_type == "post_category" ) {
		
		if ( empty($term_id) ) {
			if ( $item_type == "event_category" ) { $term_id = $event_category; } else { $term_id = $post_category; }
		}
		
		// Get term
		$term = get_term( $term_id ); // $term = get_term( $term_id, $taxonomy );
		if ( empty($item_id) ) { $item_id = $term->slug; }
		
		// If there's a title override, use it. Otherwise, use the taxonomy term name.
		if ( empty($item_title) ) {
			$item_title = $term->name;	
		}
		// TODO: append term description, if any?
		
		$item_url = get_term_link( $term_id) ;
		
		// Get the taxonomy image, if any has been set
		if ( $item_type == "event_category" ) { 
			// TMP solution:
			global $wpdb; 
			//wpstc_em_meta
			$image_id = $wpdb->get_var('SELECT meta_value FROM '.EM_META_TABLE." WHERE object_id='".$term_id."' AND meta_key='category-image-id' LIMIT 1"); // 288081
			/* // TODO: figure out how to get EM taxonomy image without direct DB query
			$image_url = ""; // tft
			//$EM_Tax_Term = new EM_Taxonomy_Term($term_id, 'term_id'); 
			//$item_image = $EM_Tax_Term->get_image_url();
			$item_image = "";
			*/
		} else {
			$taxonomy_featured_image = get_term_meta( $term_id, 'taxonomy_featured_image', true );
			if ( $taxonomy_featured_image ) { $image_id = $taxonomy_featured_image; } else { $image_id = null; }
		}
		
	} else { // if ( $item_type == ??? )
	
		$arr_item = $item; // init
		
		// Item URL
		if ( $item_type == "page_link" && isset($page_link) ) {
			$item_url = $page_link;
			//$ts_info .= '<!-- page_link item: <pre>'.print_r($item, true).'</pre> -->'; // tft
		} else if ( $item_type == "email" ) {
			if ( !empty($item_email) ) {
				$item_url = "mailto:".$item_email;
			}
		}
		
		/* WIP
		$media_file = $item['media_file'];
		$event_category = $item['event_category'];
		$post_category = $item['post_category'];
		//if ( isset($row['row_type']) ) { $row_type = $row['row_type']; } else { $row_type = null; }
		*/
					
	}
	
	// Set Link Target
	if ( empty($item_link_target) ) {
		if ( $display_format == "table" ) {
			$item_link_target = "_blank";
		} else if ( $post && $item_type == "link" ) {
			$item_link_target = "_blank";
		} else {
			//$item_link_target = "_blank";
		}
	}
	if ( !empty($item_link_target) ) { $link_target = ' target="'.$item_link_target.'"'; } else { $link_target = ""; }
	
	// Style the title
	$ts_info .= '[bia] item_title (before styling): '.$item_title."<br />";
	if ( !empty($item_title) ) {
		$item_title = '<span class="item_title">'.$item_title.'</span>';
		// Wrap the title in a hyperlink, if a URL has been set	OR if the item is linked to modal content		
		if ( $item_type == "modal" || $item_link_target == "modal" ) {
			$dialog_id = sanitize_title($item_title); // tmp/wip
			$item_title = '<a href="#!" id="dialog_handle_'.$dialog_id.'" class="dialog_handle">'.$item_title.'</a>'; 
		} else {
			if ( !empty($item_url) ) { $item_title = '<a href="'.$item_url.'" rel="bookmark"'.$link_target.'>'.$item_title.'</a>'; }
		}
		if ( !empty($hlevel) ) { // empty($image_id)
			$item_title = '<h'.$hlevel.' id="'.$item_id.'" class="collection_group">'.$item_title.'</h'.$hlevel.'>';
			if ( $hlevel <= 2 && $set_anchors == true ) { $item_title = anchor_link_top().$item_title; }
		}
	}
	
	// Finalize the item image html based on the image_id, if any
	// WIP: if show_content=="full", show full-size image, not medium thumb... 10/24
	$item_image = ""; // init
	if ( !empty($image_id) && $show_image !== 'false' ) {
	
		// WIP: TS sizing of grid images e.g. https://www.saintthomaschurch.org/whos-who-new-revised/#vestry
		$ts_info .= '[bia] image_id: '.print_r($image_id,true).'<br />';	
		$ts_info .= '[bia] aspect_ratio: '.$aspect_ratio.'<br />';
		$ts_info .= '[bia] img_size: '.print_r($img_size, true).'<br />';	
		//wp_get_attachment_image( int $attachment_id, string|int[] $size = 'thumbnail', bool $icon = false, string|array $attr = '' ): string
		//$img_attr = array ( 'sizes' => "(max-width: 600px) 100vw, 100vw" );
		$item_image = wp_get_attachment_image( $image_id, $img_size );
		
		if ( !empty($item_image) ) {
			$img_class = "bia ".$display_format."_item_image";
			if ( $display_format == "excerpts" || $display_format == "archive" ) {
				$img_class .= " post-thumbnail sdg no-caption float-left";
			}
			if ( !empty($dialog_id) && ( $item_type == "modal" || $item_link_target == "modal" ) ) {
				$item_image = '<a href="#!" id="dialog_handle_'.$dialog_id.'" class="'.$img_class.' dialog_handle">'.$item_image.'</a>'; 
			} else if ( !empty($item_url) ) { 
				$item_image = '<a href="'.$item_url.'" rel="bookmark"'.$link_target.' class="'.$img_class.'">'.$item_image.'</a>';
			} else {
				$item_image = '<div class="'.$img_class.'">'.$item_image.'</div>';
			}
		}
		
	} else {		
		$ts_info .= '[bia] No image.<br />';
		//$item_ts_info .= "arr_item: <pre>".print_r($arr_item, true)."</pre>";
	}
	
	// Set the array values based on what we've found
	// Some of the values will be null, but that will prevent undefined index errors
	$arr_item['post_id'] = $post_id;
	$arr_item['show_content'] = $show_content;
	$arr_item['item_title'] = $item_title;
	$arr_item['item_subtitle'] = $item_subtitle;
	$arr_item['item_text'] = $item_text;
	$arr_item['item_image'] = $item_image;
	$arr_item['item_date_str'] = $item_date_str;
	$arr_item['ts_info'] = $ts_info;
	$arr_item['field_values'] = $field_values;
	
	// Return the assembled item array
	return $arr_item;
		
} // END function build_item_arr


// Display a collection of post items
function birdhive_display_collection ( $args = array() ) {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );

	// Init vars
	$info = "";
	$ts_info = "";
	
	// Defaults
	$defaults = array(
		'collection_id'		=> null,
		'link_posts'		=> true,
		'show_subtitles'	=> null,
		'show_content'		=> null,
		'show_images'		=> null,
		'table_fields'		=> array(),
		'table_headers'		=> array(),
		'table_totals'		=> array(), // field names
		'num_cols'			=> "3",
		'aspect_ratio'		=> "square",
	);
	
    // Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	//$ts_info .= "birdhive_display_collection >> args: <pre>".print_r($args, true)."</pre>";

	if ( is_array($display_atts) ) { extract( $display_atts ); } // one of args
	//$ts_info .= "display_atts: <pre>".print_r($display_atts, true)."</pre>";
	
	// Get args from array
	if ( isset($collection_id) ) {
		
		$ts_info .= "collection_id: $collection_id<br />";
		
		$content_type = "mixed"; // tft
		$display_format = get_field('display_format', $collection_id);
    	$items = get_field('collection_items', $collection_id); // ACF collection item repeater field values
		$aspect_ratio = get_field('aspect_ratio', $collection_id);
		//
		if ( $display_format == "table" ) { 
			$table_fields = get_field('table_fields', $collection_id);
			$table_headers = get_field('table_headers', $collection_id);
			$table_totals = get_field('table_totals', $collection_id);
		}
		if ( $display_format == "grid" ) { $num_cols = get_field('num_cols', $collection_id); }
		//$content_type = $args['content_type']; -- probably mixed, but could be posts or whatever, collection of single type of items -- would have to loop to determine

	} else {
	
		$ts_info .= "No collection_id set<br />";
		
		if ( $display_format == "table" && isset($display_atts['fields']) ) {
			$table_fields = $display_atts['fields'];
			$table_headers = $display_atts['headers'];
			$table_totals = $display_atts['totals'];
		}
		if ( $display_format == "grid" && isset($display_atts['cols']) ) { $num_cols = $display_atts['cols']; }
		if ( isset($display_atts['aspect_ratio']) ) { $aspect_ratio = $display_atts['aspect_ratio']; } // TODO: either eliminate this, or make it so that aspect_ratio actually ever is passable as an arg, via mods to args array of display_posts, for example...
				
	}
	
	// Show TS info based on display_format (tft)
	if ( $display_format == "table" ) {
		$ts_info .= "display_format: $display_format<br />";
		$ts_info .= "table_fields: ".print_r($table_fields, true)."<br />";
		$ts_info .= "table_headers: ".print_r($table_headers, true)."<br />";
		$ts_info .= "table_totals: ".print_r($table_totals, true)."<br />";
	}
	$col_totals = array();
	
	// List/table/grid header or container
	$info .= collection_header ( $display_format, $num_cols, $aspect_ratio, $table_fields, $table_headers );
	
	if ( $display_format == "table" ) {
		$info .= '<tbody>';
	}
	//$info .= "+~+~+~+~+~+~+ collection items +~+~+~+~+~+~+<br />";
	
	// For each item, get content for display in appropriate form...
	foreach ( $items as $item ) {
	
		$item_info = "";
		$item_ts_info = "";
		$arr_item = array();
		$image_id = null;
		
		//$item_ts_info .= "item: <pre>".print_r($item, true)."</pre>";
		
		if ( is_array($item) && isset($item['item_type']) ) {
			$item_type = $item['item_type'];
		} else if ( $content_type == "posts" ) {
			$item_type = "post";
		} else if ( $content_type == "events" ) {
			$item_type = "event";
		} else {
			$item_type = "UNKNOWN!";
		}
		
		//$item_ts_info .= "item_type: ".$item_type."<br />"; //$item_ts_info .= "<!-- item_type: ".$item_type." -->";
		
		if ( $item_type == "event" && ( $display_format == "excerpts" || ( $display_format == "archive" && $show_content != 'full' ) ) ) {
		
			$item_info .= display_event_list_item( $item );
			
		} else {
		
			// Assemble the array of styling parameters
			$arr_styling = array( 'item_type' => $item_type, 'display_format' => $display_format, 'link_posts' => $link_posts, 'show_subtitle' => $show_subtitles, 'show_content' => $show_content, 'show_image' => $show_images, 'aspect_ratio' => $aspect_ratio, 'table_fields' => $table_fields, 'collection_id' => $collection_id, 'do_ts' => $do_ts ); // wip
			//$item_ts_info .= "item: <pre>".print_r($item, true)."</pre>";
			//$item_ts_info .= "arr_styling: <pre>".print_r($arr_styling, true)."</pre>";
			
			// Assemble the arr_item
			if ( $item_type == "custom_item" ) {
				$arr_item = $item;
			} else {
				$arr_item = build_item_arr ( $item, $arr_styling );
			}
			
			// Get content for display in appropriate form...
			//$item_args = array( 'content_type' => $content_type, 'display_format' => $display_format, 'item' => $item );
			//$item_info .= display_item($display_format, $arr_item, $display_atts, $table_fields, $item_ts_info); //$item_info .= display_item($item_args);
			$item_info .= display_item ( $arr_item, $arr_styling );
		
		}
		
		
		if ( isset( $arr_item['item_content'] ) && $item_type == "modal" || ( isset( $arr_item['item_link_target'] ) && $arr_item['item_link_target'] == "modal" ) ) { 
			// modal_content...
			//<div id="dialog_content_contact_us" class="dialog dialog_content"></div>
			$dialog_id = sanitize_title($arr_item['item_title']); // wip 
			$info .= '<div id="dialog_content_'.$dialog_id.'" class="dialog dialog_content">'.$arr_item['item_content'].'</div>';
		}
		
		//
		if ( !empty($table_totals) ) {
			foreach ( $table_totals as $field_name ) {		
				//$item_ts_info .= "table_totals field_name '".$field_name."'<br />";		
				if ( isset( $arr_item['field_values'][$field_name] ) ) { // isset( $arr_item[$field_name] ) || 
					//$item_ts_info .= "item value for field_name '".$field_name."': ".$arr_item['field_values'][$field_name]."<br />";
					if ( isset($col_totals[$field_name]) ) {
						$col_totals[$field_name] += (float) $arr_item['field_values'][$field_name];
						//$item_ts_info .= "add to total: ".(float) $arr_item['field_values'][$field_name]."<br />";
					} else {
						$col_totals[$field_name] = (float) $arr_item['field_values'][$field_name];
						//$item_ts_info .= "set col_totals $field_name: ".(float) $arr_item['field_values'][$field_name]."<br />";
					}
				}
			}
		}
		
		// Add the item_info to the info for return/display		
		$info .= $item_info;
		$ts_info .= $item_ts_info;
		
	} // END foreach items as item
	
	if ( $display_format == "table" ) {
		$info .= '</tbody>';
	}
		
	// Display column totals, if applicable
	if ( $display_format == "table" && !empty($col_totals) ) {
		// WIP
		//$info .= "col_totals: <pre>".print_r($col_totals, true)."</pre>";
		if ( is_array($table_fields) ) { $arr_fields = $table_fields; } else { $arr_fields = explode(",",$table_fields); }
		$info .= '<tfoot>';
		$info .= '<tr class="totals">';
		foreach ( $table_fields as $field_name ) {		
			if ( array_key_exists( $field_name, $col_totals ) ) {
				$col_value = $col_totals[$field_name];
				if ( is_numeric($col_value) ) { $col_value = number_format_i18n($col_value); }
				$info .= "<td>".$col_value."</td>";
			} else {
				$info .= "<td>--</td>"; // ".$field_name."
			}
		}
		$info .= '</tr>';
		$info .= '</tfoot>';
	}
	
	// List/table/grid footer or close container
	$info .= collection_footer ( $display_format );
	
	if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	// Return info for display
	return $info;
	
} // END function birdhive_display_collection ( $args = array() ) 

// TODO: add options for collection_SUBheaders... e.g. for group/subgroups/personnel; links displayed grouped by link categories; etc.
function collection_header ( $display_format = null, $num_cols = 3, $aspect_ratio = "square", $fields = null, $headers = null ) {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );

	// Init vars
	$info = "";
	$ts_info = "";
	
	$ts_info .= "<!-- +~+~+~+~+~+~+ collection_header +~+~+~+~+~+~+ -->";
	
	if ( $display_format == "list" ) {
	
		$info .= '<ul>';
		
	} else if ( $display_format == "links" ) {
	
		//$info .= '';
		
	} else if ( $display_format == "excerpts" || $display_format == "archive" ) {
	
		$info .= '<div class="posts_archive">';
		
	} else if ( $display_format == "table" ) {
	
		//$ts_info .= "fields: <pre>".print_r($fields, true)."</pre>";
		//$ts_info .= "headers: <pre>".print_r($headers, true)."</pre>";
		
		$info .= '<table class="posts_archive">'; //$info .= '<table class="posts_archive '.$class.'">';
		
		// Make header row from field names
		if ( !empty($fields) ) {
		
			$info .= "<tr>"; // prep the header row
		
			// Create array from fields string, as needed
			if ( is_array($fields) ) { $arr_fields = $fields; } else { $arr_fields = explode(",",$fields); }
			//$info .= "<td>".$fields."</td>"; // tft
			//$info .= "<td><pre>".print_r($arr_fields, true)."</pre></td>"; // tft
		
			if ( !empty($headers) ) {
			
				// Create array from headers string, as needed
				if ( is_array($headers) ) { $arr_headers = $headers; } else { $arr_headers = explode(",",$headers); }
			
				foreach ( $arr_headers as $header ) {
					$header = trim($header);
					if ( $header == "-" ) { $header = ""; }
					$info .= "<th>".$header."</th>";
				}
			
			} else {
			
				// If no headers were submitted, make do with the field names
				foreach ( $arr_fields as $field_name ) {
					$field_name = ucfirst(trim($field_name));
					$info .= "<th>".$field_name."</th>";
				}
			
			}
		
			$info .= "</tr>"; // close out the header row
		}
	
	} else if ( $display_format == "grid" ) {
	
		$colclass = digit_to_word($num_cols)."col";
		//if ( $class ) { $colclass .= " ".$class; }
		$info .= '<div class="flex-container '.$colclass.' '.$aspect_ratio.'">';
	
	} else {
		$info .= '<!-- display_format '.$display_format.' not matched -->';
	}
	
	if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= $ts_info; } //if ( $do_ts && !empty($ts_info) ) { $info .= $ts_info; } //if ( $do_ts && !empty($ts_info) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
	
	// Return info for display
	return $info;
}

function collection_footer ( $display_format = null ) {

	$info = "";
	//$info .= "+~+~+~+~+~+~+ collection_footer +~+~+~+~+~+~+<br />";
	
	if ( $display_format == "links" ) {
		$info .= anchor_link_top();
	} else if ( $display_format == "list" ) {
		//if ( ! is_archive() && ! is_category() ) { $info .= '<li>'.$category_link.'</li>'; }
		$info .= '</ul>';
		$info .= anchor_link_top();
	} else if ( $display_format == "excerpts" || $display_format == "archive" ) {
		$info .= '</div>';
		$info .= anchor_link_top();
	} else if ( $display_format == "table" ) {
		$info .= '</table>';
	} else if ( $display_format == "grid" ) {
		$info .= '</div>';
	}
	
	return $info;
	
}

// Get an array of posts by processing/assembling args and passing them to WP_Query
// Among other things, this function can deal w/ special cases like sermon series, accept strings of slugs and turn them into arrays, etc. -- issues related to CPTs and taxonomies
function birdhive_get_posts ( $args = array() ) {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    global $wpdb;
    
    // Init vars
    $arr_info = array();
    $ts_info = "";
    //
    $get_by_ids = false;
    $get_by_slugs = false;
    $category_link = null;
    //
    $ts_info .= "[bgp] args as passed to birdhive_get_posts: <pre>".print_r($args,true)."</pre>";

    // Defaults
	$defaults = array(
		'limit'				=> null,
		'posts_per_page'  	=> -1,
		'_search_title'		=> null, // The search_title is a special placeholder field handled by the birdhive_posts_where fcn
		'_meta_or_tax'		=> null, // TODO: deal w/ underscore?
		'post_type'			=> null,
		'post_status'		=> 'publish',
		'order'				=> null,
		'orderby'			=> null,
		'groupby'			=> null,
		//
		'meta_query'        => array(),
		'tax_query'			=> array(),
		'return_fields'		=> 'all',
		//
		'ids'				=> null,
		'slugs'				=> null,
		//
		'taxonomy'			=> null,
		'tax_terms'			=> null,
		'tax_field'			=> 'slug', // init -- in some cases will want to use term_id
		'include_children'	=> true,
		//
		'category'			=> null,
		'meta_key'			=> null,
		'meta_value'		=> null,
		'series'			=> null, // For Events & Sermons, if those post_types exist for the current application
		//
		'scope'				=> null,
		'date_field'		=> null, // e.g. sermon_date, transaction_date...
		//
		'do_ts'				=> false,
	);
	
    // Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
    
    // Limit, aka posts_per_page, aka num posts to retrieve
    if ( !empty($limit) ) { $posts_per_page = $limit; }
    if ( empty($post_type) ) {
    	if ( !empty($ids) ) {
    		$post_type = 'any';
    	} else {
    		$post_type = 'post';
    	}
    }
    
    // Set up basic query args
    $wp_args = array(
		'post_type'       => $post_type,
		'post_status'     => $post_status,
		'posts_per_page'  => $posts_per_page,
        'fields'          => $return_fields,
	);
    
    // Custom parameters
    if ( $_search_title ) { $wp_args['_search_title'] = $_search_title; }
    if ( $_meta_or_tax ) { $wp_args['_meta_or_tax'] = $_meta_or_tax; }
    
    // Order (ASC/DESC)
    if ( $order ) { $wp_args['order'] = $order; }
    
    // Posts by ID
    // NB: if IDs are specified, ignore most other args
    if ( !empty($ids) ) {
        
        $ts_info .= "Getting posts by IDs: ".$ids."<br />";
        
        // Turn the list of IDs into a proper array
		$post_ids         = array_map( 'intval', birdhive_att_explode( $ids ) );
		$wp_args['post__in'] = $post_ids;
        $wp_args['orderby']  = 'post__in';
        $get_by_ids = true;
        
	}
    
    // Posts by slug
    // NB: if slugs are specified, ignore most other args
    if ( !empty($slugs) ) {
        
        $ts_info .= "Getting posts by slugs: ".$slugs;
        
        // Turn the list of slugs into a proper array
		$post_slugs = birdhive_att_explode( $slugs );
		$wp_args['post_name__in'] = $post_slugs;
        $wp_args['orderby'] = 'post_name__in';
        $get_by_slugs = true;
        
	}
    
    // If not getting posts by ID or by slugs, build the Tax and Meta Queries
    if ( !$get_by_ids && !$get_by_slugs ) {
        
        // Deal w/ taxonomy args
        if ( $category && $category != "all" && empty($taxonomy) ) {
            $taxonomy = 'category';
            $tax_terms = $category;
        }
        $cat_id = null; // init

        // If not empty tax_terms and empty taxonomy, determine default taxonomy from post type
        if ( empty($taxonomy) && !empty($tax_terms) ) {
            $ts_info .= "Using birdhive_get_default_taxonomy"; // tft
            $taxonomy = birdhive_get_default_taxonomy($post_type);
        }

        // Taxonomy operator
        if ( $tax_terms && strpos($tax_terms,"+") !== false ) {
        
        	$arr_terms = explode("+",$tax_terms);
        	
        	// Build tax_query
			$tax_query = array (
				'relation' => 'AND'
			);
			foreach ( $arr_terms as $term ) {
				$tax_query[] = array(
					'taxonomy'  => $taxonomy,
					'field'     => $tax_field,
					'terms'     => $term,
					'include_children' => $include_children,
					'operator'  => 'IN',
				);
        	}
        	
        } else if ( $tax_terms && strpos($tax_terms,"NOT-") !== false ) {
        	// WIP
        	// What if there are multiple terms, and not all are negated?
        	if ( strpos($tax_terms,",") !== false ) {
        		
        		// Group together terms to include/exclude
        		$arr_terms = explode(",",$tax_terms);
        		$terms_in = "";
        		$terms_out = "";
        		foreach ( $arr_terms as $term ) {
        			if ( strpos($term,"NOT-") !== false ) {
        				$term = str_replace("NOT-","",$term);
        				$terms_out .= $term.",";
        			} else {
        				$terms_in .= $term.",";
        			}
        		}
        		
        		// Trim trailing commas
        		if ( substr($terms_in, -1) == ',' ) {
					$terms_in = substr($terms_in, 0, -1);
				}
				if ( substr($terms_out, -1) == ',' ) {
					$terms_out = substr($terms_out, 0, -1);
				}
				
        		// Build tax_query
        		$tax_query = array (
        			'relation' => 'AND',
        			array(
						'taxonomy'  => $taxonomy,
						'field'     => $tax_field,
						'terms'     => $terms_in,
						'include_children' => $include_children,
						'operator'  => 'IN',
					),
        			array(
						'taxonomy'  => $taxonomy,
						'field'     => $tax_field,
						'terms'     => $terms_out,
						'include_children' => $include_children,
						'operator'  => 'NOT IN',
					)
        		);    		
                
        	} else {
        		$tax_terms = str_replace("NOT-","",$tax_terms);
            	$tax_operator = 'NOT IN';
        	}
            
        } else {
            $tax_operator = 'IN';
        }

        // Post default category, if applicable -- WIP
        // TMP disabled -- was messing up recent posts snippet
        /*if ( $post_type == 'post' && ( empty($taxonomy) || $taxonomy == 'category' ) && empty($tax_terms) ) {
            $category = birdhive_get_default_category();
            if ( !empty($category) ) {
                $tax_terms = $category;
                //$cat_id = get_cat_ID( $category );
                //$tax_terms = $cat_id;
                if ( empty($taxonomy) ) {
                    $taxonomy = 'category';
                }
            } else {
                $tax_terms = null;
            }
        }*/
        
        // If terms, check to see if array or string; build tax_query accordingly
        //if ( !empty($terms) ) { } // TBD
        
        // Orderby
        if ( isset($orderby) ) {
        
        	$ts_info .= "orderby: ".print_r($orderby, true)."<br />";

			if ( !is_array($orderby) && strpos($orderby, ',') !== false) {
				$orderby = str_replace(","," ",$orderby);
				//$orderby = birdhive_att_explode( $orderby );
			}
			
            $standard_orderby_values = array( 'none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'comment_count', 'relevance', 'menu_order', 'meta_value', 'meta_value_num', 'post__in', 'post_name__in', 'post_parent__in' );
                        
            // determine if orderby is actually meta_value or meta_value_num with orderby $args value to be used as meta_key
            if ( !is_array($orderby) ) {
                
                // Is the orderby value one of the standard values for ordering?
                if ( !in_array( $orderby, $standard_orderby_values) ) {
                	
                	// TODO: determine whether to sort meta values as numbers or as text
					if (strpos($orderby, 'num') !== false) {
						$wp_args['orderby'] = 'meta_value_num'; // or meta_value?
					} else {
						$wp_args['orderby'] = 'meta_value';
					}
					$wp_args['meta_key'] = $orderby; // TODO: figure out how to opt to also include items WITHOUT this meta key...
                
                } else {
                	$wp_args['orderby'] = $orderby;
                }
                
                /* //TODO: consider naming meta_query sub-clauses, as per the following example:
                $q = new WP_Query( array(
                    'meta_query' => array(
                        'relation' => 'AND',
                        'state_clause' => array(
                            'key' => 'state',
                            'value' => 'Wisconsin',
                        ),
                        'city_clause' => array(
                            'key' => 'city',
                            'compare' => 'EXISTS',
                        ), 
                    ),
                    'orderby' => array( 
                        'city_clause' => 'ASC',
                        'state_clause' => 'DESC',
                    ),
                ) );
                */
            } else {
            
            	$orderby = array();
            		
				foreach ( $orderby as $k => $v ) {
					$v = trim($v);
					if ( in_array( $k, $standard_orderby_values ) && ($v == "ASC" || $v == "DESC") ) {
						$orderby[$k] = $v;
					} else {
						$wp_args['meta_key'] = $orderer;
						$wp_args['orderby'] = 'meta_key';
					}
				}
				// TODO: deal w/ possibility of meta_key/value pair AND a standard orderby val...
				if ( empty($wp_args['orderby']) && !empty( $orderby )) {
					$wp_args['orderby'] = $orderby;
				}
				
			}

        }
        
		// Tax Query
		if ( !empty($tax_query) ) {
			
			$wp_args['tax_query'] = $tax_query;
			
		} else if ( is_category() && $category != "all" ) {

            // Post category archive
            $ts_info .= "is_category (archive)<br />";

            // Get archive term_id from slug
            $archive_term = term_exists( "archives", "category" );
            if ( !$archive_term ) { $archive_term = term_exists( "website-archives", "category" ); }
			if ( $archive_term ) {
				$archive_cat_id = $archive_term['term_id'];
			} else {
				$archive_cat_id = 99999; // tft
			}
            // TODO: designate instead via CMS options?
            //$archive_term = get_term_by('slug', 'website-archives', 'category');
            //if ( $archive_term ) { $archive_cat_id = $archive_term->term_id; }

            $tax_field = 'term_id';
            
            $wp_args['tax_query'] = array();
            if ( $tax_terms ) {
            	$wp_args['tax_query']['relation'] = 'AND';
            	$wp_args['tax_query'][] = array(
                    'taxonomy' => 'category',
                    'field'    => $tax_field,
                    'terms'    => array( $tax_terms ),
                );
            }
            $wp_args['tax_query'][] = array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array( $archive_cat_id),
				'operator' => 'NOT IN',
			);

        } else if ( $taxonomy && $tax_terms ) {

            $ts_info .= "Building tax_query based on taxonomy & tax_terms.<br />";

            $wp_args['tax_query'] = array(
                array(
                    'taxonomy'  => $taxonomy,
                    'field'     => $tax_field,
                    'terms'     => $tax_terms,
                    'include_children' => $include_children,
                    'operator'  => $tax_operator,
                )
            );

        }
        
        // Meta Query
		if ( empty($meta_query) ) {
			
			// If meta_query was NOT set already via query args, then build it based on other args, as needed
			$meta_query_components = array();
        
			// Featured Image restrictions?
			// TODO: update this to account for custom_thumb and first_image options? Or is it no longer necessary at all?
			if ( isset($args['has_image']) && $args['has_image'] == true ) {
				$meta_query_components[] = 
					array(
						'key' => '_thumbnail_id',
						'compare' => 'EXISTS'
					);
			}
			
			// Scope restrictions? WIP
			if ( $scope && $date_field ) {
				$ts_info .= "query by scope/date_field<br />";
				// Check to make sure the date_field is a registered meta field
				//if ( registered_meta_key_exists( 'post', $date_field ) ) { //if ( registered_meta_key_exists( 'post', $date_field, $post_type ) ) { // disabled -- not working as expected
					//
					$scope_dates = sdg_scope_dates($scope);
					$start_date = $scope_dates['start'];
					$end_date = $scope_dates['end'];
					$ts_info .= "start_date: $start_date; end_date: $end_date<br />";
					
					$meta_query_components[] = 
						array(
							'key' => $date_field,
							'value' => array( $start_date, $end_date ),
							'type'    => 'numeric',
							'compare'   => 'BETWEEN',
						);
					
				//} else {
					//$ts_info .= "No registered meta_key with key: '$date_field'<br />"; // for post_type '$post_type'
				//}
			}
			
			// Designated meta_key/meta_value?
			if ( ( $meta_key && $meta_value ) ) {
				// meta_key and meta_value both specified
				$meta_query_components[] = 
					array(
						'key' => $meta_key,
						'value'   => $meta_value,
						'compare' => '=',
					);
			} else if ( ( $meta_key && $meta_key != $date_field) ) {
                // meta_key specified, but no value
				$meta_query_components[] = 
					array(
						'key' => $meta_key,
						//'value' => '' ,
                        'compare' => 'EXISTS',
					);
			}

			// Series?
			if ( post_type_exists('sermon') && $post_type == 'sermon' && $series ) {
				// Sermon series
				$meta_query_components[] = 
					array(
						'key' => 'sermons_series',
                        'value' => '"' . $series . '"', // Series ID -- matches exactly "123", not just 123. This prevents a match for "1234"
                        'compare' => 'LIKE'	
					);
			} else if ( post_type_exists('event') && $post_type == 'event' && $series ) {
				// Event series
				$meta_query_components[] = 
					array(
						'key' => 'event_series', //'key' => 'series_events', //'key' => 'events_series',
                        'value' => '"' . $series . '"', // Series ID -- matches exactly "123", not just 123. This prevents a match for "1234"
                        'compare' => 'LIKE'	
					);
			}

			// Finalize meta_query based on number of components
			if ( count($meta_query_components) > 1 ) {
				$meta_query['relation'] = 'AND';
				foreach ( $meta_query_components AS $component ) {
					$meta_query[] = $component;
				}
			} else {
				$meta_query = $meta_query_components; //$meta_query = $meta_query_components[0];
			}
			
		}

        if ( !empty($meta_query) ) {
            $wp_args['meta_query'] = $meta_query;
        }
        
        if ( $cat_id && ! is_category() ) { // is_archive()

            // Get the URL of this category
            $category_url = get_category_link( $cat_id );
            $category_link = 'Category Link';
            if ($category_url) { 
                $category_link = '<a href="'.$category_url.'"';
                if ($category === "Latest News") {
                    $category_link .= 'title="Latest News">All Latest News';
                } else {
                    $category_link .= 'title="'.$category.'">All '.$category.' Articles';
                }
                $category_link .= '</a>';
            }

        }
        
    } // END if ( !$get_by_ids )
    
    // Groupby
    //if ( $groupby ) { $wp_args['groupby'] = $groupby; }
    
    // -------
    // Run the query
    // -------
	$arr_posts = new WP_Query( $wp_args );
    
    $ts_info .= "[bgp] WP_Query run as follows:";
    $ts_info .= "<pre>args: ".print_r($wp_args, true)."</pre>";
    $ts_info .= "[".count($arr_posts->posts)."] posts found.<br />";
    //$ts_info .= "<pre>meta_query: ".print_r($meta_query, true)."</pre>";
	//$ts_info .= "birdhive_get_posts arr_posts: <pre>".print_r($arr_posts, true)."</pre>";

    //$ts_info .= "birdhive_get_posts arr_posts->request<pre>".$arr_posts->request."</pre>";
    $ts_info .= "birdhive_get_posts last_query:<pre>".$wpdb->last_query."</pre>";
    
    //if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $ts_info = '<div class="troubleshooting">'.$ts_info.'</div>'; }
    
    $arr_info['arr_posts'] = $arr_posts;
    $arr_info['args'] = $wp_args;
    $arr_info['category_link'] = $category_link;
    $arr_info['ts_info'] = $ts_info;
    
    return $arr_info;
}

// Function for display of posts in various formats -- links, grid, &c.
// This shortcode is in use on numerous Pages, as well as via the archive.php page template
// Examples:
/* ********
Table display:
-------------
[display_posts post_type="person" taxonomy="person_category" tax_terms="dca" orderby="meta_value" order="ASC" meta_key="award_year_dca" limit="-1" display_format="table" fields="award_year_dca,title,recital_venue_dca" headers="-,Recipient,Presentation Venue"]


**********
*/
add_shortcode('display_posts', 'birdhive_display_posts');
function birdhive_display_posts ( $atts = array() ) { //function birdhive_display_posts ( $args = array() ) {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
	
	global $wpdb;
	$info = "";
	$ts_info = "";
	$posts = array(); 
	$items = array(); // post items -- may be simple array of post objects, or mixed array of headers and post ids

	$args = shortcode_atts( array(
        
        'post_type'	=> 'post',
        'limit' 	=> 15,
        'orderby' 	=> 'title',
        'order' 	=> 'ASC',
        'meta_key' 	=> null,
        'meta_value'=> null,
        //'groupby'	=> null,
        //
        'ids' 		=> null,
        'slugs' 	=> null,
        'post_id' 	=> null, // ?
        'name' 		=> null,
        //
        'category' => null, // for posts/pages only
        'taxonomy'  => null,
        'tax_terms'  => null,
        //
        // This group_by is NOT the same as the wpq arg 'groupby' -- we're going to use it to retrieve posts group by group for display with headers... WIP
        'group_by'	=> null, // e.g. category, event-categories, link_category -- for queries using scope, TODO: also build in options to group_by month, etc.
        //
        'display_format' => 'list', // other options: links; excerpts; archive (full post content); grid; table
        'return_format' => null, // deprecated -- TODO: remove this extra attribute as soon as updates are complete on all sites (STC, AGO)
        
        // For grid display_format:
        'cols' => 4, // ***
        'spacing' => 'spaced', // ***
        'header' => false,
        'overlay' => false, // ***
        //
        'has_image' => false, // set to true to ONLY return posts with features images
        'class' => null, // for additional styling
        'link_posts' => true,
        'show_images' => false,
        'show_subtitles' => true,
        'show_content' => null, //'excerpts', 'full'
        'expandable' => false, // for excerpts
        'text_length' => 'excerpt', // excerpt or full length
        'preview_length' => '55',
        //'aspect_ratio' => 'square', // TBD whether to activate this or not... probably better to simplify args array
        
        // For post_type 'event' -- and others, wip (e.g. transactions -- any post type with event fields)
        'scope' => 'all', //'upcoming',
        'date_field' => null, // WIP -- maybe default to 'event_start'? -- applies to events -- other possibilities include sermon_date, transaction_date...
        
        // For Events or Sermons
        'series' => false,
        
        // For table display_format
        'fields'  => null, // ***
        'headers'  => null, // ***
        
    ), $atts );
    
    // Extract
	extract( $args );
	
	// Get query vars >> override args
	// WIP -- all override for ALL args? or just specific ones?0
	if ( get_query_var('scope') ) {
		$scope = get_query_var('scope');
		$ts_info .= "scope via query_var: ".$scope."<br />";
	} else {
		$ts_info .= "scope query_var not set<br />";
	}
	
	//$ts_info .= 'extracted args: <pre>'.print_r($args, true).'</pre>';
	$ts_info .= "post_type: ".$post_type."<br />";
    
    if ( $return_format ) { $display_format = $return_format; $args['display_format'] = $display_format; } // deal w/ deprecated attribute
    if ( $show_subtitles == "false" ) {
    	$show_subtitles = false;
    	$ts_info .= "show_subtitles: false<br />";
    } else {
    	$show_subtitles = true;
    	$ts_info .= "show_subtitles: true<br />";
    }
    
    //
    // TODO: 'category' applies to pages and posts only, but it's an easy mistake to use that attribute for events too => correct for that possibility
    // NB we'll only do this if NOT searching for events in a series, because in that case we're running a NON-EM get
    
    // Events are a special case...
    if ( post_type_exists('event') && $post_type == "event" ) {
    	
    	if ( empty($series) ) {
    	
    		// Use EM::get if no series ID has been designated
			// TODO: check to see if EM plugin is installed and active?
    	
    		// If ordering is setup by meta_key, translate that for EM
    		if ( $orderby == "date" || ( empty($orderby) && str_contains($meta_key, "event_start" ) ) || str_contains($orderby, "event_start" )) { 
    			$orderby = "event_start";
    		}
    		
			// TODO: deal w/ taxonomy parameters -- how to translate these properly for EM?
			// Deal w/ other args...: meta_key, meta_value, name, taxonomy, tax_terms, display_format, cols...
		
			// Create array of args relevant to EM search attributes
			$em_args = array();
			$em_args['limit'] = $limit;
			$em_args['order'] = $order;
			$em_args['orderby'] = $orderby;
			$em_args['category'] = $category;
			$em_args['scope'] = $scope;
		
			// Posts by ID -- translate to fit EM search attributes (https://wp-events-plugin.com/documentation/event-search-attributes/)
			if ( !empty($ids) ) {
				$ts_info .= "Getting posts by IDs: ".$ids."<br />";				
				$post_ids = array_map( 'intval', birdhive_att_explode( $ids ) );
				if ( count($post_ids) > 1 ) {
					$em_args['post_id'] = $post_ids; //$em_args['post__in'] = $post_ids;
				} else {
					$em_args['post_id'] = $ids;
				}
				//$em_args['post_id'] = $ids;
			}
			if ( $em_args ) { $ts_info .= 'shortcode_atts as passed to EM_Events::get <pre>'.print_r($em_args, true).'</pre>'; } // tft
		
			$items = EM_Events::get( $em_args ); // Retrieves an array of EM_Event Objects
			//$ts_info .= "EM items retrieved: <pre>".print_r($items, true)."</pre>";
			
			$ts_info .= 'Posts retrieved using EM_Events::get: <pre>';		
			foreach ( $items as $obj ) {
				//$ts_info .= "obj: ".print_r($obj, true)."<br />";
				$ts_info .= "post_id: ".$obj->post_id."<br />";
				$ts_info .= "event_id: ".$obj->event_id."<br />";
				$ts_info .= "event_name: ".$obj->event_name."<br />";
				$ts_info .= "-------<br />";
				//$ts_info .= "event_attributes: ".print_r($post->event_attributes, true)."<br />";
				//if ( isset($post->event_attributes['event_series']) ) { $ts_info .= "event_series: ".$post->event_attributes['event_series']."<br />"; }
			}
			//$ts_info .= 'last_query: '.print_r( $wpdb->last_query, true); // '<pre></pre>'
			$ts_info .= '</pre>'; // tft
        
    	} else {
    	
    		$ts_info .= "searching for events with series_id: ".$series."<br />";
    		
    		// If no meta_key is yet set and the orderby str is event_start, or _event_start_date, or variations on that theme, set the ordering and meta_key accordingly
			if ( empty($meta_key) && str_contains($orderby, "event_start" ) ) { 
				$args['orderby'] = "meta_value";
				$args['meta_key'] = "_event_start"; // use event_start because that covers date AND time	
			} else {
				if ( str_contains($meta_key, "event_start" ) ) { $args['meta_key'] = "_event_start"; }
				//if ( $meta_key == "event_start_date,event_start_time" ) { $args['meta_key'] = "_event_start_date"; }
				if ( empty($orderby) ) { $args['orderby'] = "meta_value"; } else { $ts_info .= "args['meta_key']: ".$args['meta_key']."<br />"; $ts_info .= "orderby: ".$orderby."<br />"; }
			}
    		
    		if ( isset($args['category']) &&  !isset($args['taxonomy']) ) { $args['taxonomy'] = "event-categories"; $args['tax_terms'] = $args['category']; unset($args["category"]); }
    	}
    	
    }
        
    // Clean up the array
    if ( $post_type !== "event" && !$date_field ) { unset($args["scope"]); }
    if ( $post_type !== "event" && $post_type !== "sermon" ) { unset($args["series"]); }
    if ( $display_format != "grid" ) { unset($args["cols"]); unset($args["spacing"]); unset($args["overlay"]); }
    
    // Make sure the display_format is valid
    // TODO: revive/fix "archive" option -- deal w/ get_template_part issue...
    /*if ( $display_format != "links" && $display_format != "list" && $display_format != "table" && $display_format != "grid" && $display_format != "excerpts" && $display_format != "archive" ) {
        $display_format = "list"; // default
    }*/
    if ( ( $display_format == "excerpts" || $display_format == "archive" ) && $show_content == null ) { $show_content = 'excerpts'; }
    //
    $ts_info .= "display_format: ".$display_format."<br />";
    $ts_info .= "show_content: ".$show_content."<br />";
    
	// Init index
	$index = "";
	
    // Retrieve an array of posts matching the args supplied -- if we didn't already get the posts using EM
    if ( empty($items) ) {
    	
    	// NOT events -- or: events in a series
    	
    	// TODO: deal w/ events scope even if searching for series?
    	
    	// If we've got a group_by value, then handle it    	
		// build set of sorted relevant taxonomies and then get posts per term_id
	
		// WIP group_by
		if ( $group_by ) {
			
			$args['do_ts'] = true;
			$group_by_secondary = null;
			
			// Is it a single or multiple group_by value?
			if ( str_contains($group_by, "," ) ) {
				$ts_info .= "multiple group_by parameters!<br />";
				$arr_groups = birdhive_att_explode($group_by);
				$group_by = $arr_groups[0];
				$ts_info .= "group_by: $group_by<br />";
				//if ( $arr_groups[1] ) { $group_by_secondary = $arr_groups[1]; }
				$group_by_secondary = $arr_groups[1];
				$ts_info .= "group_by_secondary: $group_by_secondary<br />";
				
				// WIP!
				if ( $group_by_secondary ) {
				
					//$field = get_field_object($group_by_secondary); // this won't work here because there's no object instance to check
					
					if ( taxonomy_exists($group_by_secondary) ) {
						$ts_info .= "'$group_by_secondary' is a taxonomy<br />";
					//} else if ( get_field_object($group_by_secondary) ) {
						//$ts_info .= "'$group_by_secondary' is an ACF field";
					} else if ( registered_meta_key_exists( 'post', $group_by_secondary, 'link' ) || registered_meta_key_exists( 'post', $group_by_secondary ) ) {
						$ts_info .= "'$group_by_secondary' is a registered_meta_key<br />";
						// is $group_by_secondary a meta_key? -- why doesn't this work? are ACF fields not officially registered as meta keys? why not?
						// if so...
						// orderby meta_value
					} else {
						$ts_info .= "'$group_by_secondary' is neither a taxonomy, nor a registered_meta_key<br />"; // , nor an ACF field
					}
				}
				
			}
			
			// Get posts per group
			//$info .= "group_by: $group_by<br />"; // tft
			
			// First check to see if the group_by refers to a taxonomy
			if ( taxonomy_exists($group_by) ) {
				
				$current_term_id = ""; // init
				$items = array();
				$tax_terms = array();
				
				// Get all non-empty terms for the given taxonomy, ordered by sort_num
				$terms = get_terms( array( 'taxonomy' => $group_by, 'hide_empty' => true, 'orderby' => 'meta_value_num', 'meta_key' => 'sort_num', 'parent' => 0 ) );
				foreach ( $terms as $term ) {
				
					$hlevel = 2;
					
					$term_id = $term->term_id;
					$tax_terms[] = $term_id;
					
					//$info .= $term->name."<br />";
					// TFT: get sort_num -- because the orderby isn't working right
					//get_postmeta.... WIP
					$sort_num = get_field('sort_num', $term_id, false);
					//$ts_info .= "term_id: ".$term_id."/sort_num: ".$sort_num."<br />";
					$item_id = $term->slug;
					
					// Add item to index
					$index .= '<a href="#'.$item_id.'" class="index_anchor primary">'.$term->name.'</a><br />';
					
					// WIP Add "tax_term" -- or more generically: "header"? -- item to array with term name as title
					// WIP -- add anchor for header items
					
					$term_item = array( 'item_type' => "tax_term", 'term_id' => $term_id, 'item_id' => $item_id, 'hlevel' => $hlevel );
					array_push( $items, $term_item );	
					
					// Get posts per term_id
					$wp_args = $args;
					$wp_args['taxonomy'] = $group_by;
					$wp_args['tax_terms'] = $term_id;
					$wp_args['tax_field'] = 'term_id';
					$wp_args['include_children'] = false;
					$wp_args['return_fields'] = 'ids';
					if ( $group_by_secondary ) {
						// WIP: fix this so it sorts by child terms first, then by secondary field
						$wp_args['orderby'] = $group_by_secondary;
					}
					$posts_info = birdhive_get_posts( $wp_args );
					$posts = $posts_info['arr_posts']->posts;
					//$ts_info .= 'shortcode_atts as passed to birdhive_get_posts: <pre>'.print_r($args, true).'</pre>';
					$ts_info .= $posts_info['ts_info'];

					// Add the posts to the items array
					$current_sub = ""; // init for group_by_secondary
					$hlevel = 3;
					foreach ( $posts as $post_id ) {
						if ( $group_by_secondary ) {
							$subheader = null;
							$item_id = null;
							$arr_subheader = get_field($group_by_secondary, $post_id); // acf
							if ( is_array($arr_subheader) ) {
								if ( isset($arr_subheader['label']) ) { $subheader = $arr_subheader['label']; }
								if ( isset($arr_subheader['value']) ) { $item_id = $arr_subheader['value']; }
							}
							if ( $subheader && $subheader != $current_sub && $subheader != '---' ) {
								// Add anchor for header items
								// WIP: figure out how NOT to add the header item if all the posts have the same subheader...
								$gbs_item = array( 'item_type' => "subheader", 'item_title' => $subheader, 'item_id' => $item_id, 'hlevel' => $hlevel );
								array_push( $items, $gbs_item );
								$current_sub = $subheader;
								// Add item to index
								$index .= '&bull; <a href="#'.$item_id.'" class="index_anchor secondary">'.$subheader.'</a><br />';
							}
						}
						$post_item = array( 'item_type' => "post", 'post_id' => $post_id );
						array_push( $items, $post_item );
					}
					
					// WIP: get term children, if any, and the childrens' links...
					$child_terms = get_terms( array( 'taxonomy' => $group_by, 'hide_empty' => true, 'child_of' => $term_id ) );
					foreach ( $child_terms as $child_term ) {		
					
						$hlevel = 3;
								
						$child_term_id = $child_term->term_id;
						$tax_terms[] = $child_term_id;
						
						$item_id = $child_term->slug;
						$index .= '<a href="#'.$item_id.'" class="index_anchor secondary">'.$child_term->name.'</a><br />';
						//$index .= "=> ".$child_term->name."<br />";
						// How to generalize this? Perhaps a separate function to build the hierarchical array of taxonomy terms to be retrieved?
						// ...
						$child_term_item = array( 'item_type' => "tax_term", 'term_id' => $child_term_id, 'item_id' => $item_id, 'hlevel' => $hlevel );
						array_push( $items, $child_term_item ); // WIP -- this ends up in the wrong place -- needs to be a subheader, like w/ group_by_secondary...
						// Modify parent wp_args to use child_term_id
						$wp_args['tax_terms'] = $child_term_id;
						// Get posts per child_term_id
						$posts_info = birdhive_get_posts( $wp_args );
						$child_posts = $posts_info['arr_posts']->posts;
						
						//$ts_info .= 'shortcode_atts as passed to birdhive_get_posts: <pre>'.print_r($args, true).'</pre>';
						$ts_info .= $posts_info['ts_info']; 
						
						// Add the posts to the items array
						$current_sub = ""; // init for group_by_secondary
						$hlevel = 4;
						foreach ( $child_posts as $post_id ) {
							if ( $group_by_secondary ) {
								$subheader = null;
								$item_id = null;
								$arr_subheader = get_field($group_by_secondary, $post_id); // acf
								if ( is_array($arr_subheader) ) {
									if ( isset($arr_subheader['label']) ) { $subheader = $arr_subheader['label']; }
									if ( isset($arr_subheader['value']) ) { $item_id = $arr_subheader['value']; }
								}
								if ( $subheader && $subheader != $current_sub && $subheader != '---' ) {
									// Add anchor for header items
									// WIP: figure out how NOT to add the header item if all the posts have the same subheader...
									$gbs_item = array( 'item_type' => "subheader", 'item_title' => $subheader, 'item_id' => $item_id, 'hlevel' => $hlevel );
									array_push( $items, $gbs_item );
									$current_sub = $subheader;
									// Add item to index
									$index .= '&bull; <a href="#'.$item_id.'" class="index_anchor secondary">'.$subheader.'</a><br />';
								}
							}
							$post_item = array( 'item_type' => "post", 'post_id' => $post_id );
							array_push( $items, $post_item );
						}
					}
					
					/*
					// Get posts per term_id
					$wp_args = $args;
					$wp_args['taxonomy'] = $group_by;
					$wp_args['tax_terms'] = $tax_terms; //wip... how to properly loop in child_terms and THEN group_by_secondary...
					//$wp_args['tax_terms'] = $term_id;
					$wp_args['tax_field'] = 'term_id';
					$wp_args['return_fields'] = 'ids';
					if ( $child_terms ) {
						//$wp_args['orderby'] = parent, term_id, group_by_secondary
					}
					if ( $group_by_secondary ) {
						$wp_args['orderby'] = $group_by_secondary;
					}
					
					$posts_info = birdhive_get_posts( $wp_args );
					$posts = $posts_info['arr_posts']->posts; // Retrieves an array of WP_Post Objects
					//$ts_info .= 'shortcode_atts as passed to birdhive_get_posts: <pre>'.print_r($args, true).'</pre>';
					$ts_info .= $posts_info['ts_info'];
					*/		
					//array_push( $items, $posts );
					//$info .= $posts_info['info']; // obsolete(?)
				}
				
			} else {
				// If it's not a taxonomy, then what?
			}
			
		} else {
			
			// No groupings, just get one set of posts based on args
			$posts_info = birdhive_get_posts( $args );
			$items = $posts_info['arr_posts']->posts; // Retrieves an array of WP_Post Objects
			//$info .= $posts_info['info']; // obsolete(?)
			$ts_info .= 'args as passed to birdhive_get_posts: <pre>'.print_r($args, true).'</pre>';
			$ts_info .= $posts_info['ts_info'];
			
		} // END if ( $group_by )
        
    } // END if ( empty($posts) )
    
    if ( $items ) {
        
        //$ts_info .= 'Items to be passed to birdhive_display_collection: <pre>'.print_r($items, true).'</pre>'; // tft
        
        $class = " ".$display_format; // wip
        
		//if ($args['header'] == 'true') { $info .= '<h3>Latest '.$category.' Articles:</h3>'; } // WIP
		$info .= '<div class="dsplycntnt-posts'.$class.'">';
		if ( $index ) {
			$info .= '<h2>Index</h2>';
			$info .= '<div class="dsplycntnt index">';
			$info .= $index;
			$info .= '</div>'; 
		}
		// TODO: modify the following to pass only subset of args? Much of the info is not needed for the display_collection fcn
		// TODO: check for existence of EM plugin in case some other event CPT is in use
		if ( $post_type == "event" ) { $content_type = 'events'; } else { $content_type = 'posts'; }
		// TODO: revise args to fit new setup for item arrays etc.
		$display_args = array( 'content_type' => $content_type, 'display_format' => $display_format, 'link_posts' => $link_posts, 'show_subtitles' => $show_subtitles, 'show_content' => $show_content, 'items' => $items, 'display_atts' => $args );
        $info .= birdhive_display_collection( $display_args );
		
        $info .= '</div>'; // end div class="dsplycntnt-posts" (wrapper)
        
        wp_reset_postdata();
    
    }  else {
        
        $ts_info .= "No post items found!";
        
    } // END if posts
    
    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
    
    return $info;
    
}

add_shortcode('content_collection', 'birdhive_content_collection');
function birdhive_content_collection ( $atts = array() ) {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
	global $wpdb;
	$info = "";
	$ts_info = "";
	
	$args = shortcode_atts( array(
        'id' => null,
        'do_ts' => $do_ts,
    ), $atts );
    
    // Extract
	extract( $args );
    
    $info .= birdhive_display_collection( array('collection_id' => $id, 'display_atts' => array('do_ts' => $do_ts) ) );
    
    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
    
    return $info;
    
}

// ACF field groups...
function match_group_field ( $field_groups, $field_name ) {
    
    $field = null;
    
    // Loop through the field_groups and their fields to look for a match (by field name)
    foreach ( $field_groups as $group ) {

        $group_key = $group['key'];
        //$info .= "group: <pre>".print_r($group,true)."</pre>"; // tft
        $group_title = $group['title'];
        $group_fields = acf_get_fields($group_key); // Get all fields associated with the group
        //$field_info .= "<hr /><strong>".$group_title."/".$group_key."] ".count($group_fields)." group_fields</strong><br />"; // tft

        $i = 0;
        foreach ( $group_fields as $group_field ) {

            $i++;

            if ( $group_field['name'] == $field_name ) {

                // field exists, i.e. the post_type is associated with a field matching the $field_name
                $field = $group_field;
                // field_object parameters include: key, label, name, type, id -- also potentially: 'post_type' for relationship fields, 'sub_fields' for repeater fields, 'choices' for select fields, and so on

                //$field_info .= "Matching field found for field_name $field_name!<br />"; // tft
                //$field_info .= "<pre>".print_r($group_field,true)."</pre>"; // tft

                /*
                $field_info .= "[$i] group_field: <pre>".print_r($group_field,true)."</pre>"; // tft
                $field_info .= "[$i] group_field: ".$group_field['key']."<br />";
                $field_info .= "label: ".$group_field['label']."<br />";
                $field_info .= "name: ".$group_field['name']."<br />";
                $field_info .= "type: ".$group_field['type']."<br />";
                if ( $group_field['type'] == "relationship" ) { $field_info .= "post_type: ".print_r($group_field['post_type'],true)."<br />"; }
                if ( $group_field['type'] == "select" ) { $field_info .= "choices: ".print_r($group_field['choices'],true)."<br />"; }
                $field_info .= "<br />";
                //$field_info .= "[$i] group_field: ".$group_field['key']."/".$group_field['label']."/".$group_field['name']."/".$group_field['type']."/".$group_field['post_type']."<br />";
                */

                break;
            }

        }

        if ( $field ) { 
            //$field_info .= "break.<br />";
            break;  // Once the field has been matched to a post_type field, there's no need to continue looping
        }

    } // END foreach ( $field_groups as $group )
    
    return $field;
}

///// ****** START ACF Repeater Rows Display Function(s) -- WIP 2409 ****** /////

// Music lists repeater field: list_items
// Event Programs repeater field: 
// TODO/WIP: generalize for use with other CPTs/other types of lists?
// This is a modified version of WHX4: get_event_program_items
add_shortcode('display_list_items', 'get_list_items');
function get_list_items( $atts = array() ) {
    
    // TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
	$args = shortcode_atts( array(
		'post_id'        => get_the_ID(),
        'run_updates' => false,
        'display' => 'table'
    ), $atts );
    
    // Extract
	extract( $args );
    
    // Init vars
    $arr_info = array(); // wip 06/27/23
    $info = "";
    $ts_info = "";
    $ts_info .= "===== get_list_items =====<br />";
    
    if ( $display == 'table' ) { $table = ""; }
    
    // TODO: deal more thoroughly w/ non-table display option, or eliminate that parameter altogether.
	
	if ($post_id == null) { $post_id = get_the_ID(); }
	$ts_info .= "Event Program Items for post_id: $post_id<br />";
    //$ts_info .= "display: $display<br />";
    
    // What type of program is this? Service order or concert program?
    $program_type = get_post_meta( $post_id, 'program_type', true );
    $ts_info .= "program_type: $program_type<br />";
    
    // Program Layout -- left or centered?
    $program_layout = get_post_meta( $post_id, 'program_layout', true );
	
    /*** WIP ***/
    //if ( devmode_active() || is_dev_site() ) { $run_updates = true; } // TMP(?) disabled 03/25/22
    //if ( devmode_active() || ( is_dev_site() && devmode_active() )  ) { $run_updates = true; } // ???
    
	// Get the program item repeater field values (ACF)
    $program_rows = get_field('program_items', $post_id); // ACF function: https://www.advancedcustomfields.com/resources/get_field/ -- TODO: change to use have_rows() instead?
    /*
    if ( have_rows('program_items', $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/have_rows/
        while ( have_rows('program_items', $post_id) ) : the_row();
            $XXX = get_sub_field('XXX'); // ACF function: https://www.advancedcustomfields.com/resources/get_sub_field/
        endwhile;
    } // end if
    */    
    if ( empty($program_rows) ) { $program_rows = array(); }    
    if ( is_array($program_rows) ) { $ts_info .= count($program_rows)." program_items/program_rows<br />"; } else { $ts_info .= "program_rows: ".print_r($program_rows, true); }
    
    //TODO: Determine whether program as a whole contains grouped rows
    /*
    if ( program_contains_groupings($rows) ) {
    	//
    }
    */
    
    // WIP: streamlining
    
	$authorship_display_settings = null;
	$program_composer_ids = array(); // TMP -- deprecated
    $deletion_count = 0;
    $table_rows = array();
    
    if ( is_array($program_rows) ) {
	
		// Check to see if ANY of the rows contains items with post_type == 'repertoire'
		if ( program_contains_repertoire($program_rows) ) { // TODO: generalize the following to apply to any items with authorship, not just rep/composers
	
			$ts_info .= "program contains repertoire items<br />";
			// If so, then get all the program composers
			$program_item_ids = get_program_item_ids($program_rows);
			$ts_info .= "program_item_ids: ".print_r($program_item_ids, true)."<br />";
			//
			$authorship_display_settings = set_row_authorship_display($program_item_ids);
			$ts_info .= "authorship_display_settings: <pre>".print_r($authorship_display_settings, true)."</pre><br />";
		
		}
	
		// Translate program_rows into table_rows (separate row per item)
		//////
    
		foreach( $program_rows as $r => $row ) {
		
			// TODO: check if row is empty >> next
		
			// Initialize variables
			$row_info = ""; // Info for troubleshooting
			$row_info .= "-------------------- [row $r] --------------------<br />";
		
				$grouped_row = false; // For multiple-item rows
				//
			$placeholder_label = false;
			$placeholder_item = false;
				//
			$arr_item_label = array();
			$arr_item_name = array();
				//
			$program_item_label = null;
			$program_item_name = null;
				//
				$show_person_dates = true;
		
			//
			$label_update_required = false;
			$delete_row = false;
		
			//$row_info .= "get_event_program_items ==> program row [$r]: ".print_r($row, true)."<br />";
			
			// Is a row_type set? WIP -- working on phasing out deprecated fields like 'show_item_label' in favor of simple row_types setup
			if ( isset($row['row_type']) ) { $row_type = $row['row_type']; } else { $row_type = null; }
			$row_info .= "get_event_program_items ==> row_type: ".$row_type."<br />";
			
			// ROW TYPES WIP: 
			/*
				Program item rows types:
				default : Standard two-column row
				header : Header row
				program_note : Program note
				label_only : Item label only
				title_only : Item title only
				//
				Personnel row types:
				default : Standard two-column row
				header : Header row
				role_only : Person role only
				name_only : Person name only
				//
				Additional option to consider, TBD:
				split : Title left/Authorship right
				//
				if ( $row_type == 'title_only' ) {
					
					$arr_item_name = get_rep_info( $program_item_obj_id, 'display', $show_item_authorship, true );
					$item_name = $arr_item_name['info'];
					$ts_info .= $arr_item_name['ts_info'];
			
				} else if ( empty($program_item_label) ) {

					$ts_info .= "program_item_label is empty >> use title in left col<br />";
				
				}
			*/
			
			// Is this a header row? (Check for deprecated field values)
			if ( $row_type != "header" && isset($row['is_header']) && $row['is_header'] == 1 ) { $row_type = "header"; } // TODO: update the row_type in the DB
		
			// Should this row be displayed on the front end?
			// TODO: modify to simplify as below -- set to true/false based on stored value, if any
			if ( isset($row['show_row']) && $row['show_row'] != "" ) { 
				$show_row = $row['show_row'];
				$row_info .= "get_event_program_items ==> show_row = '".$row['show_row']."'<br />";
			} else { 
				$show_row = 1; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
				$row_info .= "get_event_program_items ==> show_row = 1 (default)<br />";
			}
		
			// Should we display the item label for this row?
			// TODO: streamline/eliminate this deprecated field and simply update row_type
			if ( isset($row['show_item_label']) && $row['show_item_label'] == "0" ) { 
				$show_item_label = false;
				$row_info .= "get_event_program_items ==> show_item_label FALSE<br />";
			} else { 
				$show_item_label = true; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
				$row_info .= "get_event_program_items ==> show_item_label TRUE (default)<br />";
			}
				
			// Should the item title for this row be displayed on the front end?
			// TODO: streamline/eliminate this deprecated field and simply update row_type
			if ( isset($row['show_item_title']) && $row['show_item_title'] == "0" ) { 
				$show_item_title = false;
				$row_info .= "show_item_title = 0, i.e. false<br />";
			} else { 
				$show_item_title = true; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
				$row_info .= "default: show_item_title = true<br />";
			}
		
			// Get the item label
			// --------------------
			// TODO/WIP: maybe figure out how to skip this for rep items in $program_type == "concert_program" where title is used in left col instead of label			
			if ( $show_item_label == true && $row_type != 'title_only' ) {			
				$row_info .= "get_program_item_label<br />";
				$arr_item_label = get_program_item_label($row);
				$program_item_label = $arr_item_label['item_label'];
				$placeholder_label = $arr_item_label['placeholder'];
				$row_info .= $arr_item_label['ts_info'];
				$row_info .= ">> program_item_label: $program_item_label<br />";
			}
		
			// Program item(s)
			$program_items = array();
			$num_items = 0;
			if ( $show_item_title == true && $row_type != 'header' ) {
				// Does the row contain one or more item objects?			
				//$num_items = 1; // default: Single-item program_row translates to single table_row
				if ( isset($row['program_item']) && is_array($row['program_item']) ) {
					//$ts_info .= "program_item: ".print_r($row['program_item'], true)."<br />";
					$num_items = count($row['program_item']);
					$program_items = $row['program_item'];
				}
			}
		
			//if ( $num_items == 0 ) {
			if ( empty($program_items) ) {
		
				// TODO: eliminate redundancy
			
				// No actual items in this row -- just placeholders
				$row_info .= 'Zero-item program_row -- single table_row with placeholders<br />';
				$tr = array();
				$tds = array();
				$tr_class = "program_objects";
				if ( $show_row == "0" ) { $tr_class .= " hidden"; }
				$i = 0;
				$tr['tr_id'] = "tr-".$r.'-'.$i;
			
				if ( !empty($program_item_label) ) {
					$td_class = "zero_item_row";
					if ( $placeholder_label ) { $td_class .= " placeholder"; }
					$td_content = $program_item_label;
				
					if ( $row_type == "header" || $row_type == "program_note" || $row_type == "label_only" || $row_type == "title_only" ) {
					
						// Single wide column row
						$td_colspan = 2;					
						$row_content = "";
					
						if ( $row_type == "header" ) { 
							$td_class = "header";
							$td_content = $program_item_label;
						} else if ( $row_type == "program_note" ) {
							$td_class = "program_note";
							$td_content = $program_item_name;
						} else if ( $row_type == "label_only" ) {
							$td_class = "label_only";
							$td_content = $program_item_label;
						} else if ( $row_type == "title_only" ) {
							$td_class = "title_only";
							$td_content = $program_item_name;
						}
					} else {
						$td_colspan = 1;
					}
				
					$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
				}
			
				// Get the program item name
				// --------------------
				$arr_item_name = get_program_item_name( array( 'row' => $row, 'program_type' => $program_type, 'row_type' => $row_type, 'program_item_label' => $program_item_label ) );
				// Title as label?
				if ( !empty($arr_item_name['title_as_label']) ) {
					$row_info .= ">> use title_as_label<br />";
					$title_as_label = $arr_item_name['title_as_label'];
					$td_content = $title_as_label;
					$td_class = "title_as_label";
					$td_colspan = 1;
					$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
				} else {
					$title_as_label = null;
				}
			
				// Item Name
				if ( $arr_item_name['item_name'] ) { $program_item_name = $arr_item_name['item_name']; }
				if ( !empty($program_item_name) ) {
					$td_class = "program_item placeholder"; // placeholder because this is a zero-item row
					if ( $title_as_label ) { $td_class .= " authorship"; }
					$td_content = $program_item_name;
					$td_colspan = 1;
					// Add this item as a table cell only for two-column rows -- WIP
					if ( ! ($row_type == "header" || $row_type == "program_note" || $row_type == "label_only" || $row_type == "title_only") ) {
						$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
					}
				}
			
				//
				$tr['tr_class'] = $tr_class;
				$tr['tds'] = $tds;
			
				$table_rows[] = $tr;
			
			} else if ( $num_items == 1 ) {
				// Single-item program_row translates to single table_row
				$row_info .= 'Single-item program_row -- single table_row<br />';
			} else if ( $num_items > 1 ) {
				// Multiple items per program row -- display settings per row_type, program_type... -- WIP
				$row_info .= "*** $num_items program_items found for this row! ***<br />";
			}
		
			$row_info .= " >>>>>>> START foreach program_item <<<<<<<<br />";

			// Loop through the program items for this row (usually there is only one)
			foreach ( $program_items as $i => $program_item_obj_id ) {

				$row_info .= "<br />+~+~+~+~+ program_item #$i +~+~+~+~+<br />";
					
				$tr = array();
				$tds = array();
				$tr_class = "program_objects";
				if ( $show_row == "0" ) { $tr_class .= " hidden"; }
				if ( $num_items > 1 ) { $tr_class .= " row_group"; }
				if ( $num_items > 1 && $i == $num_items-1 ) { $tr_class .= " last_in_group"; }
				$tr['tr_id'] = "tr-".$r.'-'.$i;
			
				//
				$row_info .= "program_item_obj_id: $program_item_obj_id<br />";
				$item_post_type = get_post_type( $program_item_obj_id );
				$row_info .= "item_post_type: $item_post_type<br />";
			
				// If there's a program label set for the row, and if this is the first 
				if ( !empty($program_item_label) ) {
				
					$td_class = "test_td_class_1";
					if ( $placeholder_label ) { $td_class .= " placeholder"; }
				
					if ( $row_type == "header" || $row_type == "program_note" || $row_type == "label_only" || $row_type == "title_only" ) {
					
						// Single column row
						$td_colspan = 2;					
						$row_content = "";
					
						if ( $row_type == "header" ) { 
							$td_class = "header";
							$td_content = $program_item_label;
						} else if ( $row_type == "program_note" ) {
							$td_class = "program_note";
							$td_content = $program_item_name;
						} else if ( $row_type == "label_only" ) {
							$td_class = "label_only";
							$td_content = $program_item_label;
						} else if ( $row_type == "title_only" ) {
							$td_class = "title_only";
							$td_content = $program_item_name;
						}
					} else {
						$td_colspan = 1;
						if ( $i == 0 ) { $td_content = $program_item_label; } else { $td_content = ""; } //$td_content = "***"; }
					}			
				
					$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
				}
				
				if ( $authorship_display_settings && isset($authorship_display_settings[$r.'-'.$i]) ) {
					$display_settings = $authorship_display_settings[$r.'-'.$i];
					$row_info .= "[".$r.'-'.$i."] program_row >> display_settings: ".print_r($display_settings, true)."<br />";
					if ( isset($display_settings['show_name']) && $display_settings['show_name'] ) { $tr_class .= " show_authorship"; } else { $tr_class .= " hide_authorship"; }
					if ( isset($display_settings['show_dates']) && $display_settings['show_dates'] ) { $tr_class .= " show_person_dates"; } else { $tr_class .= " hide_person_dates"; }
				} else {
					$row_info .= "[".$r.'-'.$i."] program_row >> display_settings not set<br />";
				}
			
				// Get the program item name
				// --------------------
				// WIP
				$arr_item_name = get_program_item_name( array( 'row' => $row, 'program_type' => $program_type, 'row_type' => $row_type, 'program_item_obj_id' => $program_item_obj_id, 'program_item_label' => $program_item_label ) );
				// Title as label?
				if ( !empty($arr_item_name['title_as_label']) ) {
					$row_info .= ">> use title_as_label<br />";
					$title_as_label = $arr_item_name['title_as_label'];
					$td_content = $title_as_label;
					$td_class = "title_as_label";
					$td_colspan = 1;
					$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
				} else {
					$title_as_label = null;
				}
			
				$row_info .= "START arr_item_name['ts_info']<br />";
				$row_info .= $arr_item_name['ts_info']; // ts_info is already commented
				$row_info .= "END arr_item_name['ts_info']<br />";
				//$row_info .= "arr_item_name['info']: <pre>".$arr_item_name['info']."</pre>";
			
				// Program item_name
				if ( $arr_item_name['item_name'] ) { $program_item_name = $arr_item_name['item_name']; }
				if ( !empty($program_item_name) ) {
					$td_class = "program_item";
					if ( $title_as_label ) { $td_class .= " authorship"; }
					$td_content = $program_item_name;
					$td_colspan = 1;
					// Add this item as a table cell only for two-column rows -- WIP
					if ( ! ($row_type == "header" || $row_type == "program_note" || $row_type == "label_only" || $row_type == "title_only") ) {
						$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
					}
				}
			
				$tr['tr_class'] = $tr_class;
				$tr['tds'] = $tds;
					
				$table_rows[] = $tr;
				
			}
			
			$row_info .= '--------------------<br />';
		
				// Get the program item name
				// --------------------
				//$item_name_args = array();
				//$item_name_args['show_item_authorship'] = null; // wip -- get value true/false based on $program_composers array
				//$arr_item_name = get_program_item_name($item_name_args);
			
			
				// Match Placeholders
				//if ( $run_updates == true ) { match_placeholders($row); }
			
				// Cleanup/Deletion of extra/empty program rows
				// --------------------
				/*
				// Check for extra/empty rows -- prep to delete them
				if ( empty($program_item_label) && empty($program_item_name) ) {
					// Empty row -- no label, no item
					$delete_row = true;
					$row_info .= "[$i] delete the row, because everything is empty.<br />";
				} else if ( ( $program_item_label == "x" || $program_item_label == "")
					&& ( $program_item_label == "x" || $program_item_label == "*NULL*" || $program_item_label == "" ) 
					&& ( $program_item_name == "x" || $program_item_name == "*NULL*" || $program_item_name == "" ) 
				   ) {
					// Both label and item are either placeholder junk or empty
					$delete_row = true;
					$row_info .= "[$i] delete the row, because everything is meaningless.<br />";
				} else if ( $program_item_label == "*NULL*" || $program_item_name == "*NULL*" ) {
					// TODO: ???
					if ( $program_item_label == "*NULL*" ) { $program_item_label = ""; }
					if ( $program_item_name == "*NULL*" ) { $program_item_name = ""; }
				}
			
				if ( $run_updates == true ) { $do_deletions = true; } else { $do_deletions = false; }
				$do_deletions = false; // tft -- failsafe!
			
			
				// If the row is empty/x-filled and needs to be deleted, then do so
				if ( $delete_row == true ) {
				
					//sdg_log( "divline1", $do_log );
					//sdg_log( "program row to be deleted:", $do_log );
					//sdg_log( print_r($row, true), $do_log );
					$row_info .= "row: ".print_r($row, true)."<br />";
					$row_info .= "[$i] program row to be deleted<br />";
					$row_info .= "[$i] program row: item_label_txt='".$row['item_label_txt']."'; item_label='".$row['item_label']."'; program_item_txt='".$row['program_item_txt']."'<br />";
					//$row_info .= "[$i] program row: program_item='".print_r($row['program_item'], true)."'<br />";
				
					// ... but only run the action if this is the first deletion for this post_id in this round
					// ... because if a row has already been deleted then the row indexes will have changed for all the personnel rows
					// ... and though it would likely not be so difficult to reset the row index accordingly, for now let's proceed with caution...
					if ( $deletion_count == 0 && $do_deletions == true ) {

						if ( delete_row('program_items', $i, $post_id) ) { // ACF function: https://www.advancedcustomfields.com/resources/delete_row/ -- syntax: delete_row($selector, $row_num, $post_id) 
							$row_info .= "[program row $i deleted]<br />";
							$deletion_count++;
							//sdg_log( "[program row $i deleted successfully]", $do_log );
						} else {
							$row_info .= "[deletion failed for program row $i]<br />";
							//sdg_log( "[failed to delete program row $i]", $do_log );
						}
					
					} else {
					
						if ( $do_deletions == true ) {
							$row_info .= "[$i] row to be deleted on next round due to row_index issues.<br />";
							//sdg_log( "row to be deleted on next round due to row_index issues.", $do_log );
						} else {
							$row_info .= "[$i] row to be deleted when do_deletions is re-enabled.<br />";
							//sdg_log( "row to be deleted when do_deletions is re-enabled.", $do_log );
						}
					}
				
				}
				*/
			
			
				// Display the row if it's a header, or if BOTH item_label and item_name are not empty
				// --------------------
			
				// Set up the table row
				/*if ( $display == 'table' && $delete_row != true ) {
					$tr_class = "program_objects";
					if ( $show_row == '0' || $show_row == 0 ) { $tr_class .= " hidden"; } else { $tr_class .= " show_row"; }
					if ( $show_person_dates == false ) { $tr_class .= " hide_person_dates"; } else { $tr_class .= " show_person_dates"; }
					if ( $num_row_items > 1 || $grouped_row == true ) { $tr_class .= " grouping"; }
					$table .= '<tr class="'.$tr_class.'">';
				}*/
			
				// Insert row_info for troubleshooting
				if ( $do_ts ) {
					if ( $display == 'table' ) {
						//$table .= '<div class="troubleshooting">row_info:<br />'.$row_info.'</div>'; //$row_info; // Display comments w/ in row for ease of parsing dev notes
					} else {
						//$ts_info .= 'row_info:<br />'.$row_info;
					}
					$ts_info .= $row_info; //$ts_info .= 'row_info:<br />'.$row_info;
				}
			
				// Add the table cells and close out the row
				/*if ( $display == 'table' && $delete_row != true ) {
				
					if ( $row_type == "header" || $row_type == "program_note" || $row_type == "label_only" || $row_type == "title_only" ) {
					
						// Single column row
						$row_content = "";
						if ( $row_type == "header" ) { 
							$td_class = "header";
							$row_content = $program_item_label;
						} else if ( $row_type == "program_note" ) {
							$td_class = "program_note";
							$row_content = $program_item_name;
						} else if ( $row_type == "label_only" ) {
							$td_class = "label_only";
							$row_content = $program_item_label;
						} else if ( $row_type == "title_only" ) {
							$td_class = "title_only";
							$row_content = $program_item_name;
						}
						if ( $placeholder_label == true ) { $td_class .= " placeholder"; }
						$table .= '<td class="'.$td_class.'" colspan="2">'.$row_content.'</td>';
					
					} else {
					
						// Two column standard row
					
						$td_class = "program_label";
					
						if ( $show_item_label != true || empty($program_item_label) ) { $td_class .= " no_label"; }
						if ( $placeholder_label == true ) { $td_class .= " placeholder"; }
						if ( $label_update_required == true ) { $td_class .= " update_required"; }
					
						$table .= '<td class="'.$td_class.'">'.$program_item_label.'</td>';
						$td_class = "program_item";
						if ( $placeholder_item == true ) { $td_class .= " placeholder"; }
						$table .= '<td class="'.$td_class.'">'.$program_item_name.'</td>';
					
					}
					$table .= '</tr>';
				}*/
			
				// Data Cleanup -- WIP
				// ...figuring out how to sync repertoire related_events w/ updates to program items -- display some TS info to aid this process
				/*if ( $do_ts ) {
					$arr_row_info = event_program_row_cleanup ( $post_id, $i, $row, "program_items" );								
					$ts_info .= $arr_row_info['info'];
					$row_errors = $arr_row_info['errors'];
					//if ( $row_errors ) { $post_errors = true; }
					if ( isset($row['program_item'][0]) ) {
						foreach ( $row['program_item'] as $program_item_obj_id ) {						
							$item_post_type = get_post_type( $program_item_obj_id );						
							if ( $item_post_type == 'repertoire' ) {
								// Update the repertoire_events field for this rep record, as needed
								$ts_info .= update_repertoire_events( $program_item_obj_id, false, array($post_id) );							
							}					
						}
					}
				}*/

				// --------------------
			
			//$i++;
		
		
		} // END foreach( $program_rows as $row )
    
    }
    
    /////
    
    // Build the table based on the table_rows array
    if ( $display == 'table' && count($table_rows) > 0 ) {
        
        if ( $display == 'table' ) {
        	$table_classes = "event_program program ".$program_layout;
            $table = '<table class="'.$table_classes.'">';
            $table .= '<tbody>';
        }

        // Has a Program Items header been designated? If so, then display it.
        $program_items_header = get_post_meta( $post_id, 'program_items_header', true );
        if ( $display == 'table' && !empty($program_items_header) ) {
            $table .= '<tr><th colspan="2"><h2>'.$program_items_header.'</h2></th></tr>'; //class=""
        } else {
        	// TBD display of header for non-table display
        }
        
        // --------------------
        
        foreach( $table_rows as $tr ) {
        
        	//$ts_info .= "tr: <pre>".print_r($tr, true)."</pre><br />";
        	
        	$table .= '<tr id="'.$tr['tr_id'].'" class="'.$tr['tr_class'].'">';
        	foreach ( $tr['tds'] as $td ) {
        		$table .= '<td class="'.$td['td_class'].'" colspan="'.$td['td_colspan'].'">'.$td['td_content'].'</td>';
        	}
        	$table .= '</tr>';
        	
        	/*
        
        	//$tr;
        	//$show_row
        	//$row_type
        	//$td_class
        	//$td_class1
        	//$td_class2
        	//$show_item_authorship = true;
			//$show_person_dates = true;
        	
        	
        	// Set up the table row
			$tr_class = "program_objects";
			if ( $show_row == '0' || $show_row == 0 ) { $tr_class .= " hidden"; } else { $tr_class .= " show_row"; }
			if ( $show_person_dates == false ) { $tr_class .= " hide_person_dates"; } else { $tr_class .= " show_person_dates"; }
            if ( $num_row_items > 1 || $grouped_row == true ) { $tr_class .= " grouping"; }
            
			$table .= '<tr class="'.$tr_class.'">';
			
			if ( $row_type == "header" || $row_type == "program_note" || $row_type == "label_only" || $row_type == "title_only" ) {
                    
				// Single column row
				
				$row_content = "";
				if ( $row_type == "header" ) { 
					$td_class = "header";
					$row_content = $program_item_label;
				} else if ( $row_type == "program_note" ) {
					$td_class = "program_note";
					$row_content = $program_item_name;
				} else if ( $row_type == "label_only" ) {
					$td_class = "label_only";
					$row_content = $program_item_label;
				} else if ( $row_type == "title_only" ) {
					$td_class = "title_only";
					$row_content = $program_item_name;
				}
				if ( $placeholder_label == true ) { $td_class .= " placeholder"; }
				$table .= '<td class="'.$td_class.'" colspan="2">'.$row_content.'</td>';
				
			} else {
				
				// Two column standard row
				
				$td_class = "program_label";
				
				if ( $show_item_label != true || empty($program_item_label) ) { $td_class .= " no_label"; }
				if ( $placeholder_label == true ) { $td_class .= " placeholder"; }
				if ( $label_update_required == true ) { $td_class .= " update_required"; }
				
				$table .= '<td class="'.$td_class.'">'.$program_item_label.'</td>';
				$td_class = "program_item";
				if ( $placeholder_item == true ) { $td_class .= " placeholder"; }
				$table .= '<td class="'.$td_class.'">'.$program_item_name.'</td>';
				
			}
				
			$table .= '</tr>';
			*/
        }
		
		// --------------------
		
		// Close the table
        if ( $display == 'table' ) {
            $table .= '</tbody>';
            $table .= '</table>';
        }
        
        $info .= $table;
        
    } // end if $rows
    
    $arr_info['info'] = $info;
    $arr_info['ts_info'] = $ts_info;
	return $arr_info;
	
}


function get_list_items_v1( $atts = array() ) {
    
    // TS/logging setup
    $do_ts = devmode_active( array("dcp") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
	$args = shortcode_atts( array(
		'post_id'        => get_the_ID(),
        'run_updates' => false,
        'display' => 'table'       
    ), $atts );
    
    // Extract
	extract( $args );
    
    // Init vars
    $arr_info = array();
    $info = "";
    $ts_info = "";
    $ts_info .= "===== get_list_items =====<br />";
    
    if ( $display == 'table' ) { $table = ""; }
    
    // TODO: deal more thoroughly w/ non-table display option, or eliminate that parameter altogether.
	
	if ($post_id == null) { $post_id = get_the_ID(); }
	$ts_info .= "Items for post_id: $post_id<br />";
    //$ts_info .= "display: $display<br />";
    
    // What type of list is this? Music list? Other kind of list?
    //$list_type = get_post_meta( $post_id, 'list_type', true );
    //$ts_info .= "list_type: $list_type<br />";
    
	// Get the list item repeater field values (ACF)
    $list_rows = get_field('list_items', $post_id); // ACF function: https://www.advancedcustomfields.com/resources/get_field/ -- TODO: change to use have_rows() instead?   
    if ( empty($list_rows) ) { $list_rows = array(); }    
    if ( is_array($list_rows) ) { $ts_info .= count($list_rows)." list_items/list_rows<br />"; } else { $ts_info .= "list_rows: ".print_r($list_rows, true); }
    
    // WIP: streamlining
    
	$authorship_display_settings = null;
	$list_composer_ids = array(); // TMP -- deprecated
    $deletion_count = 0;
    $table_rows = array();
    
    if ( is_array($list_rows) ) {
	
		// Check to see if ANY of the rows contains items with post_type == 'repertoire'
		if ( list_contains_repertoire($list_rows) ) { // TODO: generalize the following to apply to any items with authorship, not just rep/composers
	
			$ts_info .= "list contains repertoire items<br />";
			// If so, then get all the list composers
			$list_item_ids = get_list_item_ids($list_rows);
			$ts_info .= "list_item_ids: ".print_r($list_item_ids, true)."<br />";
			//
			$authorship_display_settings = set_row_authorship_display($list_item_ids);
			$ts_info .= "authorship_display_settings: <pre>".print_r($authorship_display_settings, true)."</pre><br />";
		
		}
	
		// Translate list_rows into table_rows (separate row per item)
    
		foreach( $list_rows as $r => $row ) {
		
			// TODO: check if row is empty >> next
		
			// Initialize variables
			$row_info = ""; // Info for troubleshooting
			$row_info .= "-------------------- [row $r] --------------------<br />";
		
			$grouped_row = false; // For multiple-item rows
			//
			$placeholder_label = false;
			$placeholder_item = false;
			//
			$arr_item_label = array();
			$arr_item_name = array();
			//
			$list_item_label = null;
			$list_item_name = null;
			//
			$show_person_dates = true;
		
			//
			$label_update_required = false;
			$delete_row = false;
		
			//$row_info .= "get_list_items ==> list row [$r]: ".print_r($row, true)."<br />";
			
			// Is a row_type set? WIP -- working on phasing out deprecated fields like 'show_item_label' in favor of simple row_types setup
			if ( isset($row['row_type']) ) { $row_type = $row['row_type']; } else { $row_type = null; }
			$row_info .= "get_list_items ==> row_type: ".$row_type."<br />";
			
			// ROW TYPES WIP: 
			/*
				List item rows types:
				default : Standard two-column row
				header : Header row
				list_note : Program note
				label_only : Item label only
				title_only : Item title only
				//
				Additional option to consider, TBD:
				split : Title left/Authorship right
				//
				if ( $row_type == 'title_only' ) {
					
					$arr_item_name = get_rep_info( $list_item_obj_id, 'display', $show_item_authorship, true );
					$item_name = $arr_item_name['info'];
					$ts_info .= $arr_item_name['ts_info'];
			
				} else if ( empty($list_item_label) ) {

					$ts_info .= "list_item_label is empty >> use title in left col<br />";
				
				}
			*/
			
			// Is this a header row? (Check for deprecated field values)
			if ( $row_type != "header" && isset($row['is_header']) && $row['is_header'] == 1 ) { $row_type = "header"; } // TODO: update the row_type in the DB
		
			// Should this row be displayed on the front end?
			// TODO: modify to simplify as below -- set to true/false based on stored value, if any
			if ( isset($row['show_row']) && $row['show_row'] != "" ) { 
				$show_row = $row['show_row'];
				$row_info .= "get_list_items ==> show_row = '".$row['show_row']."'<br />";
			} else { 
				$show_row = 1; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
				$row_info .= "get_list_items ==> show_row = 1 (default)<br />";
			}
		
			// Should we display the item label for this row?
			// TODO: streamline/eliminate this deprecated field and simply update row_type
			if ( isset($row['show_item_label']) && $row['show_item_label'] == "0" ) { 
				$show_item_label = false;
				$row_info .= "get_list_items ==> show_item_label FALSE<br />";
			} else { 
				$show_item_label = true; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
				$row_info .= "get_list_items ==> show_item_label TRUE (default)<br />";
			}
				
			// Should the item title for this row be displayed on the front end?
			// TODO: streamline/eliminate this deprecated field and simply update row_type
			if ( isset($row['show_item_title']) && $row['show_item_title'] == "0" ) { 
				$show_item_title = false;
				$row_info .= "show_item_title = 0, i.e. false<br />";
			} else { 
				$show_item_title = true; // Default to 'Yes'/true/show the row if no zero value has been saved explicitly
				$row_info .= "default: show_item_title = true<br />";
			}
		
			// Get the item label
			// --------------------
			// TODO/WIP: maybe figure out how to skip this for rep items in $list_type == "concert_program" where title is used in left col instead of label			
			if ( $show_item_label == true && $row_type != 'title_only' ) {			
				$row_info .= "get_list_item_label<br />";
				$arr_item_label = get_list_item_label($row);
				$list_item_label = $arr_item_label['item_label'];
				$placeholder_label = $arr_item_label['placeholder'];
				$row_info .= $arr_item_label['ts_info'];
				$row_info .= ">> list_item_label: $list_item_label<br />";
			}
		
			// Program item(s)
			$list_items = array();
			$num_items = 0;
			if ( $show_item_title == true && $row_type != 'header' ) {
				// Does the row contain one or more item objects?			
				//$num_items = 1; // default: Single-item list_row translates to single table_row
				if ( isset($row['list_item']) && is_array($row['list_item']) ) {
					//$ts_info .= "list_item: ".print_r($row['list_item'], true)."<br />";
					$num_items = count($row['list_item']);
					$list_items = $row['list_item'];
				}
			}
		
			//if ( $num_items == 0 ) {
			if ( empty($list_items) ) {
		
				// TODO: eliminate redundancy
			
				// No actual items in this row -- just placeholders
				$row_info .= 'Zero-item list_row -- single table_row with placeholders<br />';
				$tr = array();
				$tds = array();
				$tr_class = "list_objects";
				if ( $show_row == "0" ) { $tr_class .= " hidden"; }
				$i = 0;
				$tr['tr_id'] = "tr-".$r.'-'.$i;
			
				if ( !empty($list_item_label) ) {
					$td_class = "zero_item_row";
					if ( $placeholder_label ) { $td_class .= " placeholder"; }
					$td_content = $list_item_label;
				
					if ( $row_type == "header" || $row_type == "list_note" || $row_type == "label_only" || $row_type == "title_only" ) {
					
						// Single wide column row
						$td_colspan = 2;					
						$row_content = "";
					
						if ( $row_type == "header" ) { 
							$td_class = "header";
							$td_content = $list_item_label;
						} else if ( $row_type == "list_note" ) {
							$td_class = "list_note";
							$td_content = $list_item_name;
						} else if ( $row_type == "label_only" ) {
							$td_class = "label_only";
							$td_content = $list_item_label;
						} else if ( $row_type == "title_only" ) {
							$td_class = "title_only";
							$td_content = $list_item_name;
						}
					} else {
						$td_colspan = 1;
					}
				
					$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
				}
			
				// Get the list item name
				// --------------------
				$arr_item_name = get_list_item_name( array( 'row' => $row, 'list_type' => $list_type, 'row_type' => $row_type, 'list_item_label' => $list_item_label ) );
				// Title as label?
				if ( !empty($arr_item_name['title_as_label']) ) {
					$row_info .= ">> use title_as_label<br />";
					$title_as_label = $arr_item_name['title_as_label'];
					$td_content = $title_as_label;
					$td_class = "title_as_label";
					$td_colspan = 1;
					$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
				} else {
					$title_as_label = null;
				}
			
				// Item Name
				if ( $arr_item_name['item_name'] ) { $list_item_name = $arr_item_name['item_name']; }
				if ( !empty($list_item_name) ) {
					$td_class = "list_item placeholder"; // placeholder because this is a zero-item row
					if ( $title_as_label ) { $td_class .= " authorship"; }
					$td_content = $list_item_name;
					$td_colspan = 1;
					// Add this item as a table cell only for two-column rows -- WIP
					if ( ! ($row_type == "header" || $row_type == "list_note" || $row_type == "label_only" || $row_type == "title_only") ) {
						$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
					}
				}
			
				//
				$tr['tr_class'] = $tr_class;
				$tr['tds'] = $tds;
			
				$table_rows[] = $tr;
			
			} else if ( $num_items == 1 ) {
				// Single-item list_row translates to single table_row
				$row_info .= 'Single-item list_row -- single table_row<br />';
			} else if ( $num_items > 1 ) {
				// Multiple items per list row -- display settings per row_type, list_type... -- WIP
				$row_info .= "*** $num_items list_items found for this row! ***<br />";
			}
		
			$row_info .= " >>>>>>> START foreach list_item <<<<<<<<br />";

			// Loop through the list items for this row (usually there is only one)
			foreach ( $list_items as $i => $list_item_obj_id ) {

				$row_info .= "<br />+~+~+~+~+ list_item #$i +~+~+~+~+<br />";
					
				$tr = array();
				$tds = array();
				$tr_class = "list_objects";
				if ( $show_row == "0" ) { $tr_class .= " hidden"; }
				if ( $num_items > 1 ) { $tr_class .= " row_group"; }
				if ( $num_items > 1 && $i == $num_items-1 ) { $tr_class .= " last_in_group"; }
				$tr['tr_id'] = "tr-".$r.'-'.$i;
			
				//
				$row_info .= "list_item_obj_id: $list_item_obj_id<br />";
				$item_post_type = get_post_type( $list_item_obj_id );
				$row_info .= "item_post_type: $item_post_type<br />";
			
				// If there's a list label set for the row, and if this is the first 
				if ( !empty($list_item_label) ) {
				
					$td_class = "test_td_class_1";
					if ( $placeholder_label ) { $td_class .= " placeholder"; }
				
					if ( $row_type == "header" || $row_type == "list_note" || $row_type == "label_only" || $row_type == "title_only" ) {
					
						// Single column row
						$td_colspan = 2;					
						$row_content = "";
					
						if ( $row_type == "header" ) { 
							$td_class = "header";
							$td_content = $list_item_label;
						} else if ( $row_type == "list_note" ) {
							$td_class = "list_note";
							$td_content = $list_item_name;
						} else if ( $row_type == "label_only" ) {
							$td_class = "label_only";
							$td_content = $list_item_label;
						} else if ( $row_type == "title_only" ) {
							$td_class = "title_only";
							$td_content = $list_item_name;
						}
					} else {
						$td_colspan = 1;
						if ( $i == 0 ) { $td_content = $list_item_label; } else { $td_content = ""; } //$td_content = "***"; }
					}			
				
					$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
				}
				
				if ( $authorship_display_settings && isset($authorship_display_settings[$r.'-'.$i]) ) {
					$display_settings = $authorship_display_settings[$r.'-'.$i];
					$row_info .= "[".$r.'-'.$i."] list_row >> display_settings: ".print_r($display_settings, true)."<br />";
					if ( isset($display_settings['show_name']) && $display_settings['show_name'] ) { $tr_class .= " show_authorship"; } else { $tr_class .= " hide_authorship"; }
					if ( isset($display_settings['show_dates']) && $display_settings['show_dates'] ) { $tr_class .= " show_person_dates"; } else { $tr_class .= " hide_person_dates"; }
				} else {
					$row_info .= "[".$r.'-'.$i."] list_row >> display_settings not set<br />";
				}
			
				// Get the list item name
				// --------------------
				// WIP
				$arr_item_name = get_list_item_name( array( 'row' => $row, 'list_type' => $list_type, 'row_type' => $row_type, 'list_item_obj_id' => $list_item_obj_id, 'list_item_label' => $list_item_label ) );
				// Title as label?
				if ( !empty($arr_item_name['title_as_label']) ) {
					$row_info .= ">> use title_as_label<br />";
					$title_as_label = $arr_item_name['title_as_label'];
					$td_content = $title_as_label;
					$td_class = "title_as_label";
					$td_colspan = 1;
					$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
				} else {
					$title_as_label = null;
				}
			
				$row_info .= "START arr_item_name['ts_info']<br />";
				$row_info .= $arr_item_name['ts_info']; // ts_info is already commented
				$row_info .= "END arr_item_name['ts_info']<br />";
				//$row_info .= "arr_item_name['info']: <pre>".$arr_item_name['info']."</pre>";
			
				// Program item_name
				if ( $arr_item_name['item_name'] ) { $list_item_name = $arr_item_name['item_name']; }
				if ( !empty($list_item_name) ) {
					$td_class = "list_item";
					if ( $title_as_label ) { $td_class .= " authorship"; }
					$td_content = $list_item_name;
					$td_colspan = 1;
					// Add this item as a table cell only for two-column rows -- WIP
					if ( ! ($row_type == "header" || $row_type == "list_note" || $row_type == "label_only" || $row_type == "title_only") ) {
						$tds[] = array( 'td_class' => $td_class, 'td_colspan' => $td_colspan, 'td_content' => $td_content );
					}
				}
			
				$tr['tr_class'] = $tr_class;
				$tr['tds'] = $tds;
					
				$table_rows[] = $tr;
				
			}
			
			$row_info .= '--------------------<br />';
				
			// Insert row_info for troubleshooting
			if ( $do_ts ) {
				if ( $display == 'table' ) {
					//$table .= '<div class="troubleshooting">row_info:<br />'.$row_info.'</div>'; //$row_info; // Display comments w/ in row for ease of parsing dev notes
				} else {
					//$ts_info .= 'row_info:<br />'.$row_info;
				}
				$ts_info .= $row_info; //$ts_info .= 'row_info:<br />'.$row_info;
			}
		
			// --------------------
		
		
		} // END foreach( $list_rows as $row )
    
    }
    
    /////
    
    // Build the table based on the table_rows array
    if ( $display == 'table' && count($table_rows) > 0 ) {
        
        if ( $display == 'table' ) {
        	$table_classes = "list ".$list_layout;
            $table = '<table class="'.$table_classes.'">';
            $table .= '<tbody>';
        }

        // Has a Program Items header been designated? If so, then display it.
        $list_items_header = get_post_meta( $post_id, 'list_items_header', true );
        if ( $display == 'table' && !empty($list_items_header) ) {
            $table .= '<tr><th colspan="2"><h2>'.$list_items_header.'</h2></th></tr>'; //class=""
        } else {
        	// TBD display of header for non-table display
        }
        
        // --------------------
        
        foreach( $table_rows as $tr ) {
        
        	//$ts_info .= "tr: <pre>".print_r($tr, true)."</pre><br />";
        	
        	$table .= '<tr id="'.$tr['tr_id'].'" class="'.$tr['tr_class'].'">';
        	foreach ( $tr['tds'] as $td ) {
        		$table .= '<td class="'.$td['td_class'].'" colspan="'.$td['td_colspan'].'">'.$td['td_content'].'</td>';
        	}
        	$table .= '</tr>';
        	
        }
		
		// --------------------
		
		// Close the table
        if ( $display == 'table' ) {
            $table .= '</tbody>';
            $table .= '</table>';
        }
        
        $info .= $table;
        
    } // end if $rows
    
    $arr_info['info'] = $info;
    $arr_info['ts_info'] = $ts_info;
	return $arr_info;
	
}


///// ****** END ACF Repeater Rows Display Function(s) -- WIP ****** /////

/***  SEARCH FORM ***/

// TODO: generalize the following to make this functionality not so repertoire-specific
// See also SDG common_functions.php -- new version WIP
// https://www.advancedcustomfields.com/resources/creating-wp-archive-custom-field-filter/
add_shortcode('birdhive_search_form', 'birdhive_search_form');
function birdhive_search_form ( $atts = array(), $content = null, $tag = '' ) {

	// TS/logging setup
    $do_ts = devmode_active( array("dcp", "search") );
    $do_log = false;
    sdg_log( "divline2", $do_log );
	
	// Init vars
	$info = "";
    $ts_info = "";
    //$search_values = false; // var to track whether any search values have been submitted on which to base the search
    $search_values = array(); // var to track whether any search values have been submitted and to which post_types they apply
    
    $ts_info .= '_GET: <pre>'.print_r($_GET,true).'</pre>'; // tft
    //$ts_info .= '_REQUEST: <pre>'.print_r($_REQUEST,true).'</pre>'; // tft
        
	$args = shortcode_atts( array(
		'post_type'    => 'post',
		'fields'       => null,
        'form_type'    => 'simple_search',
        'limit'        => '-1'
    ), $atts );
    
    // Extract
	extract( $args );
    
    //$info .= "form_type: $form_type<br />"; // tft

    // After building the form, assuming any search terms have been submitted, we're going to call the function birdhive_get_posts
    // In prep for that search call, initialize some vars to be used in the args array
    // Set up basic query args
    $wp_args = array(
		'post_type'       => array( $post_type ), // Single item array, for now. May add other related_post_types -- e.g. repertoire; edition
		'post_status'     => 'publish',
		'posts_per_page'  => $limit, //-1, //$posts_per_page,
        'orderby'         => 'title',
        'order'           => 'ASC',
        'return_fields'   => 'ids',
	);
    
    // WIP / TODO: fine-tune ordering -- 1) rep with editions, sorted by title_clean 2) rep without editions, sorted by title_clean
    /*
    'orderby'	=> 'meta_value',
    'meta_key' 	=> '_event_start_date',
    'order'     => 'DESC',
    */
    
    //
    $meta_query = array();
    $meta_query_related = array();
    $tax_query = array();
    $tax_query_related = array();
    //$options_posts = array();
    //
    $mq_components_primary = array(); // meta_query components
    $tq_components_primary = array(); // tax_query components
    $mq_components_related = array(); // meta_query components
    $tq_components_related = array(); // tax_query components
    //$mq_components = array(); // meta_query components
    //$tq_components = array(); // tax_query components
    
    // Get related post type(s), if any
    if ( $post_type == "repertoire" ) {
        $related_post_type = 'edition';
    } else {
        $related_post_type = null;
    }
    
    // init -- determines whether or not to *search* multiple post types -- depends on kinds of search values submitted
    $search_primary_post_type = false;
    $search_related_post_type = false;
    $query_assignment = "primary"; // init -- each field pertains to either primary or related query
    
    // Check to see if any fields have been designated via the shortcode attributes
    if ( $fields ) {
        
        // Turn the fields list into an array
        $arr_fields = birdhive_att_explode( $fields ); //if ( function_exists('sdg_att_explode') ) { }
        //$info .= print_r($arr_fields, true); // tft
        
        // e.g. http://stthomas.choirplanner.com/library/search.php?workQuery=Easter&composerQuery=Williams
        
        $info .= '<form class="birdhive_search_form '.$form_type.'">';
        //$info .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" class="birdhive_search_form '.$form_type.'">';
        
        // Get all ACF field groups associated with the primary post_type
        $field_groups = acf_get_field_groups( array( 'post_type' => $post_type ) );
        
        // Get all taxonomies associated with the primary post_type
        $taxonomies = get_object_taxonomies( $post_type );
        //$info .= "taxonomies for post_type '$post_type': <pre>".print_r($taxonomies,true)."</pre>"; // tft
        
        ///
        $search_operator = "and"; // init
        
        // Loop through the field names and create the actual form fields
        foreach ( $arr_fields as $arr_field ) {
            
            $field_info = ""; // init
            $field_name = $arr_field; // may be overrriden below
            $alt_field_name = null; // for WIP fields/transition incomplete, e.g. repertoire_litdates replacing related_liturgical_dates
                    
            // Fine tune the field name
            if ( $field_name == "title" ) {
                $placeholder = "title"; // for input field
                if ( $post_type == "repertoire" ) { // || $post_type == "edition"
                    $field_name = "title_clean"; // todo -- address problem that editions don't have this field
                    //$field_name = "post_title";
                } else {
                    $field_name = "post_title";
                    //$field_name = "s";
                }
            } else {
                $placeholder = $field_name; // for input field
            }
            
            if ( $form_type == "advanced_search" ) {
                $field_label = str_replace("_", " ",ucfirst($placeholder));
                if ( $field_label == "Repertoire category" ) { 
                    $field_label = "Category";
                } else if ( $field_name == "liturgical_date" || $field_label == "Related liturgical dates" ) { 
                    $field_label = "Liturgical Dates";
                    $field_name = "repertoire_litdates";
                    $alt_field_name = "related_liturgical_dates";
                }/* else if ( $field_name == "edition_publisher" ) {
                    $field_label = "Publisher";
                }*/
            }
            
            // Check to see if the field_name is an actual field, separator, or search operator
            if ( str_starts_with($field_name, '&') ) { 
                
                // This "field" is a separator/text between fields                
                $info .= substr($field_name,1).'&nbsp;';
                
            } else if ( $field_name == 'search_operator' ) {
                
                // This "field" is a search operator, i.e. search type
                
                if ( !isset($_GET[$field_name]) || empty($_GET[$field_name]) ) { $search_operator = 'and'; } else { $search_operator = $_GET[$field_name]; } // default to "and"

                $info .= 'Search Type: ';
                $info .= '<input type="radio" id="and" name="search_operator" value="and"';
                if ( $search_operator == 'and' ) { $info .= ' checked="checked"'; }
                $info .= '>';
                $info .= '<label for="and">AND <span class="tip">(match all criteria)</span></label>&nbsp;';
                $info .= '<input type="radio" id="or" name="search_operator" value="or"';
                if ( $search_operator == 'or' ) { $info .= ' checked="checked"'; }
                $info .= '>';
                $info .= '<label for="or">OR <span class="tip">(match any)</span></label>';
                $info .= '<br />';
                        
            } else if ( $field_name == 'devmode' ) {
                
                // This "field" is for testing/dev purposes only
                
                if ( !isset($_GET[$field_name]) || empty($_GET[$field_name]) ) { $devmode = 'true'; } else { $devmode = $_GET[$field_name]; } // default to "true"

                $info .= 'Dev Mode?: ';
                $info .= '<input type="radio" id="devmode" name="devmode" value="true"';
                if ( $devmode == 'true' ) { $info .= ' checked="checked"'; }
                $info .= '>';
                $info .= '<label for="true">True</label>&nbsp;';
                $info .= '<input type="radio" id="false" name="devmode" value="false"';
                if ( $devmode !== 'true' ) { $info .= ' checked="checked"'; }
                $info .= '>';
                $info .= '<label for="false">False</label>';
                $info .= '<br />';
                        
            } else {
                
                // This is an actual search field
                
                // init/defaults
                $field_type = null; // used to default to "text"
                $pick_object = null; // ?pods?
                $pick_custom = null; // ?pods?
                $field = null;
                $field_value = null;
                
                // First, deal w/ title field -- special case
                if ( $field_name == "post_title" ) {
                    $field = array( 'type' => 'text', 'name' => $field_name );
                }
                //if ( $field_name == "edition_publisher"
                
                // Check to see if a field by this name is associated with the designated post_type -- for now, only in use for repertoire(?)
                $field = match_group_field( $field_groups, $field_name );
                
                if ( $field ) {
                    
                    // if field_name is same as post_type, must alter it to prevent automatic redirect when search is submitted -- e.g. "???"
                    if ( post_type_exists( $arr_field ) ) {
                        $field_name = $post_type."_".$arr_field;
                    }
                    
                    $query_assignment = "primary";
                    
                } else {
                    
                    //$field_info .= "field_name: $field_name -- not found for $post_type >> look for related field.<br />"; // tft
                    
                    // If no matching field was found in the primary post_type, then
                    // ... get all ACF field groups associated with the related_post_type(s)                    
                    $related_field_groups = acf_get_field_groups( array( 'post_type' => $related_post_type ) );
                    $field = match_group_field( $related_field_groups, $field_name );
                                
                    if ( $field ) {
                        
                        // if field_name is same as post_type, must alter it to prevent automatic redirect when search is submitted -- e.g. "publisher" => "edition_publisher"
                        if ( post_type_exists( $arr_field ) ) {
                            $field_name = $related_post_type."_".$arr_field;
                        }
                        $query_assignment = "related";
                        $field_info .= "field_name: $field_name found for related_post_type: $related_post_type.<br />"; // tft    
                        
                    } else {
                        
                        // Still no field found? Check taxonomies 
                        //$field_info .= "field_name: $field_name -- not found for $related_post_type either >> look for taxonomy.<br />"; // tft
                        
                        // For field_names matching taxonomies, check for match in $taxonomies array
                        if ( taxonomy_exists( $field_name ) ) {
                            
                            $field_info .= "$field_name taxonomy exists.<br />";
                                
                            if ( in_array($field_name, $taxonomies) ) {

                                $query_assignment = "primary";                                    
                                $field_info .= "field_name $field_name found in primary taxonomies array<br />";

                            } else {

                                // Get all taxonomies associated with the related_post_type
                                $related_taxonomies = get_object_taxonomies( $related_post_type );

                                if ( in_array($field_name, $related_taxonomies) ) {

                                    $query_assignment = "related";
                                    $field_info .= "field_name $field_name found in related taxonomies array<br />";                                        

                                } else {
                                    $field_info .= "field_name $field_name NOT found in related taxonomies array<br />";
                                }
                                //$info .= "taxonomies for post_type '$related_post_type': <pre>".print_r($related_taxonomies,true)."</pre>"; // tft

                                $field_info .= "field_name $field_name NOT found in primary taxonomies array<br />";
                            }
                            
                            $field = array( 'type' => 'taxonomy', 'name' => $field_name );
                            
                        } else {
                            $field_info .= "Could not determine field_type!<br />";
                        }
                    }
                }                
                
                if ( $field ) {
                    
                    //$field_info .= "field: <pre>".print_r($field,true)."</pre>"; // tft
                    
                    if ( isset($field['post_type']) ) { $field_post_type = $field['post_type']; } else { $field_post_type = null; } // ??
                    
                    // Check to see if a custom post type or taxonomy exists with same name as $field_name
                    // In the case of the choirplanner search form, this will be relevant for post types such as "Publisher" and taxonomies such as "Voicing"
                    if ( post_type_exists( $arr_field ) || taxonomy_exists( $arr_field ) ) {
                        $field_cptt_name = $arr_field;
                        //$field_info .= "field_cptt_name: $field_cptt_name same as arr_field: $arr_field<br />"; // tft
                    } else {
                        $field_cptt_name = null;
                    }

                    //
                    $field_info .= "field_name: $field_name<br />"; // tft
                    if ( $alt_field_name ) { $field_info .= "alt_field_name: $alt_field_name<br />"; }                    
                    $field_info .= "query_assignment: $query_assignment<br />";

                    // Check to see if a value was submitted for this field
                    if ( isset($_GET[$field_name]) ) { // if ( isset($_REQUEST[$field_name]) ) {
                        
                        $field_value = $_GET[$field_name]; // $field_value = $_REQUEST[$field_name];
                        
                        // If field value is not empty...
                        if ( !empty($field_value) && $field_name != 'search_operator' && $field_name != 'devmode' ) {
                            //$search_values = true; // actual non-empty search values have been found in the _GET/_REQUEST array
                            // instead of boolean, create a search_values array? and track which post_type they relate to?
                            $search_values[] = array( 'field_post_type' => $field_post_type, 'arr_field' => $arr_field, 'field_name' => $field_name, 'field_value' => $field_value );
                            //$field_info .= "field value: $field_value<br />"; 
                            //$ts_info .= "query_assignment for field_name $field_name is *$query_assignment* >> search value: '$field_value'<br />";
                            
                            if ( $query_assignment == "primary" ) {
                                $search_primary_post_type = true;
                                $ts_info .= ">> Setting search_primary_post_type var to TRUE based on field $field_name searching value $field_value<br />";
                            } else {
                                $search_related_post_type = true;
                                $ts_info .= ">> Setting search_related_post_type var to TRUE based on field $field_name searching value $field_value<br />";
                            }
                            
                        }
                        
                        $field_info .= "field value: $field_value<br />";
                        
                    } else {
                        //$field_info .= "field value: [none]<br />";
                        $field_value = null;
                    }

                    
                    // Get 'type' field option
                    $field_type = $field['type'];
                    $field_info .= "field_type: $field_type<br />"; // tft
                    
                    if ( !empty($field_value) ) {
                        if ( function_exists('sdg_sanitize')) { $field_value = sdg_sanitize($field_value); }
                    }
                    
                    //$field_info .= "field_name: $field_name<br />";                    
                    //$field_info .= "value: $field_value<br />";
                    
                    if ( $field_type !== "text" && $field_type !== "taxonomy" ) {
                        //$field_info .= "field: <pre>".print_r($field,true)."</pre>"; // tft
                        //$field_info .= "field key: ".$field['key']."<br />";
                        //$field_info .= "field display_format: ".$field['display_format']."<br />";
                    }                    
                    
                    //if ( ( $field_name == "post_title" || $field_name == "title_clean" ) && !empty($field_value) ) {
                    
                    if ( $field_name == "post_title" && !empty($field_value) ) {
                        
                        //$wp_args['s'] = $field_value;
                        $wp_args['_search_title'] = $field_value; // custom parameter -- see posts_where filter fcn

                    } else if ( $field_type == "text" && !empty($field_value) ) { 
                        
                        // TODO: figure out how to determine whether to match exact or not for particular fields
                        // -- e.g. box_num should be exact, but not necessarily for title_clean?
                        // For now, set it explicitly per field_name
                        /*if ( $field_name == "box_num" ) {
                            $match_value = '"' . $field_value . '"'; // matches exactly "123", not just 123. This prevents a match for "1234"
                        } else {
                            $match_value = $field_value;
                        }*/
                        $match_value = $field_value;
                        //$mq_components[] =  array(
                        $query_component = array(
                            'key'   => $field_name,
                            'value' => $match_value,
                            'compare'=> 'LIKE'
                        );
                        
                        // Add query component to the appropriate components array
                        if ( $query_assignment == "primary" ) {
                            $mq_components_primary[] = $query_component;
                        } else {
                            $mq_components_related[] = $query_component;
                        }
                        
                        $field_info .= ">> Added $query_assignment meta_query_component for key: $field_name, value: $match_value<br/>";

                    } else if ( $field_type == "select" && !empty($field_value) ) { 
                        
                        // If field allows multiple values, then values will return as array and we must use LIKE comparison
                        if ( $field['multiple'] == 1 ) {
                            $compare = 'LIKE';
                        } else {
                            $compare = '=';
                        }
                        
                        $match_value = $field_value;
                        $query_component = array(
                            'key'   => $field_name,
                            'value' => $match_value,
                            'compare'=> $compare
                        );
                        
                        // Add query component to the appropriate components array
                        if ( $query_assignment == "primary" ) {
                            $mq_components_primary[] = $query_component;
                        } else {
                            $mq_components_related[] = $query_component;
                        }                        
                        
                        $field_info .= ">> Added $query_assignment meta_query_component for key: $field_name, value: $match_value<br/>";

                    } else if ( $field_type == "relationship" ) { // && !empty($field_value) 

                        $field_post_type = $field['post_type'];                        
                        // Check to see if more than one element in array. If not, use $field['post_type'][0]...
                        if ( count($field_post_type) == 1) {
                            $field_post_type = $field['post_type'][0];
                        } else {
                            // ???
                        }
                        
                        $field_info .= "field_post_type: ".print_r($field_post_type,true)."<br />";
                        
                        if ( !empty($field_value) ) {
                            
                            $field_value_converted = ""; // init var for storing ids of posts matching field_value
                            
                            // If $options,
                            if ( !empty($options) ) {
                                
                                if ( $arr_field == "publisher" ) {
                                    $key = $arr_field; // can't use field_name because of redirect issue
                                } else {
                                    $key = $field_name;
                                }
                                $query_component = array(
                                    'key'   => $key, 
                                    //'value' => $match_value,
                                    // TODO: FIX -- value as follows doesn't work w/ liturgical dates because it's trying to match string, not id... need to get id!
                                    'value' => '"' . $field_value . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                                    'compare'=> 'LIKE', 
                                );

                                // Add query component to the appropriate components array
                                if ( $query_assignment == "primary" ) {
                                    $mq_components_primary[] = $query_component;
                                } else {
                                    $mq_components_related[] = $query_component;
                                }
                                
                                if ( $alt_field_name ) {
                                    
                                    $meta_query['relation'] = 'OR';
                                    
                                    $query_component = array(
                                        'key'   => $alt_field_name,
                                        //'value' => $field_value,
                                        // TODO: FIX -- value as follows doesn't work w/ liturgical dates because it's trying to match string, not id... need to get id!
                                        'value' => '"' . $field_value . '"',
                                        'compare'=> 'LIKE'
                                    );
                                    
                                    // Add query component to the appropriate components array
                                    if ( $query_assignment == "primary" ) {
                                        $mq_components_primary[] = $query_component;
                                    } else {
                                        $mq_components_related[] = $query_component;
                                    }
                                    
                                }
                                
                            } else {
                                
                                // If no $options, match search terms
                                $field_info .= "options array is empty.<br />";
                                
                                // Get id(s) of any matching $field_post_type records with post_title like $field_value
                                $field_value_args = array('post_type' => $field_post_type, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', '_search_title' => $field_value, 'suppress_filters' => FALSE );
                                $field_value_posts = get_posts( $field_value_args );
                                if ( count($field_value_posts) > 0 ) {

                                    $field_info .= count($field_value_posts)." field_value_posts found<br />";
                                    //$field_info .= "field_value_args: <pre>".print_r($field_value_args, true)."</pre><br />";

                                    // The problem here is that, because ACF stores multiple values as a single meta_value array, 
                                    // ... it's not possible to search efficiently for an array of values
                                    // TODO: figure out if there's some way to for ACF to store the meta_values in separate rows?
                                    
                                    $sub_query = array();
                                    
                                    if ( count($field_value_posts) > 1 ) {
                                        $sub_query['relation'] = 'OR';
                                    }
                                    
                                    // TODO: make this a subquery to better control relation
                                    foreach ( $field_value_posts as $fvp_id ) {
                                        $sub_query[] = [
                                            'key'   => $arr_field, // can't use field_name because of "publisher" issue
                                            //'key'   => $field_name,
                                            'value' => '"' . $fvp_id . '"',
                                            'compare' => 'LIKE',
                                        ];
                                    }
                                    
                                    // Add query component to the appropriate components array
                                    if ( $query_assignment == "primary" ) {
                                        $mq_components_primary[] = $sub_query;
                                    } else {
                                        $mq_components_related[] = $sub_query;
                                    }
                                    //$mq_components_primary[] = $sub_query;
                                }
                                
                            }
                            
                            //$field_info .= ">> WIP: set meta_query component for: $field_name = $field_value<br/>";
                            $field_info .= "Added meta_query_component for key: $field_name, value: $field_value<br/>";
                            
                        }
                        
                        // For text fields, may need to get ID matching value -- e.g. person id for name mousezart (220824), if composer field were not set up as combobox -- maybe faster?
                        
                                                
                        /* ACF
                        create_field( $field_name ); // new ACF fcn to generate HTML for field 
                        // see https://www.advancedcustomfields.com/resources/creating-wp-archive-custom-field-filter/ and https://www.advancedcustomfields.com/resources/upgrade-guide-version-4/
                        
                        
                        // Old ACF -- see ca. 08:25 in video tutorial:
                        $field_obj = get_field_object($field_name);
                        foreach ( $field_obj['choices'] as $choice_value => $choice_label ) {
                            // checkbox code or whatever
                        }                        
                        */

                    } else if ( $field_type == "taxonomy" && !empty($field_value) ) {

                        $query_component = array (
                            'taxonomy' => $field_name,
                            //'field'    => 'slug',
                            'terms'    => $field_value,
                        );
                        
                        // Add query component to the appropriate components array
                        if ( $query_assignment == "primary" ) {
                            $tq_components_primary[] = $query_component;
                        } else {
                            $tq_components_related[] = $query_component;
                        }

                        if ( $post_type == "repertoire" ) {

                            // Since rep & editions share numerous taxonomies in common, check both
                            
                            $related_field_name = 'repertoire_editions'; //$related_field_name = 'related_editions';
                            
                            $field_info .= ">> WIP: field_type: taxonomy; field_name: $field_name; post_type: $post_type; terms: $field_value<br />"; // tft
                            
                            // Add a tax query somehow to search for related_post_type posts with matching taxonomy value                            
                            // Create a secondary query for related_post_type?
                            // PROBLEM WIP -- tax_query doesn't seem to work with two post_types if tax only applies to one of them?
                            
                            /*
                            $tq_components_primary[] = array(
                                'taxonomy' => $field_name,
                                //'field'    => 'slug',
                                'terms'    => $field_value,
                            );
                            
                            // Add query component to the appropriate components array
                            if ( $query_assignment == "primary" ) {
                                $tq_components_primary[] = $query_component;
                            } else {
                                $tq_components_related[] = $query_component;
                            }
                            */

                        }

                    }
                    
                    //$field_info .= "-----<br />";
                    
                } // END if ( $field )
                
                
                // Set up the form fields
                // ----------------------
                if ( $form_type == "advanced_search" ) {
                   
                    //$field_info .= "CONFIRM field_type: $field_type<br />"; // tft
                    
                    $input_class = "advanced_search";
                    $input_html = "";
                    $options = array();
                    
                    if ( in_array($field_name, $taxonomies) ) {
                        $input_class .= " primary_post_type";
                        $field_label .= "*";
                    }                    
                    
                    $info .= '<label for="'.$field_name.'" class="'.$input_class.'">'.$field_label.':</label>';
                    
                    if ( $field_type == "text" ) {
                        
                        $input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="'.$input_class.'" />';                                            
                    
                    } else if ( $field_type == "select" ) {
                        
                        if ( isset($field['choices']) ) {
                            $options = $field['choices'];
                            //$field_info .= "field: <pre>".print_r($field, true)."</pre>";
                            //$field_info .= "field choices: <pre>".print_r($field['choices'],true)."</pre>"; // tft
                        } else {
                            $options = null; // init
                            $field_info .= "No field choices found. About to go looking for values to set as options...<br />";
                            $field_info .= "field: <pre>".print_r($field, true)."</pre>";
                        }
                        
                    } else if ( $field_type == "relationship" ) {
                        
                        if ( $field_cptt_name ) { $field_info .= "field_cptt_name: $field_cptt_name<br />"; } // tft 
                        if ( $arr_field ) { $field_info .= "arr_field: $arr_field<br />"; } // tft 
                        
                        // repertoire_litdates
                        // related_liturgical_dates
                        
                        if ( $field_cptt_name != $arr_field ) {
                        //if ( $field_cptt_name != $field_name ) {
                            
                            $field_info .= "field_cptt_name NE arr_field<br />"; // tft
                            //$field_info .= "field_cptt_name NE field_name<br />"; // tft
                            
                            // TODO: 
                            if ( $field_post_type && $field_post_type != "person" && $field_post_type != "publisher" ) { // TMP disable options for person fields so as to allow for free autocomplete
                                
                                // TODO: consider when to present options as combo box and when to go for autocomplete text
                                // For instance, what if the user can't remember which Bach wrote a piece? Should be able to search for all...
                                
                                // e.g. field_post_type = person, field_name = composer 
                                // ==> find all people in Composers person_category -- PROBLEM: people might not be correctly categorized -- this depends on good data entry
                                // -- alt: get list of composers who are represented in the music library -- get unique meta_values for meta_key="composer"

                                // TODO: figure out how to filter for only composers related to editions? or lit dates related to rep... &c.
                                // TODO: find a way to do this more efficiently, perhaps with a direct wpdb query to get all unique meta_values for relevant keys
                                
                                //
                                // set up WP_query
                                $options_args = array(
                                    'post_type' => $post_type, //'post_type' => $field_post_type,
                                    'post_status' => 'publish',
                                    'fields' => 'ids',
                                    'posts_per_page' => -1, // get them all
                                    'meta_query' => array(
                                        'relation' => 'OR',
                                        array(
                                            'key'     => $field_name,
                                            'compare' => 'EXISTS'
                                        ),
                                        array(
                                            'key'     => $alt_field_name,
                                            'compare' => 'EXISTS'
                                        ),
                                    ),
                                );
                                
                                $options_arr_posts = new WP_Query( $options_args );
                                $options_posts = $options_arr_posts->posts;

                                //$field_info .= "options_args: <pre>".print_r($options_args,true)."</pre>"; // tft
                                $field_info .= count($options_posts)." options_posts found <br />"; // tft
                                //$field_info .= "options_posts: <pre>".print_r($options_posts,true)."</pre>"; // tft

                                $arr_ids = array(); // init

                                foreach ( $options_posts as $options_post_id ) {

                                    // see also get composer_ids
                                    $meta_values = get_field($field_name, $options_post_id, false);
                                    $alt_meta_values = get_field($alt_field_name, $options_post_id, false);
                                    if ( !empty($meta_values) ) {
                                        //$field_info .= count($meta_values)." meta_value(s) found for field_name: $field_name and post_id: $options_post_id.<br />";
                                        foreach ($meta_values AS $meta_value) {
                                            $arr_ids[] = $meta_value;
                                        }
                                    }
                                    if ( !empty($alt_meta_values) ) {
                                        //$field_info .= count($alt_meta_values)." meta_value(s) found for alt_field_name: $alt_field_name and post_id: $options_post_id.<br />";
                                        foreach ($alt_meta_values AS $meta_value) {
                                            $arr_ids[] = $meta_value;
                                        }
                                    }

                                }

                                $arr_ids = array_unique($arr_ids);

                                // Build the options array from the ids
                                foreach ( $arr_ids as $id ) {
                                    if ( $field_post_type == "person" ) {
                                        $last_name = get_post_meta( $id, 'last_name', true );
                                        $first_name = get_post_meta( $id, 'first_name', true );
                                        $middle_name = get_post_meta( $id, 'middle_name', true );
                                        //
                                        $option_name = $last_name;
                                        if ( !empty($first_name) ) {
                                            $option_name .= ", ".$first_name;
                                        }
                                        if ( !empty($middle_name) ) {
                                            $option_name .= " ".$middle_name;
                                        }
                                        //$option_name = $last_name.", ".$first_name;
                                        $options[$id] = $option_name;
                                        // TODO: deal w/ possibility that last_name, first_name fields are empty
                                    } else if ( function_exists( 'sdg_post_title' ) ) {
										$title_args = array( 'post' => $post_id, 'line_breaks' => true, 'show_subtitle' => true, 'echo' => false, 'hlevel' => 0, 'hlevel_sub' => 0 );
										$options[$id] = sdg_post_title( $title_args );
									} else {
										$options[$id] = get_the_title($id);
									}
                                }

                            }

                            asort($options);

                        } else {
                        	
                        	$input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="'.$input_class.'" />';                                            
                    
                    		//$input_html = "LE TSET"; // tft
                        	//$input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="autocomplete '.$input_class.' relationship" />';
                        }
                        
                    } else if ( $field_type == "taxonomy" ) {
                        
                        // Get options, i.e. taxonomy terms
                        $obj_options = get_terms ( $field_name );
                        //$info .= "options for taxonomy $field_name: <pre>".print_r($options, true)."</pre>"; // tft
                        
                        // Convert objects into array for use in building select menu
                        foreach ( $obj_options as $obj_option ) { // $option_value => $option_name
                            
                            $option_value = $obj_option->term_id;
                            $option_name = $obj_option->name;
                            //$option_slug = $obj_option->slug;
                            $options[$option_value] = $option_name;
                        }
                        
                    } else {
                        
                        $field_info .= "field_type could not be determined.";
                    }
                    
                    if ( !empty($options) ) { // WIP // && strpos($input_class, "combobox")

                        //if ( !empty($field_value) ) { $ts_info .= "options: <pre>".print_r($options, true)."</pre>"; } // tft

                        $input_class .= " combobox"; // tft
                                                
                        $input_html = '<select name="'.$field_name.'" id="'.$field_name.'" class="'.$input_class.'">'; 
                        $input_html .= '<option value>-- Select One --</option>'; // default empty value // class="'.$input_class.'"
                        
                        // Loop through the options to build the select menu
                        foreach ( $options as $option_value => $option_name ) {
                            $input_html .= '<option value="'.$option_value.'"';
                            if ( $option_value == $field_value ) { $input_html .= ' selected="selected"'; }
                            //if ( $option_name == "Men-s Voices" ) { $option_name = "Men's Voices"; }
                            $input_html .= '>'.$option_name.'</option>'; //  class="'.$input_class.'"
                        }
                        $input_html .= '</select>';

                    } else if ( $options && strpos($input_class, "multiselect") !== false ) {
                        // TODO: implement multiple select w/ remote source option in addition to combobox (which is for single-select inputs) -- see choirplanner.js WIP
                    } else if ( empty($input_html) ) {
                        $input_html = '<input type="text" id="'.$field_name.'" name="'.$field_name.'" value="'.$field_value.'" class="autocomplete '.$input_class.'" />'; // tft
                    }
                    
                    $info .= $input_html;
                    
                } else {
                    $input_class = "simple_search";
                    $info .= '<input type="text" id="'.$field_name.'" name="'.$field_name.'" placeholder="'.$placeholder.'" value="'.$field_value.'" class="'.$input_class.'" />';
                }
                
                if ( $form_type == "advanced_search" ) {
                    $info .= '<br />';
                    /*$info .= '<div class="devview">';
                    $info .= '<span class="troubleshooting smaller">'.$field_info.'</span>\n'; // tft
                    $info .= '</div>';*/
                    //$info .= '<!-- '."\n".$field_info."\n".' -->';
                }
                
                //$ts_info .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />";
                //if ( strpos($field_name, "publisher") || strpos($field_name, "devmode") || strpos($arr_field, "devmode") || $field_name == "devmode" ) {
                if ( (!empty($field_value) && $field_name != 'search_operator' && $field_name != 'devmode' ) ||
                   ( !empty($options_posts) && count($options_posts) > 0 ) ||
                   strpos($field_name, "liturgical") ) {
                    $ts_info .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />";
                }
                //$field_name == "liturgical_date" || $field_name == "repertoire_litdates" || 
                //if ( !empty($field_value) ) { $ts_info .= "+++++<br />FIELD INFO<br/>+++++<br />".$field_info."<br />"; }
                
            } // End conditional for actual search fields
            
        } // end foreach ( $arr_fields as $field_name )
        
        $info .= '<input type="submit" value="Search Library">';
        $info .= '<a href="#!" id="form_reset">Clear Form</a>';
        $info .= '</form>';        
        
        // 
        $args_related = null; // init
        $mq_components = array();
        $tq_components = array();
        //$ts_info .= "mq_components_primary: <pre>".print_r($mq_components_primary,true)."</pre>"; // tft
        //$ts_info .= "tq_components_primary: <pre>".print_r($tq_components_primary,true)."</pre>"; // tft
        //$ts_info .= "mq_components_related: <pre>".print_r($mq_components_related,true)."</pre>"; // tft
        //$ts_info .= "tq_components_related: <pre>".print_r($tq_components_related,true)."</pre>"; // tft
        
        // If field values were found related to both post types,
        // AND if we're searching for posts that match ALL terms (search_operator: "and"),
        // then set up a second set of args/birdhive_get_posts
        if ( $search_primary_post_type == true && $search_related_post_type == true && $search_operator == "and" ) { 
            $ts_info .= "Querying both primary and related post_types (two sets of args)<br />";
            $args_related = $wp_args;
            $args_related['post_type'] = $related_post_type; // reset post_type            
        } else if ( $search_primary_post_type == true && $search_related_post_type == true && $search_operator == "or" ) { 
            // WIP -- in this case
            $ts_info .= "Querying both primary and related post_types (two sets of args) but with OR operator... WIP<br />";
            //$args_related = $wp_args;
            //$args_related['post_type'] = $related_post_type; // reset post_type            
        } else {
            if ( $search_primary_post_type == true ) {
                // Searching primary post_type only
                $ts_info .= "Searching primary post_type only<br />";
                $wp_args['post_type'] = $post_type;
                $mq_components = $mq_components_primary;
                $tq_components = $tq_components_primary;
            } else if ( $search_related_post_type == true ) {
                // Searching related post_type only
                $ts_info .= "Searching related post_type only<br />";
                $wp_args['post_type'] = $related_post_type;
                $mq_components = $mq_components_related;
                $tq_components = $tq_components_related;
            }
        }
        
        // Finalize meta_query or queries
        // ==============================
        /* 
        WIP if meta_key = title_clean and related_post_type is true then incorporate also, using title_clean meta_value:
        $wp_args['_search_title'] = $field_value; // custom parameter -- see posts_where filter fcn
        */
        
        if ( empty($args_related) ) {
            
            if ( count($mq_components) > 1 && empty($meta_query['relation']) ) {
                $meta_query['relation'] = $search_operator;
            }
            if ( count($mq_components) == 1) {
                //$ts_info .= "Single mq_component.<br />";
                $meta_query = $mq_components; //$meta_query = $mq_components[0];
            } else {
                foreach ( $mq_components AS $component ) {
                    $meta_query[] = $component;
                }
            }
            
            if ( !empty($meta_query) ) { $wp_args['meta_query'] = $meta_query; }
            
        } else {
            
            // TODO: eliminate redundancy!
            if ( count($mq_components_primary) > 1 && empty($meta_query['relation']) ) {
                $meta_query['relation'] = $search_operator;
            }
            if ( count($mq_components_primary) == 1) {                
                $meta_query = $mq_components_primary; //$meta_query = $mq_components_primary[0];
            } else {
                foreach ( $mq_components_primary AS $component ) {
                    $meta_query[] = $component;
                }
            }
            /*foreach ( $mq_components_primary AS $component ) {
                $meta_query[] = $component;
            }*/
            if ( !empty($meta_query) ) { $wp_args['meta_query'] = $meta_query; }
            
            // related query
            if ( count($mq_components_related) > 1 && empty($meta_query_related['relation']) ) {
                $meta_query_related['relation'] = $search_operator;
            }
            if ( count($mq_components_related) == 1) {
                $meta_query_related = $mq_components_related; //$meta_query_related = $mq_components_related[0];
            } else {
                foreach ( $mq_components_related AS $component ) {
                    $meta_query_related[] = $component;
                }
            }
            /*foreach ( $mq_components_related AS $component ) {
                $meta_query_related[] = $component;
            }*/
            if ( !empty($meta_query_related) ) { $args_related['meta_query'] = $meta_query_related; }
            
        }
        
        
        // Finalize tax_query or queries
        // =============================
        if ( empty($args_related) ) {
            
            if ( count($tq_components) > 1 && empty($tax_query['relation']) ) {
                $tax_query['relation'] = $search_operator;
            }
            foreach ( $tq_components AS $component ) {
                $tax_query[] = $component;
            }
            if ( !empty($tax_query) ) { $wp_args['tax_query'] = $tax_query; }
            
        } else {
            
            // TODO: eliminate redundancy!
            if ( count($tq_components_primary) > 1 && empty($tax_query['relation']) ) {
                $tax_query['relation'] = $search_operator;
            }
            foreach ( $tq_components_primary AS $component ) {
                $tax_query[] = $component;
            }
            if ( !empty($tax_query) ) { $wp_args['tax_query'] = $tax_query; }
            
            // related query
            if ( count($tq_components_related) > 1 && empty($tax_query_related['relation']) ) {
                $tax_query_related['relation'] = $search_operator;
            }
            foreach ( $tq_components_related AS $component ) {
                $tax_query_related[] = $component;
            }
            if ( !empty($tax_query_related) ) { $args_related['tax_query'] = $tax_query_related; }
            
        }

        ///// WIP
        if ( $related_post_type ) {
            
            // If we're dealing with multiple post types, then the and/or is extra-complicated, because not all taxonomies apply to all post_types
            // Must be able to find, e.g., repertoire with composer: Mousezart as well as ("OR") all editions/rep with instrument: Bells
            
            if ( $search_operator == "or" ) {
                if ( !empty($tax_query) && !empty($meta_query) ) {
                    $wp_args['_meta_or_tax'] = true; // custom parameter -- see posts_where filters
                }
            }
        }
        /////
        
        // If search values have been submitted, then run the search query
        if ( count($search_values) > 0 ) {
            
            $ts_info .= "About to pass wp_args to birdhive_get_posts: <pre>".print_r($wp_args,true)."</pre>"; // tft
            
            // Get posts matching the assembled args
            /* ===================================== */
            if ( $form_type == "advanced_search" ) {
                //$ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $posts_info = array(); // tft
                $posts_info = birdhive_get_posts( $wp_args );
            } else {
                $posts_info = birdhive_get_posts( $wp_args );
            }
            
            if ( isset($posts_info['arr_posts']) ) {
                
                $arr_post_ids = $posts_info['arr_posts']->posts; // Retrieves an array of IDs (based on return_fields: 'ids')
                $ts_info .= "Num arr_post_ids: [".count($arr_post_ids)."]<br />";
                //$ts_info .= "arr_post_ids: <pre>".print_r($arr_post_ids,true)."</pre>"; // tft
                
                //$info .= '<div class="troubleshooting">'.$posts_info['ts_info'].'</div>';
                $ts_info .= $posts_info['ts_info'];
                
                // Print last SQL query string
                global $wpdb;
                $ts_info .= "last_query:<pre>".$wpdb->last_query."</pre>";
                //$info .= '<div class="troubleshooting">'."last_query:<pre>".$wpdb->last_query."</pre>".'</div>';
                
            }
            
            if ( $args_related ) {
                
                $ts_info .= "About to pass args_related to birdhive_get_posts: <pre>".print_r($args_related,true)."</pre>"; // tft
                
                $ts_info .= "<strong>NB: search temporarily disabled for troubleshooting.</strong><br />"; $related_posts_info = array(); // tft
                //$related_posts_info = birdhive_get_posts( $args_related );
                
                if ( isset($related_posts_info['arr_posts']) ) {
                
                    $arr_related_post_ids = $related_posts_info['arr_posts']->posts;
                    $ts_info .= "Num arr_related_post_ids: [".count($arr_related_post_ids)."]<br />";
                    //$ts_info .= "arr_related_post_ids: <pre>".print_r($arr_related_post_ids,true)."</pre>"; // tft

                    $ts_info .= $related_posts_info['ts_info'];

                    // Print last SQL query string
                    global $wpdb;
                    $ts_info .= "last_query: <pre>".$wpdb->last_query."</pre>"; // tft
                    
                    // WIP -- we're running an "and" so we need to find the OVERLAP between the two sets of ids... one set of repertoire ids, one of editions... hmm...
                    if ( !empty($arr_post_ids) ) {
                        
                        $related_post_field_name = "repertoire_editions"; // TODO: generalize!
                        
                        $full_match_ids = array(); // init
                        
                        // Search through the smaller of the two data sets and find posts that overlap both sets; return only those
                        // TODO: eliminate redundancy
                        if ( count($arr_post_ids) > count($arr_related_post_ids) ) {
                            // more rep than edition records
                            $ts_info .= "more rep than edition records >> loop through arr_related_post_ids<br />";
                            foreach ( $arr_related_post_ids as $tmp_id ) {
                                $ts_info .= "tmp_id: $tmp_id<br />";
                                $tmp_posts = get_field($related_post_field_name, $tmp_id); // repertoire_editions
                                if ( empty($tmp_posts) ) { $tmp_posts = get_field('musical_work', $tmp_id); } // WIP/tmp
                                if ( $tmp_posts ) {
                                    foreach ( $tmp_posts as $tmp_match ) {
                                        // Get the ID
                                        if ( is_object($tmp_match) ) {
                                            $tmp_match_id = $tmp_match->ID;
                                        } else {
                                            $tmp_match_id = $tmp_match;
                                        }
                                        // Look
                                        if ( in_array($tmp_match_id, $arr_post_ids) ) {
                                            // it's a full match -- keep it
                                            $full_match_ids[] = $tmp_match_id;
                                            $ts_info .= "$related_post_field_name tmp_match_id: $tmp_match_id -- FOUND in arr_post_ids<br />";
                                        } else {
                                            $ts_info .= "$related_post_field_name tmp_match_id: $tmp_match_id -- NOT found in arr_post_ids<br />";
                                        }
                                    }
                                } else {
                                    $ts_info .= "No $related_post_field_name records found matching related_post_id $tmp_id<br />";
                                }
                            }
                        } else {
                            // more editions than rep records
                            $ts_info .= "more editions than rep records >> loop through arr_post_ids<br />";
                            foreach ( $arr_post_ids as $tmp_id ) {
                                $tmp_posts = get_field($related_post_field_name, $tmp_id); // repertoire_editions
                                if ( empty($tmp_posts) ) { $tmp_posts = get_field('related_editions', $tmp_id); } // WIP/tmp
                                if ( $tmp_posts ) {
                                    foreach ( $tmp_posts as $tmp_match ) {
                                        // Get the ID
                                        if ( is_object($tmp_match) ) {
                                            $tmp_match_id = $tmp_match->ID;
                                        } else {
                                            $tmp_match_id = $tmp_match;
                                        }
                                        // Look for a match in arr_post_ids
                                        if ( in_array($tmp_match_id, $arr_related_post_ids) ) {
                                            // it's a full match -- keep it
                                            $full_match_ids[] = $tmp_match_id;
                                        } else {
                                            $ts_info .= "$related_post_field_name tmp_match_id: $tmp_match_id -- NOT in arr_related_post_ids<br />";
                                        }
                                    }
                                }
                            }
                        }
                        //$arr_post_ids = array_merge($arr_post_ids, $arr_related_post_ids); // Merge $arr_related_posts into arr_post_ids -- nope, too simple
                        $arr_post_ids = $full_match_ids;
                        $ts_info .= "Num full_match_ids: [".count($full_match_ids)."]".'</div>';
                        
                    } else {
                        $arr_post_ids = $arr_related_post_ids;
                    }

                }
            }
            
            // 
            
            if ( !empty($arr_post_ids) ) {
                    
                $ts_info .= "Num matching posts found (raw results): [".count($arr_post_ids)."]"; // tft -- if there are both rep and editions, it will likely be an overcount
                $info .= format_search_results($arr_post_ids);

            } else {
                
                $info .= "No matching items found.<br />";
                
            } // END if ( !empty($arr_post_ids) )            
            
            /*if ( isset($posts_info['arr_posts']) ) {
                
                $arr_posts = $posts_info['arr_posts'];//$posts_info['arr_posts']->posts; // Retrieves an array of WP_Post Objects
                
                $ts_info .= $posts_info['ts_info']."<hr />";
                
                if ( !empty($arr_posts) ) {
                    
                    $ts_info .= "Num matching posts found (raw results): [".count($arr_posts->posts)."]"; 
                    //$info .= '<div class="troubleshooting">'."Num matching posts found (raw results): [".count($arr_posts->posts)."]".'</div>'; // tft -- if there are both rep and editions, it will likely be an overcount
               
                    if ( count($arr_posts->posts) == 0 ) { // || $form_type == "advanced_search"
                        //$ts_info .= "args: <pre>".print_r($args,true)."</pre>"; // tft
                    }
                    
                    // Print last SQL query string
                    global $wpdb;
                    $ts_info .= "<p>last_query:</p><pre>".$wpdb->last_query."</pre>"; // tft

                    $info .= format_search_results($arr_posts);
                    
                } // END if ( !empty($arr_posts) )
                
            } else {
                $ts_info .= "No arr_posts retrieved.<br />";
            }*/
            
        } else {
            
            $ts_info .= "No search values submitted.<br />";
            
        }        
        
    } // END if ( $args['fields'] )
    
    if ( $ts_info != "" && ( $do_ts === true || $do_ts == "dcp" ) ) { $info .= '<div class="troubleshooting">'.$ts_info.'</div>'; }
    
    return $info;
    
} // END fcn birdhive_search_form

?>