<?php

// Sift through the first 200 lines to find the code.

function NFB_url_table_exists() {
	global $wpdb;
	$table_name = $wpdb->prefix.'nfb_video_plugin_urls';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name):
		$sql = "CREATE TABLE ". $table_name."(
			id bigint(5) NOT NULL AUTO_INCREMENT,
			version varchar(5) NOT NULL,
			type varchar(10) NOT NULL,
			title varchar(250) NULL,
			url varchar(300) NOT NULL,
			author_name varchar(60) NULL,
			author_url varchar(300) NULL,
			provider_name varchar(100) NULL,
			provider_url varchar(300) NULL,
			thumbnail_url varchar(300) NULL,
			thumbnail_width int(4) NULL,
			thumbnail_height int(4) NULL,
			html text NOT NULL,
			width int(4) NULL,
			height int(4) NULL,
			video_description text NULL,
			cust_video_description text NULL,
			cache_age int(6) NULL DEFAULT 0,
			UNIQUE KEY id(id)
		);";
		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name):
			return 1;
		else:
			return 0;
		endif;
	else:
		return 1;
	endif;
}

function NFB_retrieve_stored_embeds(){
	global $wpdb;
	$table_name = $wpdb->prefix.'nfb_video_plugin_urls';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name):
		$sql = "SELECT id, type, title, url, provider_name, thumbnail_url FROM $table_name;";
		$results = $wpdb->get_results($sql);
		return $results;
	else:
		return array();
	endif;
}

function NFB_inject_film_to_db(){
	global $NFB_OEMBED, $wpdb;
	$table_name = $wpdb->prefix.'nfb_video_plugin_urls';
	$sql = "INSERT INTO ".$table_name." (version, type, title, url, author_name, provider_name, provider_url, thumbnail_url, thumbnail_width, thumbnail_height, html, width, height, video_description, cust_video_description) VALUES('".$NFB_OEMBED['version']."', '".$NFB_OEMBED['type']."', '".$NFB_OEMBED['title']."', '".$NFB_OEMBED['original_link']."', '".$NFB_OEMBED['author_name']."', '".$NFB_OEMBED['provider_name']."', '".$NFB_OEMBED['provider_url']."', '".$NFB_OEMBED['thumbnail_url']."', ".$NFB_OEMBED['thumbnail_width'].", ".$NFB_OEMBED['thumbnail_height'].", '".$NFB_OEMBED['html']."', ".$NFB_OEMBED['width'].", ".$NFB_OEMBED['height'].", '".$NFB_OEMBED['video_description']."', '');";
	$results = $wpdb->query($sql);
	return $results;
}

function NFB_retrieve_oembed($url){
	global $NFB_OEMBED;

	$NFB_FOUND_OEMBED = $x = 0;
	$html = "";
	
	$file_handle = @fopen($url,"r");
	
	if (!$file_handle):
		return '<span style="color:red;">[Invalid Link]</span> '.$url;
	endif;

	while(!feof($file_handle)) :
		if ($x == 100){ break; }
		$toadd = fgets($file_handle, 4096);
		if (strstr($toadd, 'json+oembed') || strstr($toadd, 'xml+oembed')) :
			$html .= $toadd;
			$NFB_FOUND_OEMBED = 1;
		endif;
		$x++;
	endwhile;
	fclose($file_handle);
	
	
	//RETRIEVE THE OEMBED LINKS AND PUT THEM IN THE NFB_OEMBED ARRAY
	if ($NFB_FOUND_OEMBED == 1):
	
		$NFB_OEMBED['original_link'] = $url;
		
		if(NFB_url_table_exists() == 1):
			global $wpdb;
			$table_name = $wpdb->prefix.'nfb_video_plugin_urls';
			
			$sql = "SELECT * FROM ".$table_name." WHERE url='".$url."'";
			$results = $wpdb->get_results($sql);
			if (count($results) != 0):
				$NFB_OEMBED['exists_in_db'] = 1;
				global $nfb_from_db_result_set;
				$nfb_from_db_result_set = $results;
				if (NFB_extract_oembed('xml')):
					return NFB_print_embed();
				else:
					echo $url;
				endif;
			else:
				$NFB_OEMBED['exists_in_db'] = 0;
				preg_match_all('/https?:\/\/[0-9a-z_.\\w\/\\#~:?+=&;%@!-]*\?[0-9a-z_.\\w\/\\#~:?+=&;%@!-]*/i', $html, $oembedlinks);
				foreach($oembedlinks[0] as $olink) :
					if (strstr($olink, 'xml')) :
						$NFB_OEMBED['xml_link'] = $olink;
					elseif (strstr($olink, 'json')) :
						$NFB_OEMBED['json_link'] = $olink;
					endif;
				endforeach;
				if (NFB_extract_oembed('xml')):
					return NFB_print_embed();
				else:
					echo $url;
				endif;
			endif;
		endif;
	else:
		return $url;
	endif;
}

