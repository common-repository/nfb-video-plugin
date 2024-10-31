<style>
#nfb-admin * {margin:0; padding:0;}
#nfb-admin {background-color:#000; min-width:1000px; position:relative; padding:10px; font-family:arial, helvetica, sans-serif; color:#ccc; font-size:12px;}
#nfb-admin:after {content:".";display:block;height:0;clear:both;visibility:hidden;}

#nfb-admin a {text-decoration:none; font-weight:bolder; color:#fff;}
#nfb-admin a:hover {text-decoration:underline;}
#nfb-admin p {font-size:12px; line-height:18px; margin-bottom:18px;}
#nfb-admin h2 {color:#fff; border-bottom:2px solid #fff; font-size:30px; line-height:36px; font-weight:normal;margin-right:10px; padding-bottom:3px;}
#nfb-admin h3 {color:#ff9900; font-size:16px; line-height:18px; font-weight:normal;}
#nfb-admin h4 {color:#ff9900; font-size:14px; line-height:18px;}
#nfb-admin h2, #nfb-admin h3 {margin-bottom:18px;max-width:500px;}
#nfb-admin table {margin-bottom:18px; max-width:500px;}
#nfb-admin table td {vertical-align:top;padding-bottom:9px;}
#nfb-admin ul {list-style:none;margin:0; padding:0; margin-bottom:18px;}
#nfb-admin ul li {display:inline; margin-right:10px;}

#nfb-admin #view-zone {width:516px; padding:10px; min-height:500px; float:right;background-color:#fff; border:3px solid #000; color:#000;}
#nfb-admin #view-zone h3.header {font-size:18px; font-weight:normal;}
#nfb-admin #view-zone a {color:#000;}
#nfb-admin #view-zone a:hover {color:#a50000;}
#nfb-admin #view-zone embed {margin-bottom:18px;}
#nfb-admin #view-zone input[type=text] {width:100%;}

#nfb-admin .nfb-note { font-size:11px;}
#nfb-admin .success-msg {padding:9px;margin-bottom:18px; border:1px solid white;max-width:475px;}
</style>
<?php
	$str_success = "";
	$error_var_1 = $error_var_2 = 0;
	
	if (isset($_POST['Submit'])):

		function reset_size(){
			if (($_POST['max-vid-width'] == "") || (is_numeric($_POST['max-vid-width']) == false)):
				update_option('nfb_max_vid_width', 516);
			endif;
			if (($_POST['max-vid-height'] == "") || (is_numeric($_POST['max-vid-height']) == false)):
				update_option('nfb_max_vid_height', 337);
			endif;
		}
		update_option('nfb_admin_lang', $_POST['admin-language']);
		if (($_POST['max-vid-width'] != "") && (is_numeric($_POST['max-vid-width']) == true)):
			update_option('nfb_max_vid_width', $_POST['max-vid-width']);
		else:
			$error_var_1 = 1;
		endif;
		if (($_POST['max-vid-height'] != "") && (is_numeric($_POST['max-vid-height']) == true)):
			update_option('nfb_max_vid_height', $_POST['max-vid-height']);
		else:
			$error_var_2 = 1;
		endif;
		update_option('nfb_show_description', $_POST['show-description']);
		if (($error_var_1 == 1) || ($error_var_2 == 1)):
			reset_size();
			$str_success = '<div class="success-msg">The video size you have entered is invalid; size reset to default</div>';
		else:
			$str_success = '<div class="success-msg">Your settings have been updated.</div>';
		endif;
		update_option('nfb_css_oembed_box', $_POST['oembed-box-css']);
		update_option('nfb_css_oembed_caption', $_POST['oembed-caption-css']);
		update_option('nfb_css_description', $_POST['description-css']);
		$error_var_1 = $error_var_2 = 0;
	endif;

	global $NFB_ADMIN_CAP, $NFB_ADMIN_ZONE;
	$NFB_ADMIN_ZONE = 1;
	include_once('nfb_functions.php');
	NFB_set_admin_text(get_option('nfb_admin_lang'));
