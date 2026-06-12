<?php
$logCtx = ['whx4', 'display', 'collections'];

add_shortcode('content_collection', 'birdhive_content_collection');
function birdhive_content_collection ( $atts = array() )
{
    global $wpdb;
    $info = "";

    $args = shortcode_atts( array(
        'id' => null,
    ), $atts );

    // Extract
    extract( $args );

    $info .= birdhive_display_collection( array('collection_id' => $id ) );

    return $info;
}

// Display a collection of post items
function birdhive_display_collection ( $args = array() )
{
    // Init vars
    $info = "";

    // Defaults
    $defaults = array(
        'collection_id'        => null,
        'link_posts'        => true,
        'show_subtitles'    => null,
        'show_content'        => null,
        'show_images'        => null,
        'table_fields'        => array(),
        'table_headers'        => array(),
        'table_totals'        => array(), // field names
        'num_cols'            => "3",
        'aspect_ratio'        => "square",
        'custom_class'        => null,
    );

    // Parse & Extract args
    $args = wp_parse_args( $args, $defaults );
    extract( $args );
    
    // One possible args is display_atts
    if ( isset($display_atts) && is_array($display_atts) ) { extract( $display_atts ); } else { $display_atts = []; }

    // Get args from array
    if ( isset($collection_id) ) {
        $content_type = "mixed";
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
        // No collection_id set
        if ( $display_format == "table" && isset($display_atts['fields']) ) {
            $table_fields = $display_atts['fields'];
            $table_headers = $display_atts['headers'];
            $table_totals = $display_atts['totals'];
        }
        if ( $display_format == "grid" && isset($display_atts['cols']) ) {
            $num_cols = $display_atts['cols'];
        } else {
            // Get num_cols from default: ".$num_cols." for display_format: ".$display_format
        }

        if ( isset($display_atts['aspect_ratio']) ) { $aspect_ratio = $display_atts['aspect_ratio']; } 
        // TODO: either eliminate this, or make it so that aspect_ratio actually ever is passable as an arg, via mods to args array of display_posts, for example...
    }

    // Show TS info based on display_format (tft)
    if ( $display_format == "table" ) {
        //"display_format: $display_format<br />";
        //"table_fields: ".print_r($table_fields, true)."<br />";
        //"table_headers: ".print_r($table_headers, true)."<br />";
        //"table_totals: ".print_r($table_totals, true)."<br />";
    }
    $col_totals = array();

    // List/table/grid header or container
    //$info .= collection_header ( $display_format, $num_cols, $aspect_ratio, $table_fields, $table_headers );
    //$header_args = array( 'display_format' => $display_format, 'num_cols' => $num_cols, 'aspect_ratio' => $aspect_ratio, 'table_fields' => $table_fields, 'table_headers' => $table_headers );
    $header_args = array( 'display_format' => $display_format, 'num_cols' => $num_cols, 'aspect_ratio' => $aspect_ratio, 'fields' => $table_fields, 'headers' => $table_headers );
    $info .= collection_header ( $header_args );

    if ( $display_format == "table" ) {
        $info .= '<tbody>';
    }
    //$info .= "+~+~+~+~+~+~+ collection items +~+~+~+~+~+~+<br />";

    // For each item, get content for display in appropriate form...
    foreach ( $items as $item ) {
        $item_info = "";
        $arr_item = array();
        $image_id = null;

        if ( is_array($item) && isset($item['item_type']) ) {
            $item_type = $item['item_type'];
        } else if ( $content_type == "posts" ) {
            $item_type = "post";
        } else if ( $content_type == "events" ) {
            $item_type = "event";
        } else {
            $item_type = "UNKNOWN!";
        }
        
        if ( $item_type == "event" && ( $display_format == "excerpts" || ( $display_format == "archive" && $show_content != 'full' ) ) ) {
            $item_info .= display_event_list_item( $item );
        } else {
            // Assemble the array of styling parameters
            $arr_styling = array( 'item_type' => $item_type, 'display_format' => $display_format, 'link_posts' => $link_posts, 'show_subtitle' => $show_subtitles, 'show_content' => $show_content, 'show_image' => $show_images, 'aspect_ratio' => $aspect_ratio, 'table_fields' => $table_fields, 'collection_id' => $collection_id ); // wip

            // Assemble the arr_item
            if ( $item_type == "custom_item" ) {
                $arr_item = $item;
            } else {
                $arr_item = build_item_arr ( $item, $arr_styling );
            }

            // Get content for display in appropriate form...
            //$item_args = array( 'content_type' => $content_type, 'display_format' => $display_format, 'item' => $item );
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
                if ( isset( $arr_item['field_values'][$field_name] ) ) { // isset( $arr_item[$field_name] ) ||
                    if ( isset($col_totals[$field_name]) ) {
                        $col_totals[$field_name] += (float) $arr_item['field_values'][$field_name];
                    } else {
                        $col_totals[$field_name] = (float) $arr_item['field_values'][$field_name];
                    }
                }
            }
        }

        // Add the item_info to the info for return/display
        $info .= $item_info;

    } // END foreach items as item

    if ( $display_format == "table" ) {
        $info .= '</tbody>';
    }

    // Display column totals, if applicable
    if ( $display_format == "table" && !empty($col_totals) ) {
        // WIP
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

    // Return info for display
    return $info;
}