function NFB_replace_embed_values(){
	global $NFB_OEMBED;
	$regex = '/https?:\/\/[0-9a-z_.\\w\/\\#~:?+=&;%@!-]+/';
	preg_match($regex, html_entity_decode($NFB_OEMBED['html']), $url_matches);
	foreach ($url_matches as $match):
		$replace = $match."?traffic_src=nfb_video_plugin";
		$NFB_OEMBED['html'] = str_replace($match,$replace,$NFB_OEMBED['html']);
	endforeach;
	$replace_vals = array( 
		array( 'search' => '/width\=\"[0-9]+\"/', 'replace' => 'width="'.((get_option('nfb_max_vid_width') == "") ? $NFB_OEMBED['nfb_width'] : get_option('nfb_max_vid_width')).'"'),
		array( 'search' => '/width=([0-9]+)/', 'replace' => 'width='.((get_option('nfb_max_vid_width') == "") ? $NFB_OEMBED['nfb_width'] : get_option('nfb_max_vid_width')) ),
		array( 'search' => '/height\=\"[0-9]+\"/', 'replace' => 'height="'.((get_option('nfb_max_vid_height') == "") ? $NFB_OEMBED['nfb_height'] : get_option('nfb_max_vid_height')).'"' ),
		array( 'search' => '/height=([0-9]+)/', 'replace' => 'height='.((get_option('nfb_max_vid_height') == "") ? $NFB_OEMBED['nfb_height'] : get_option('nfb_max_vid_height')) )
	);
	str_replace("&nbsp;", '"', $NFB_OEMBED['html']);
	foreach ($replace_vals as $rv):
		$NFB_OEMBED['html'] = preg_replace($rv['search'], $rv['replace'], html_entity_decode($NFB_OEMBED['html']));
	endforeach;
	return $NFB_OEMBED['html'];
}

function NFB_extract_oembed($type){
	global $NFB_OEMBED, $NFB_ADMIN_ZONE;
	if ($NFB_OEMBED['exists_in_db'] == 1):
		global $wpdb, $nfb_from_db_result_set;
		$results = get_object_vars($nfb_from_db_result_set[0]);
		$NFB_OEMBED['version'] = $results['version'];
		$NFB_OEMBED['type'] = $results['type'];
		$NFB_OEMBED['html'] = $results['html'];
		$NFB_OEMBED['width'] = $results['width'];
		$NFB_OEMBED['height'] = $results['height'];
		$NFB_OEMBED['cache_age'] = $results['cache_age'];
		$NFB_OEMBED['thumbnail_height'] = $results['thumbnail_height'];
		$NFB_OEMBED['thumbnail_width'] = $results['thumbnail_width'];
		$NFB_OEMBED['title'] = $results['title'];
		$NFB_OEMBED['url'] = $results['url'];
		$NFB_OEMBED['video_description'] = $results['video_description'];
			$NFB_OEMBED['nfb_width'] = 516;
			$NFB_OEMBED['nfb_height'] = 228;

			if (($NFB_OEMBED['html'] != "") && ($NFB_ADMIN_ZONE == 0)):

				$NFB_OEMBED['html'] = NFB_replace_embed_values();

			endif;		
		return 1;
	else:
		if ($type == 'json') :
			$file_handle = file_get_contents(urldecode($NFB_OEMBED['json_link']), "r");
		else :
			$file_handle = file_get_contents(urldecode($NFB_OEMBED['xml_link']), "r");
		endif;
		/**************************************************************
		***  oEmbed Version number : Must be 1 to work.
		***************************************************************/
		preg_match_all( "/\<version\>(.*?)\<\/version\>/s", $file_handle, $result);

		if (strip_tags($result[0][0]) == "1.0"):
			$NFB_OEMBED['version'] = strip_tags($result[0][0]);
			
			$to_match = array('type', 'version', 'html', 'width', 'height', 'author_name', 'author_url', 'cache_age', 'provider_name', 'provider_url', 'thumbnail_height', 'thumbnail_width', 'thumbnail_url', 'title', 'video_description');
				
			foreach ($to_match as $tm):
				preg_match("/\<".$tm."\>(.*?)\<\/".$tm."\>/s", $file_handle, $test_result);
				$the_value = (count($test_result) > 0) ? $test_result[1] : "" ;

				$NFB_OEMBED[$tm] = strip_tags($the_value);
			endforeach;


			$NFB_OEMBED['nfb_width'] = 516;
			$NFB_OEMBED['nfb_height'] = 228;
			
			if (($NFB_OEMBED['html'] != "") && ($NFB_ADMIN_ZONE == 0)):
				$NFB_OEMBED['html'] = NFB_replace_embed_values();
			endif;
			NFB_inject_film_to_db();
			return true;
		else:
			return false;
		endif;
	endif;

}

