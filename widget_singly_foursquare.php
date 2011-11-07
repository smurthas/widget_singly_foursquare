<?php
/*
Plugin Name:  Widget Singly Foursquare
Plugin URI: https://github.com/smurthas/widget_singly_foursquare
Description: Display your tweets in your sidebar, via your Singly account!
Version: 0.0.1
Author: Simon Murtha-Smith
Author URI: http://smurthas.com
*/

/*
License: GPL
Compatibility: WordPress 2.6 with Widget-plugin.

Installation:
Place the widget_singly_foursquare folder in your /wp-content/plugins/ directory
and activate through the administration panel, and then go to the widget panel and
drag it to where you would like to have it!
*/

/*  Copyright Simon Murtha-Smith

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$API_HOST = 'http://localhost:8042/';
$states_arr  = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'DC'=>"Washington D.C.",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");
$states_abbr = array();
foreach ($states_arr as $abbr => $state) {
    $states_abbr[$state] = $abbr;
}



class widget_singly_histobin {
}

function widget_singly_foursquare_init() {
    
    if ( !function_exists('register_sidebar_widget') )
        return;
    if ( !function_exists('simplexml_load_file') ) {
        echo 'PHP 5.1 or later requires: no simplexml_load_file()';
        return;
    }
    
    function widget_singly_foursquare( $args ) {
        global $API_HOST, $states_abbr;
        extract($args);

        $options = get_option('widget_singly_foursquare');
        $title = $options['widget_singly_foursquare_title'];
        $_api_key = $options['widget_singly_foursquare_option_api_key'];

        $widget_singly_foursquare_option_cached_time = $options['widget_singly_foursquare_option_cached_time'];
        $widget_singly_foursquare_option_cached_output = $options['widget_singly_foursquare_option_cached_output'];

        // section main logic from here
        $twitters = false;
        $cached_time = $options['widget_singly_foursquare_option_cached_time'];
        // if( $cached_time + 300 < time() ) {      // once at 5 min.
            
            $_jsonfilestr = $API_HOST . 'synclets/foursquare/getCurrent/checkin';
            // $_jsonfilestr = $API_HOST . $_api_key . '/synclets/foursquare/getCurrent/checkin';
            // echo $_jsonfilestr;
            $_twitterCount = 3;
            $curl = curl_init();
            curl_setopt ($curl, CURLOPT_URL, $_jsonfilestr);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec ($curl);
            curl_close ($curl);
            $twitters = json_decode($result);
        // }

        if( $twitters ) {
            // fix 0.1.2 - support overflow on IE 6
            // $output .= '<div id="twitter_time_line"  style="width:100%; overflow:hidden;" >';
            
            $cities;
            $states;
            foreach( $twitters as $tw ) {
                $city = $tw->venue->location->city;
                $state = $tw->venue->location->state;
                if( $state && $states_abbr[$state] ) {
                    $state = $states_abbr[$state];
                }
                if( $state ) {
                    $state = strtoupper($state);
                    $city .= ', ' . $state;
                }
                if( !$cities[$city] ) {
                    $cities[$city] = 0;
                }
                if( !$states[$state] ) {
                    $states[$state] = 0;
                }
                $cities[$city] += 1;
                $states[$state] += 1;
            }
            // echo json_encode($cities);
            // echo json_encode($states);
            
            $state_arr = array();
            foreach($states as $state => $count) {
                $state_arr[] = array($state, $count);
            }
            
            // echo json_encode($state_arr);
            $output .= "<div id='widget_singly_foursquare_states_chart'></div>";
            $output .= "<script type='text/javascript' src='http://code.jquery.com/jquery-1.7.min.js'></script>";
            $output .= "<script type='text/javascript' src='wp-content/plugins/widget_singly_foursquare/jquery.jqplot.min.js'></script>";
            $output .= "<script type='text/javascript' src='wp-content/plugins/widget_singly_foursquare/jqplot.pieRenderer.min.js'></script>";
            $output .= "<link rel='stylesheet' href='wp-content/plugins/widget_singly_foursquare/jquery.jqplot.min.css'/>";
            $output .= "<script type='text/javascript'>var states = " . json_encode($state_arr) . "; var MAX_BINS = 4;</script>";
            $output .= "<script type='text/javascript' src='wp-content/plugins/widget_singly_foursquare/plot.js'></script>";
            $options['widget_singly_foursquare_option_cached_time'] = time();
            $options['widget_singly_foursquare_option_cached_output'] = $output;
            update_option('widget_singly_foursquare', $options);
        }else{
            $output = $options['widget_singly_foursquare_option_cached_output'];
            $output .= '<!-- cached -->';
        }

        // These lines generate the output
        echo $before_widget . $before_title . $title . $after_title;
        echo $output;
        echo $after_widget;
    } /* widget_singly_foursquare() */


    function widget_singly_foursquare_control() {
        $options = $newoptions = get_option('widget_singly_foursquare');
        if ( $_POST["widget_singly_foursquare_submit"] ) {
            $newoptions['widget_singly_foursquare_title'] = strip_tags(stripslashes($_POST["widget_singly_foursquare_title"]));
            $newoptions['widget_singly_foursquare_option_api_key'] = $_POST["widget_singly_foursquare_option_api_key"];
            $newoptions['widget_singly_foursquare_option_cached_time'] = 0;
            $newoptions['widget_singly_foursquare_option_cached_output'] = "";
        }
        if ( $options != $newoptions ) {
            $options = $newoptions;
            update_option('widget_singly_foursquare', $options);
        }

        // those are default value
        if( !$options['widget_singly_foursquare_title'] ) $options['widget_singly_foursquare_title'] = "4sq Singly";
        $_api_key = $options['widget_singly_foursquare_option_api_key'];

        $title = htmlspecialchars($options['widget_singly_foursquare_title'], ENT_QUOTES);
?>

        <?php _e('Title:'); ?> <input style="width: 170px;" id="widget_singly_foursquare_title" name="widget_singly_foursquare_title" type="text" value="<?php echo $title; ?>" /><br />
        <?php _e('Singly API Key:'); ?> <input style="width: 200px;" id="widget_singly_foursquare_option_api_key" name="widget_singly_foursquare_option_api_key" type="text" value="<?php echo $_api_key; ?>" /><br />

          <input type="hidden" id="widget_singly_foursquare_submit" name="widget_singly_foursquare_submit" value="1" />

<?php
    } /* widget_singly_foursquare_control() */

    register_sidebar_widget('Foursquare Singly', 'widget_singly_foursquare');
    register_widget_control('Foursquare Singly', 'widget_singly_foursquare_control' );
} /* widget_singly_foursquare_init() */

add_action('plugins_loaded', 'widget_singly_foursquare_init');

?>