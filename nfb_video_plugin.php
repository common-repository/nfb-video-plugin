<?php
/**
 * @package NFB_Video_Plugin
 * @author Audrey-Rose Savard
 * @version 0.9.14
 */
/*
Plugin Name: NFB Video Plugin
Plugin URI: http://wordpress.org/extend/plugins/nfb-video-plugin/
Description: The NFB Video Plugin is designed to allow users to embed videos 
from sites who provide autodiscoverable oembed links on their 
film pages. Problems? a.savard@nfb.ca
Author: Audrey-Rose Savard | National Film Board of Canada
Version: 0.9.14
Author URI: http://audreyrosesavard.com/

  Copyright 2009-2012 National Film Board of Canada (email : a.savard@nfb.ca)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once('nfb_functions.php');

function nfb_get_embed_code($text){
	global $NFB_OEMBED, $NFB_ADMIN_ZONE;
	$NFB_ADMIN_ZONE = 0;

	$links = NFB_get_links($text);

	foreach($links as $link):
		$embed = NFB_retrieve_oembed(substr(trim($link), 2));
		$text = preg_replace('/\s?'.str_replace("?", "\?", str_replace("/","\/",trim($link))).'/i', $embed, $text);
	endforeach;
	return $text;
}

function oEmbed_NFB_menu(){
	global $NFB_OEMBED;
	$nfb_lang = ((get_option('nfb_admin_lang') != null ) ? get_option('nfb_admin_lang') : "en");
    $txt_1 = ( $nfb_lang == "fr") ? "Plugin Vid&eacute;o ONF - Param&ecirc;tres" : "NFB Video Plugin - Settings" ;
	$txt_2 = ( $nfb_lang == "fr") ? "Plugin Vid&eacute;o ONF" : "NFB Video Plugin" ;
	add_options_page($txt_1, $txt_2, 10, __FILE__,'oEmbed_NFB_options_main');
}

function oEmbed_NFB_options_main(){
	include('nfb_video_plugin_admin_main.php');
}

function nfb_video_plugin_activate(){
	// set the variables to change to the new ones.
	// these set your new variables and remove the old ones for the nfb video plugin
	$mod_vars = array(
		array( "nfb_max_vid_width", 516, "oembed_nfb_width" ),
		array( "nfb_max_vid_height", 337, "oembed_nfb_height" ),
		array( "nfb_admin_lang", "en", "" ),
		array( "nfb_show_description", 0, "" ),
		array( "nfb_css_oembed_box", "", "" ),
		array( "nfb_css_oembed_caption", "", "oembed_nfb_css_video_caption" ), 
		array( "nfb_css_description", "", "" ),
	);
	foreach ($mod_vars as $v):
		$option = get_option($v[2]);
		add_option( $v[0], ((($option != null) && ($option != "")) ? $option : $v[1] ) );
                if (get_option($v[0]) == ""):
		    if ($v[0] == "nfb_max_vid_width"):
		        update_option("nfb_max_vid_width", 516);
		    elseif ($v[0] == "nfb_max_vid_height"):
		        update_option("nfb_max_vid_height ", 337);
		    endif;
                endif;
		delete_option( $v[2] );
	endforeach;

	$del_vars = array(
		"oembed_nfb_video_provider",
		"oembed_nfb_separator",
		"oembed_nfb_video_title",
		"oembed_nfb_video_author",
		"oembed_nfb_css_video_plugin_provider",
		"oembed_nfb_css_video_plugin_author",
		"oembed_nfb_css_video_plugin_provider_link",
		"oembed_nfb_css_video_plugin_title_link",
		"oembed_nfb_css_video_plugin_title",
		"nfb_show_provider",
		"nfb_separator",
		"nfb_show_title",
		"nfb_show_author",
		"nfb_display_lang",
		"nfb_css_author_url",
		"nfb_css_provider",
		"nfb_css_author",
		"nfb_css_provider_url",
		"nfb_css_title_url",
		"nfb_css_film_title"
	);

	foreach ($del_vars as $v):
		delete_option($v);
	endforeach;
}
function nfb_video_plugin_deactivate(){

    //next update

}
/* PAGE DETAILS */

function oEmbed_NFB_head(){
		echo '<style type="text/css" media="screen">
		div.nfb-oembed-box {'.get_option('nfb_css_oembed_box').'}
		div.nfb-oembed-box div.nfb-oembed-caption {'.get_option('nfb_css_oembed_caption').'}
		div.nfb-oembed-box div.nfb-oembed-caption blockquote {'.get_option('nfb_css_description').'}
		</style>';
}

register_activation_hook(__file__,'nfb_video_plugin_activate');
register_deactivation_hook(__file__,'nfb_video_plugin_deactivate');

// execute the following filter to replace the links with embeds.
add_filter('the_content', 'nfb_get_embed_code', 1);

// The Administration parts for oEmbed_NFB
add_action('admin_menu', 'oEmbed_NFB_menu');

// The plugin adds the CSS to the header
add_action('wp_head', 'oEmbed_NFB_head');

?>