function NFB_print_embed(){
	global $NFB_OEMBED;
	$the_string = "<div class='nfb-oembed-box'>";
	$the_string .= "{$NFB_OEMBED['html']}";
	$the_string .= "<div class='nfb-oembed-caption'><p>";
	
	if (get_option("nfb_show_title") == 1):
		if ($NFB_OEMBED['url'] != ""):
			$the_string .= "<span><a href={$NFB_OEMBED['url']}>{$NFB_OEMBED['title']}</a></span>";
		else:
			$the_string .= "<span>{$NFB_OEMBED['title']}</span>";
		endif;
	endif;

	if (get_option('nfb_show_description') == 1) :
		if ($NFB_OEMBED['video_description'] != ""):
			$the_string .= "<blockquote>{$NFB_OEMBED['video_description']}</blockquote>";
		endif;
	endif;

	$the_string .= "</p></div></div>";
	return $the_string;
}

function NFB_get_links($text){
	if (substr($text, 0, 4) == 'http'){
		$text = ' '.$text;
	}
	preg_match_all('/\s?\oehttps?:\/\/[0-9a-z_.\\w\/\\#~:?+=&;%@!-]*\?*[0-9a-z_.\\w\/\\#~:?+=&;%@!-]*/i', $text, $links);
	return $links[0];
}

function NFB_set_admin_text ($lang) {
	global $NFB_ADMIN_CAP;
    if ($lang == "fr"):
		$NFB_ADMIN_CAP = array(
			"h2" => "Plugins ONF",
			"setting" => "Param&ecirc;tres pour le plugin vid&eacute;o",
			"section_title_1" => "Param&ecirc;tres de langue",
			"section_title_2" => "Param&ecirc;tres vid&eacute;o",
			"section_title_3" => "Param&ecirc;tres de style (CSS)",
			"admin_lang" => "Langue d'administration",
			"max_vid_wid" => "Largeur max. vid&eacute;o",
			"max_vid_wid_def" => "(516 par d&eacute;faut)",
			"max_vid_hei" => "Hauteur max. vid&eacute;o",
			"max_vid_hei_def" => "(337 par d&eacute;faut)",
			"yes" => "oui",
			"no" => "non",
			"lang_1" => "Anglais (par d&eacute;faut)",
			"lang_2" => "Fran&ccedil;ais",
			"view_title" => "Les derni&egrave;res ajouts sur le site de l'ONF",
			"oembed_link" => "Prenez ce lien pour lier au oEmbed de ce film :",
			"show_description" => "Afficher la description du film",
			"oembed_zone" => "Couleur de fond<br /><em>Le bloc derriere le titre/embed/l&eacute;gende/description</em>",
			"caption_zone" => "Bo&icirc;te au tour du l&eacute;gende",
			"description" => "Description",
			"h1" => "",
		);
	else:
		$NFB_ADMIN_CAP = array(
			"h2" => "NFB Plugins",
			"setting" => "Settings for Video Plug-in",
			"section_title_1" => "Language Settings",
			"section_title_2" => "Video Settings",
			"section_title_3" => "Style Settings (CSS)",
			"admin_lang" => "Admin Language",
			"max_vid_wid" => "Max. Video Width",
			"max_vid_wid_def" => "(516 by default)",
			"max_vid_hei" => "Max. Video Height",
			"max_vid_hei_def" => "(337 by default)",
			"yes" => "yes",
			"no" => "no",
			"lang_1" => "English (by default)",
			"lang_2" => "French",
			"view_title" => "The latest selections from the NFB website",
			"oembed_link" => "Use this link to oEmbed this film :",
			"show_description" => "Show Film Description",
			"oembed_zone" => "Background<br /><em>The entire block behind the title/embed/caption/description</em>",
			"caption_zone" => "Box around the caption",
			"description" => "Description",
		);
	endif;
}
?>