?>

	<div id="nfb-admin">
		<div id="view-zone">
			<h3 class="header"><?php echo $NFB_ADMIN_CAP['view_title']?></h3>
				<?php //CODE TO GET THE RSS FEED
					require_once (ABSPATH . WPINC . '/rss-functions.php');
					$rss = @fetch_rss(((get_option('nfb_admin_lang') == "fr") ? 'http://onf.ca/fil-rss/ajouts-recents/' : 'http://nfb.ca/feeds/new_additions/'));
					if ( isset($rss->items) && 0 != count($rss->items) ) { ?>		
					<ul>
					<?php
					$rss->items = array_slice($rss->items, 0, 5);
					$x = 0;
					foreach ($rss->items as $item ) { 
						global $NFB_OEMBED;
						$NFB_OEMBED['xml_link'] = "http://www.nfb.ca/remote/services/oembed/?url=".$item['link']."&amp;format=xml";
						NFB_extract_oembed('xml'); ?>
						<h3><a href="<?php echo wp_filter_kses($item['link']); ?>" title="<?php echo wp_specialchars($item['title']); ?>"><?php echo wp_specialchars($item['title']); ?></a></h3>
						<?php echo htmlspecialchars_decode($NFB_OEMBED['html']);?>
						<p><br /><?php echo stripslashes(wp_filter_kses($item['description'])); ?></p>
						<p><?php echo $NFB_ADMIN_CAP['oembed_link']?><br /><input type="text" value="oe<?php echo wp_filter_kses($item['link']); ?>" /></p>
					<?php 
						$x = $x + 1;
						if ($x >= 3):
							break;
						endif;
					} ?>
					</ul>
				<?php } ?>
			
			

			<p><a href="http://www.nfb.ca/">Get more films from the NFB!</a></p>
		</div>
		
		<h2><?php echo $NFB_ADMIN_CAP['h2']?></h2>
		<?php echo $str_success;?>
		<h3><?php echo $NFB_ADMIN_CAP['setting']?></h3>
<form action="" method="post" >		
		<table>
			<tr>
				<td colspan="2"><h4><?php echo $NFB_ADMIN_CAP['section_title_1']?></h4></td>
			</tr>
			<tr>
				<td width="40%"><?php echo $NFB_ADMIN_CAP['admin_lang']?>&nbsp;&nbsp;</td> 
				<td>
					<select name="admin-language">
						<option value="en" <?php if (get_option('nfb_admin_lang') == "en") { ?>selected<?php } ?>><?php echo $NFB_ADMIN_CAP['lang_1']?></option>
						<option value="fr" <?php if (get_option('nfb_admin_lang') == "fr") { ?>selected<?php } ?>><?php echo $NFB_ADMIN_CAP['lang_2']?></option>
					</select>
				</td>
			</tr>
		</table>
		
		<table>
			<tr>
				<td colspan="2"><h4><?php echo $NFB_ADMIN_CAP['section_title_2']?></h4></td>
			</tr>
			<tr>
				<td width="40%"><?php echo $NFB_ADMIN_CAP['max_vid_wid']?>&nbsp;&nbsp;</td> 
				<td><input name="max-vid-width" type="text" size="6" value="<?php echo get_option('nfb_max_vid_width');?>" /> (516 by default)</td>
			</tr>
			<tr>
				<td><?php echo $NFB_ADMIN_CAP['max_vid_hei']?>&nbsp;&nbsp;</td> 
				<td><input name="max-vid-height" type="text" size="6"  value="<?php echo get_option('nfb_max_vid_height');?>" /> (337 by default)</td>
			</tr>
			<tr>
				<td><?php echo $NFB_ADMIN_CAP['show_description']?></td> 
				<td>
					<select name="show-description">
						<option value="0" <?php if (get_option('nfb_show_description') == 0) { ?>selected<?php } ?>><?php echo $NFB_ADMIN_CAP['no']?></option>
						<option value="1" <?php if (get_option('nfb_show_description') == 1) { ?>selected<?php } ?>><?php echo $NFB_ADMIN_CAP['yes']?></option>
					</select>
				</td>
			</tr>

		</table>
		<table>
			<tr>
				<td colspan="2"><h4><?php echo $NFB_ADMIN_CAP['section_title_3']?></h4></td>
			</tr>
			<tr>
				<td width="45%"><?php echo $NFB_ADMIN_CAP['oembed_zone']?></td> 
				<td>
					<textarea name="oembed-box-css"><?php echo get_option('nfb_css_oembed_box');?></textarea>
				</td>
			</tr>
			<tr>
				<td><?php echo $NFB_ADMIN_CAP['caption_zone']?></td> 
				<td>
					<textarea name="oembed-caption-css"><?php echo get_option('nfb_css_oembed_caption');?></textarea>
				</td>
			</tr>		
			<tr>
				<td><?php echo $NFB_ADMIN_CAP['description']?></td> 
				<td>
					<textarea name="description-css"><?php echo get_option('nfb_css_description');?></textarea>
				</td>
			</tr>				
		</table>
		<table>
			<tr>
				<td colspan="2">

					<input type="submit" name="Submit" value="Save Changes" />
				</td>
			</tr>
		</table>
</form>
	</div>