// TODO: add options for collection_SUBheaders... e.g. for group/subgroups/personnel; links displayed grouped by link categories; etc.
function collection_header ( $args = array() )
{
    $logCtx = ['whx4', 'display', 'collections'];

    // Init vars
    $info = "";

    // Defaults
    $defaults = array(
        'display_format'    => null,
        'num_cols'            => true,
        'aspect_ratio'        => null,
        'fields'            => null,
        'headers'            => null,
        'custom_class'        => null,
    );

    // Parse & Extract args
    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    if ( $display_format == "list" ) {
        $info .= '<ul>';
    } else if ( $display_format == "links" ) {
        //$info .= '';
    } else if ( $display_format == "excerpts" || $display_format == "archive" ) {
        $info .= '<div class="posts_archive">';
    } else if ( $display_format == "table" ) {
        $table_classes = "posts_archive";
        if ( $custom_class ) { $table_classes .= " ".$custom_class; }
        $info .= '<table class="'.$table_classes.'">';

        // Make header row -- from field names(?)
        if ( !empty($fields) ) {
            $info .= '<tr>'; //$info .= "<tr>"; // prep the header row

            // Create array from fields string, as needed
            if ( is_array($fields) ) { $arr_fields = $fields; } else { $arr_fields = explode(",",$fields); }
            //$info .= "<td>".$fields."</td>"; // tft
            //$info .= "<td><pre>".print_r($arr_fields, true)."</pre></td>"; // tft
            $col = 0;
            $th_customization = ""; // wip -- see e.g. bkkp employment income

            if ( !empty($headers) ) {
                // Create array from headers string, as needed
                if ( is_array($headers) ) { $arr_headers = $headers; } else { $arr_headers = explode(",",$headers); }

                foreach ( $arr_headers as $header ) {
                    $header = trim($header);
                    if ( $header == "-" ) { $header = ""; }
                    //$info .= "<th>".$header."</th>";
                    //$info .= '<th class="'.$thclass.'">'.$header."</th>";
                    $info .= '<th'.$th_customization.'>'.$header."</th>";
                    //width="25%
                    $col++;
                }
            } else {
                // If no headers were submitted, make do with the field names
                foreach ( $arr_fields as $field_name ) {
                    $field_name = ucfirst(trim($field_name));
                    $info .= '<th'.$th_customization.'>'.$header."</th>";
                    $col++;
                }
            }
            $info .= "</tr>"; // close out the header row
        }
    } else if ( $display_format == "grid" ) {
        $colclass = sdg_digit_to_word($num_cols)."col";
        $info .= '<div class="flex-container '.$colclass.' '.$aspect_ratio.'">';
    } else {
        $info .= '<!-- display_format '.$display_format.' not matched -->';
    }

    // Return info for display
    return $info;
}

function collection_footer ( $display_format = null )
{
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
