<?php
/**
 Plugin Name: bbPress Post Toolbar
 Plugin URI: http://wordpress.org/extend/plugins/bbpress-post-toolbar/
 Description: Post toolbar for click-to-insert HTML elements, as well as [youtube][/youtube] shortcode handling.
 Version: 0.4.0
 Author: Jason Schwarzenberger
 Author URI: http://master5o1.com/
*/
/*  Copyright 2011  Jason Schwarzenberger  (email : jason@master5o1.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// WordPress Actions & Filters:
add_action( 'init', array('bbp_5o1_toolbar', 'plugin_do_options') );

// TODO: Make these so they're only done on bbPress pages:
add_action( 'wp_head' , array('bbp_5o1_toolbar', 'post_form_toolbar_script') );
if ( !get_option( 'bbp_5o1_toolbar_manual_insertion' ) )
	add_action( 'wp_footer' , array('bbp_5o1_toolbar', 'post_form_toolbar_delete') );

add_action('admin_menu', array('bbp_5o1_toolbar', 'admin_add_config_link') );
add_filter( 'plugin_action_links', array('bbp_5o1_toolbar', 'admin_add_settings_link') , 10, 2 );

if ( get_option('bbp_5o1_toolbar_use_custom_smilies') ) {
	add_filter( 'smilies_src', array('bbp_5o1_toolbar', 'switch_smileys_url'), 0, 3 );
	if ( file_exists(str_replace('plugins/bbpress-post-toolbar','smilies/package-config.php', dirname(__FILE__))) )
		require_once(str_replace('plugins/bbpress-post-toolbar','smilies/package-config.php', dirname(__FILE__)));
	elseif ( file_exists(dirname(__FILE__) . '/smilies/package-config.php') )
		require_once('smilies/package-config.php');
}

// bbPress 2.0 Actions & Filters:
add_filter( 'bbp_get_reply_content', array('bbp_5o1_toolbar', 'do_youtube_shortcode') );
if ( !get_option( 'bbp_5o1_toolbar_manual_insertion' ) )
	add_action( 'bbp_template_notices' , array('bbp_5o1_toolbar', 'post_form_toolbar_bar') );
if ( get_option( 'bbp_5o1_toolbar_manual_insertion' ) )
	add_action( 'bbp_post_toolbar_insertion', array('bbp_5o1_toolbar','post_form_toolbar_bar') );

// Shortcodes:
add_shortcode( 'youtube', array('bbp_5o1_toolbar', 'youtube_shortcode') );

// Plugin Activation/Deactivation Hooks:	
register_activation_hook(__FILE__, array('bbp_5o1_toolbar', 'plugin_activation') );
register_deactivation_hook(__FILE__, array('bbp_5o1_toolbar', 'plugin_deactivation') );

load_plugin_textdomain('bbp_5o1_toolbar', false, basename( dirname( __FILE__ ) ) . '/languages' );

// Plugin class:
class bbp_5o1_toolbar {

	function version() {
		$plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ), 'plugin' );
		return $plugin_data['Version'];
	}
	
	function plugin_activation() {
		add_option( 'bbp_5o1_toolbar_use_custom_smilies', false, '', 'yes' );
		add_option( 'bbp_5o1_toolbar_use_youtube', true, '', 'yes' );
		add_option( 'bbp_5o1_toolbar_use_textalign', false, '', 'yes' );
		add_option( 'bbp_5o1_toolbar_use_images', false, '', 'yes' );
		add_option( 'bbp_5o1_toolbar_show_credit', false, '', 'yes' );
		add_option( 'bbp_5o1_toolbar_custom_help', false, '', 'yes' );
		add_option( 'bbp_5o1_toolbar_manual_insertion', false, '', 'yes' );
	}
	
	function plugin_deactivation() {
		// Perhaps allow a save-from-deletion option on deactivation?
		delete_option( 'bbp_5o1_toolbar_use_custom_smilies' );
		delete_option( 'bbp_5o1_toolbar_use_youtube' );
		delete_option( 'bbp_5o1_toolbar_use_textalign' );
		delete_option( 'bbp_5o1_toolbar_use_images' );
		delete_option( 'bbp_5o1_toolbar_show_credit' );
		delete_option( 'bbp_5o1_toolbar_custom_help' );
		delete_option( 'bbp_5o1_toolbar_manual_insertion' );
	}
	
	function admin_add_settings_link( $links, $file ) {
		if ( 'bbpress-post-toolbar/bbpress-post-toolbar.php' != $file )
			return $links;
		$settings_link = '<a href="' . admin_url( 'plugins.php?page=bbpress-post-toolbar' ) . '">' . __( 'Settings', 'bbp_5o1_toolbar') . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
	
	function plugin_options_page() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		$custom_smilies = false;
		if ( get_option('bbp_5o1_toolbar_use_custom_smilies') )
			$custom_smilies = true;
		$youtube = false;
		if ( get_option('bbp_5o1_toolbar_use_youtube') )
			$youtube = true;
		$textalign = false;
		if ( get_option('bbp_5o1_toolbar_use_textalign') )
			$textalign = true;
		$images = false;
		if ( get_option('bbp_5o1_toolbar_use_images') )
			$images = true;
		$credit = false;
		if ( get_option('bbp_5o1_toolbar_show_credit') )
			$credit = true;
		$manual = false;
		if ( get_option( 'bbp_5o1_toolbar_manual_insertion' ) )
			$manual = true;
		?>
		<div class="wrap">
			<div style="max-width: 650px;">
				<h2>bbPress Post Toolbar</h2>
				<?php _e('Plugin Version', 'bbp_5o1_toolbar'); ?> <?php echo bbp_5o1_toolbar::version(); ?><br />
				<?php _e('If you enjoy this plugin, please consider making a <a href="http://master5o1.com/donate/">donation</a> to master5o1 as a token of thanks.', 'bbp_5o1_toolbar'); ?>
				<h3><?php _e('Options', 'bbp_5o1_toolbar'); ?></h3>
				<form method="post" action="">
					<p>
						<strong><?php _e('Use customised smilies?', 'bbp_5o1_toolbar'); ?></strong><br /><br />
						<span style="margin: 0 50px;">
						<label style="display: inline-block; width: 150px;"><input name="bbp_5o1_toolbar_use_custom_smilies" type="radio" value="1" <?php print (($custom_smilies) ? 'checked="checked"' : '' ) ?> /> <?php _e('Yes', 'bbp_5o1_toolbar'); ?></label>
						<label><input name="bbp_5o1_toolbar_use_custom_smilies" type="radio" value="0" <?php print ((!$custom_smilies) ? 'checked="checked"' : '' ) ?> /> <?php _e('No (default)', 'bbp_5o1_toolbar'); ?></label>
						</span><br />
						<div style="margin: 0 50px;"><small><?php _e('Note: It is recommended that the <code>/wp-content/plugins/bbpress-post-toolbar/smilies/</code> directory is copied or moved to the <code>/wp-content/</code> directory.  This is to prevent any custom smilies that you may have added from being lost on an upgrade to this plugin.', 'bbp_5o1_toolbar'); ?></small></div>
					</p>
					<p>
						<strong><?php _e('Allow embedding of Youtube videos?', 'bbp_5o1_toolbar'); ?></strong><br /><br />
						<span style="margin: 0 50px;">
						<label style="display: inline-block; width: 150px;"><input name="bbp_5o1_toolbar_use_youtube" type="radio" value="1" <?php print (($youtube) ? 'checked="checked"' : '' ) ?> /> <?php _e('Yes (default)', 'bbp_5o1_toolbar'); ?></label>
						<label><input name="bbp_5o1_toolbar_use_youtube" type="radio" value="0" <?php print ((!$youtube) ? 'checked="checked"' : '' ) ?> /> No</label>
						</span><br />
						<div style="margin: 0 50px;"><small><?php _e('Note: To embed a video, the YouTube video link must be wrapped using the [youtube] shortcode  An example is shown on the YouTube panel in the toolbar.', 'bbp_5o1_toolbar'); ?></small></div>
					</p>
					<p>
						<strong><?php _e('Allow text-alignment buttons?', 'bbp_5o1_toolbar'); ?></strong><br /><br />
						<span style="margin: 0 50px;">
						<label style="display: inline-block; width: 150px;"><input name="bbp_5o1_toolbar_use_textalign" type="radio" value="1" <?php print (($textalign) ? 'checked="checked"' : '' ) ?> /> <?php _e('Yes', 'bbp_5o1_toolbar'); ?></label>
						<label><input name="bbp_5o1_toolbar_use_textalign" type="radio" value="0" <?php print ((!$textalign) ? 
'checked="checked"' : '' ) ?> /> <?php _e('No (default)', 'bbp_5o1_toolbar'); ?></label>
						</span>
					</p>
					<p>
						<strong><?php _e('Allow images to be posted?', 'bbp_5o1_toolbar'); ?></strong><br /><br />
						<span style="margin: 0 50px;">
						<label style="display: inline-block; width: 150px;"><input name="bbp_5o1_toolbar_use_images" type="radio" value="1" <?php print (($images) ? 'checked="checked"' : '' ) ?> /> <?php _e('Yes', 'bbp_5o1_toolbar'); ?></label>
						<label><input name="bbp_5o1_toolbar_use_images" type="radio" value="0" <?php print ((!$images) ? 
'checked="checked"' : '' ) ?> /> <?php _e('No (default)', 'bbp_5o1_toolbar'); ?></label>
						</span><br />
						<div style="margin: 0 50px;"><small><?php _e('Note: Allowing images in bbPress posts will also allow them in WordPress comments.  I will try to disable this when I have learnt a bit more about WordPress and bbPress.', 'bbp_5o1_toolbar'); ?></small></div>
					</p>
					<p>
						<strong><?php _e('Link to master5o1&#39;s website in the About panel as a credit to the plugin developer?', 'bbp_5o1_toolbar'); ?></strong><br /><br />
						<span style="margin: 0 50px;">
						<label style="display: inline-block; width: 150px;"><input name="bbp_5o1_toolbar_show_credit" type="radio" value="1" <?php print (($credit) ? 'checked="checked"' : '' ) ?> /> <?php _e('Yes', 'bbp_5o1_toolbar'); ?></label>
						<label><input name="bbp_5o1_toolbar_show_credit" type="radio" value="0" <?php print ((!$credit) ? 
'checked="checked"' : '' ) ?> /> <?php _e('No (default)', 'bbp_5o1_toolbar'); ?></label>
						</span><br />
						<div style="margin: 0 50px;">
							<span><strong><?php _e('Example', 'bbp_5o1_toolbar'); ?>:</strong> <?php printf( __('Version %s by %s.', 'bbp_5o1_toolbar'), bbp_5o1_toolbar::version(), '<a href="http://master5o1.com/" title="master5o1&#39;s website">master5o1</a>' ); ?></span><br />
							<span><strong><?php _e('Default', 'bbp_5o1_toolbar'); ?>:</strong> <?php printf( __('Version %s by %s.', 'bbp_5o1_toolbar'), bbp_5o1_toolbar::version(), 'master5o1' ); ?></span>
						</div>
					</p>
					<p>
						<strong><?php _e('Customised help panel message.'); ?></strong></br /><br />
						<textarea name="bbp_5o1_toolbar_custom_help" style="margin: 0 50px; min-height: 100px; min-width: 400px;"><?php echo get_option('bbp_5o1_toolbar_custom_help'); ?></textarea>
						<div style="margin: 0 50px;">
							<small><?php _e('Clear the text area to revert to the default help panel message.', 'bbp_5o1_toolbar'); ?></small>
						</div>
					</p>

					<p>
						<strong><?php _e('Allow manual insertion of the bar?', 'bbp_5o1_toolbar'); ?></strong><br /><br />
						<span style="margin: 0 50px;">
						<label style="display: inline-block; width: 150px;"><input name="bbp_5o1_toolbar_manual_insertion" type="radio" value="1" <?php print (($manual) ? 'checked="checked"' : '' ) ?> /> <?php _e('Yes', 'bbp_5o1_toolbar'); ?></label>
						<label><input name="bbp_5o1_toolbar_manual_insertion" type="radio" value="0" <?php print ((!$manual) ? 
'checked="checked"' : '' ) ?> /> <?php _e('No (default)', 'bbp_5o1_toolbar'); ?></label>
						</span><br />
						<div style="margin: 0 50px;"><small><?php _e("Note: Manual bar insertion requires that you use <code>&lt;?php do_action( 'bbp_post_toolbar_insertion' ); ?&gt;</code> in your theme files, or wherever you desire the bar to appear.", 'bbp_5o1_toolbar'); ?></small></div>
					</p>
					
					<input type="hidden" name="bbpress-post-toolbar" value="bbpress-post-toolbar" />
					<input type="submit" value="Submit" />
				</form>
			</div>
		</div>
		<?php
	}
	
	function plugin_do_options() {
		if ( is_admin() && current_user_can('manage_options') ) {
			if (isset($_POST['bbpress-post-toolbar']) && $_POST['bbpress-post-toolbar'] == "bbpress-post-toolbar") {
			
				if ($_POST['bbp_5o1_toolbar_use_custom_smilies'] == 1)
					update_option('bbp_5o1_toolbar_use_custom_smilies', true);
				elseif ($_POST['bbp_5o1_toolbar_use_custom_smilies'] == 0)
					update_option('bbp_5o1_toolbar_use_custom_smilies', false);
					
				if ($_POST['bbp_5o1_toolbar_use_youtube'] == 1)
					update_option('bbp_5o1_toolbar_use_youtube', true);
				elseif ($_POST['bbp_5o1_toolbar_use_youtube'] == 0)
					update_option('bbp_5o1_toolbar_use_youtube', false);
					
				if ($_POST['bbp_5o1_toolbar_use_textalign'] == 1)
					update_option('bbp_5o1_toolbar_use_textalign', true);
				elseif ($_POST['bbp_5o1_toolbar_use_textalign'] == 0)
					update_option('bbp_5o1_toolbar_use_textalign', false);
					
				if ($_POST['bbp_5o1_toolbar_use_images'] == 1)
					update_option('bbp_5o1_toolbar_use_images', true);
				elseif ($_POST['bbp_5o1_toolbar_use_images'] == 0)
					update_option('bbp_5o1_toolbar_use_images', false);
					
				if ($_POST['bbp_5o1_toolbar_show_credit'] == 1)
					update_option('bbp_5o1_toolbar_show_credit', true);
				elseif ($_POST['bbp_5o1_toolbar_show_credit'] == 0)
					update_option('bbp_5o1_toolbar_show_credit', false);
				
				update_option('bbp_5o1_toolbar_custom_help', $_POST['bbp_5o1_toolbar_custom_help']);
				
				if ($_POST['bbp_5o1_toolbar_manual_insertion'] == 1)
					update_option('bbp_5o1_toolbar_manual_insertion', true);
				elseif ($_POST['bbp_5o1_toolbar_manual_insertion'] == 0)
					update_option('bbp_5o1_toolbar_manual_insertion', false);
			}
		}
	}
	
	function admin_add_config_link() {
		if ( function_exists('add_submenu_page') )
			add_submenu_page('plugins.php', __('bbPress Post Toolbar Options', 'bbp_5o1_toolbar'), __('bbPress Post Toolbar', 'bbp_5o1_toolbar'), 'manage_options', 'bbpress-post-toolbar', array('bbp_5o1_toolbar','plugin_options_page') );
	}
	
	function do_youtube_shortcode($content) {
		$shortcode_tags = array('youtube' => Array ( 'bbp_5o1_toolbar', 'youtube_shortcode' ));
		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;
		$tagnames = array_keys($shortcode_tags);
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
		$pattern = '(.?)\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
		return preg_replace_callback('/'.$pattern.'/s', 'do_shortcode_tag', $content);
	}
	
	function youtube_shortcode( $atts, $content = null ) {
		$url_query = explode('&', parse_url($content, PHP_URL_QUERY));
		foreach ($url_query as $query) {
			$q = explode('=', $query);
			$video_code[$q[0]] = $q[1];
		}
		return '<iframe style="margin:1.0em auto;" width="425" height="349" src="http://www.youtube.com/embed/'.$video_code['v'].'" frameborder="0" allowfullscreen></iframe>';
	}

	function switch_smileys_url($link, $img, $url) {
		if ( file_exists(str_replace('plugins/bbpress-post-toolbar','smilies/package-config.php', dirname(__FILE__))) )
			return $url . "/wp-content/smilies/" . $img;
		elseif ( file_exists(dirname(__FILE__) . '/smilies/package-config.php') )
			return $url . "/wp-content/plugins/bbpress-post-toolbar/smilies/" . $img;
		return $link;
	}

	function switch_panel($panel) {
		global $wpsmiliestrans;
		$data = "";
		if ($panel == 'link') {
			$data = '<div style="width: 310px; display: inline-block;"><span>Link URL:</span><br />
<input style="display:inline-block;width:300px;" type="text" id="link_url" value="" /></div>
<div style="width: 310px; display: inline-block;"><span>Link Name: (optional)</span><br />
<input style="display:inline-block;width:300px;" type="text" id="link_name" value="" /></div>
<a class="toolbar-apply" style="margin-top: 1.4em;" onclick="insert_panel(\'link\');">Apply Link</a>
<p style="font-size: x-small;">Hint: Paste the link URL into the <em>Link URL</em> text box, then select text and hit <a onclick="insert_panel(\'link\');">Apply Link</a> to use the selected text as the link name.</p>';
		} elseif ($panel == 'image') {
			$data = '<div style="width: 310px; display: inline-block;"><span>Image URL:</span><br />
<input style="display:inline-block;width:300px;" type="text" id="image_url" value="" /></div>
<div style="width: 310px; display: inline-block;"><span>Image Title: (optional)</span><br />
<input style="display:inline-block;width:300px;" type="text" id="image_title" value="" /></div>
<div style="clear:both;">Image hosting: <a href="http://ompldr.org" target="_blank">Omploader</a>, <a href="http://imgur.com" target="_blank">imgur</a><a class="toolbar-apply" onclick="insert_panel(\'image\');">Apply Image</a></div>';
		} elseif ($panel == 'color') {
			$data = '<span title="Red" onclick="insert_color(\'red\');" style="background:red;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>
<span title="Green" onclick="insert_color(\'green\');" style="cursor:pointer;background:green;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>

<span title="Blue" onclick="insert_color(\'blue\');" style="cursor:pointer;background:blue;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>
<span title="Yellow" onclick="insert_color(\'yellow\');" style="cursor:pointer;background:yellow;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>
<span title="Magenta" onclick="insert_color(\'magenta\');" style="cursor:pointer;background:magenta;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>
<span title="Cyan" onclick="insert_color(\'cyan\');" style="cursor:pointer;background:cyan;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>
<span title="Black" onclick="insert_color(\'black\');" style="cursor:pointer;background:black;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>
<span title="White" onclick="insert_color(\'white\');" style="cursor:pointer;background:white;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>
<span title="Grey" onclick="insert_color(\'grey\');" style="cursor:pointer;background:grey;width:50px;height:50px;display:inline-block;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;cursor:pointer;"></span>';
		} elseif ($panel == 'size') {
			$data = '<div style="line-height: 50px;"><a class="size" onclick="insert_size(\'xx-small\');" style="font-size:xx-small;">xx-small</a>
<a class="size" onclick="insert_size(\'x-small\');" style="font-size:x-small;">x-small</a>
<a class="size" onclick="insert_size(\'small\');" style="font-size:small;">small</a>
<a class="size" onclick="insert_size(\'medium\');" style="font-size:medium;">medium</a>
<a class="size" onclick="insert_size(\'large\');" style="font-size:large;">large</a>
<a class="size" onclick="insert_size(\'x-large\');" style="font-size:x-large;">x-large</a>
<a class="size" onclick="insert_size(\'xx-large\');" style="font-size:xx-large;">xx-large</a></div>';
		} elseif ($panel == 'smiley') {
			foreach ($wpsmiliestrans as $code => $name) {
				$data .= '<a class="smiley" onclick="insert_smiley(\''.$code.'\');">' . str_replace("class='wp-smiley' ", '', convert_smilies($code)) . '</a>';
			}
		} elseif ($panel == 'youtube') {
			$random_yt[] = "http://www.youtube.com/watch?v=RSJbYWPEaxw";
			$random_yt[] = "http://www.youtube.com/watch?v=GI6CfKcMhjY";
			$random_yt[] = "http://www.youtube.com/watch?v=XCspzg9-bAg";
			$random_yt[] = "http://www.youtube.com/watch?v=RZ-uV72pQKI";
			$random_yt[] = "http://www.youtube.com/watch?v=rgUrqGFxV3Q";
			$data = '<div style="width: 310px; display: inline-block;"><span>Youtube URL:</span><br />
<input style="display:inline-block;width:300px;" type="text" id="youtube_url" value="" /></div>
<a class="toolbar-apply" style="margin-top: 1.4em;" onclick="insert_panel(\'youtube\');">Apply Link</a>
<p style="font-size: x-small;">Random Example: [youtube]'.$random_yt[rand(0, (count($random_yt)-1))].'[/youtube]</p>';
		}
		return $data;
	}
	
	function post_form_toolbar_bar() {
		global $wpsmiliestrans;
		$items = array();
		$items[] = array( 'action' => 'insert_data',
						 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/bold.png" title="Bold" alt="Bold" />',
						 'data' => 'strong');
		$items[] = array( 'action' => 'insert_data',
						 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/italic.png" title="Italics" alt="Italics" />',
						 'data' => 'em');
		$items[] = array( 'action' => 'insert_data',
						 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/underline.png" title="Underline" alt="Underline" />',
						 'data' => 'underline');
		$items[] = array( 'action' => 'insert_data',
						 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/strikethrough.png" title="Strike through" alt="Strike through" />',
						 'data' => 'strike');
		$items[] = array( 'action' => 'switch_panel',
						 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/fontcolor.png" title="Color" alt="Color" />',
						 'data' => bbp_5o1_toolbar::switch_panel('color'));
		$items[] = array( 'action' => 'switch_panel',
						 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/font.png" title="Size" alt="Size" />',
						 'data' => bbp_5o1_toolbar::switch_panel('size'));
		if ( get_option( 'use_smilies' ) ) {
			$items[] = array( 'action' => 'switch_panel',
							  'inside_anchor' => str_replace("class='wp-smiley' ", '', convert_smilies(':)')),
							  'data' => bbp_5o1_toolbar::switch_panel('smiley'));
		}
		$items[] = array( 'action' => 'switch_panel',
						 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/link.png" title="Link" alt="Link" />',
						 'data' => bbp_5o1_toolbar::switch_panel('link'));
		if ( get_option('bbp_5o1_toolbar_use_images') ) {
			$items[] = array( 'action' => 'switch_panel',
							 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/image.png" title="Image" alt="Image" />',
							 'data' => bbp_5o1_toolbar::switch_panel('image'));
		}
		if ( get_option('bbp_5o1_toolbar_use_youtube') ) {
			$items[] = array( 'action' => 'switch_panel',
							  'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/youtube.png" title="Youtube" alt="Youtube" />',
							  'data' => bbp_5o1_toolbar::switch_panel('youtube'));
		}
		$items[] = array( 'action' => 'insert_data',
						  'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/quote.png" title="Quote" alt="Quote" />',
						  'data' => 'blockquote');
		$items[] = array( 'action' => 'insert_data',
						  'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/code.png" title="Code" alt="Code" />',
						  'data' => 'code');
		if ( get_option('bbp_5o1_toolbar_use_textalign') ) {
			$items[] = array( 'action' => 'insert_data',
							 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/fontleft.png" title="Left Align" alt="Left Align" />',
							 'data' => 'fontleft');
			$items[] = array( 'action' => 'insert_data',
							 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/fontcenter.png" title="Center Align" alt="Center Align" />',
							 'data' => 'fontcenter');
			$items[] = array( 'action' => 'insert_data',
							 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/fontjustify.png" title="Justified Align" alt="Justified Align" />',
							 'data' => 'fontjustify');
			$items[] = array( 'action' => 'insert_data',
							 'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/bbpress-post-toolbar/images/fontright.png" title="Right Align" alt="Right Align" />',
							 'data' => 'fontright');
		}
		// Allow for pluggable and extended items to be added:
		$items = array_merge($items, apply_filters( 'bbp_5o1_toolbar_add_items' , array() ));
		?>
		<div id="post-toolbar">
			<ul id="buttons" style="list-style-type: none;"><?php
				$i = 0;
				foreach ($items as $item) :
					if ($item['action'] == 'api_item') : 
						?><li><a onclick="do_button({action : '<?php echo $item['action']; ?>', panel : 'post-toolbar-item-<?php print $i; ?>'}, <?php echo $item['data']; ?>)"><?php echo $item['inside_anchor']; ?></a></li><?php
					else:
						?><li><a onclick="do_button({ action : '<?php echo $item['action']; ?>', panel : 'post-toolbar-item-<?php print $i; ?>' }, function(){ return '<?php if ($item['action'] == 'insert_data' || $item['action'] == 'insert_shortcode') { echo $item['data']; } ?>';})"><?php echo $item['inside_anchor']; ?></a></li><?php
					endif;
					$i++;
				endforeach;
			  ?><li class="right-button"><a onclick="switch_panel('post-toolbar-item-help');"><?php _e( 'Help', 'bbp_5o1_toolbar'); ?></a></li>
			</ul>
			<?php
			$i = 0;
			foreach ($items as $item) :
				if ($item['action'] == 'switch_panel') :
					?><div id="post-toolbar-item-<?php print $i; ?>" class="panel"><?php print $item['data']; ?></div><?php
				endif;
				$i++;
			endforeach;
			?><div id="post-toolbar-item-help" class="panel">
				<h4 style="display: inline-block;">bbPress Post Toolbar <?php _e('Help', 'bbp_5o1_toolbar'); ?></h4><span style="line-height: 16px; margin: auto 5px;">&mdash; <a onclick="switch_panel('post-toolbar-item-about');" style="cursor: pointer;"><?php _e('About', 'bbp_5o1_toolbar'); ?></a></span>
				<div>
			<?php if ( !get_option('bbp_5o1_toolbar_custom_help') ) : ?>
				<p><?php _e("This toolbar allows simple click-to-add HTML elements.", 'bbp_5o1_toolbar'); ?></p>
				<p><?php _e("For the options that are simple buttons (e.g. bold, italics), one can select text and then click the button to apply the tag around the selected text.", 'bbp_5o1_toolbar'); ?></p>
				<p><?php _e("For the options at open panels (e.g. link), open the panel first, add the url to the text box (if link), then hit Apply Link.  If it's font sizing or colors, then select the text and click the size you want, e.g., xx-small.", 'bbp_5o1_toolbar'); ?></p>
			<?php else: echo get_option('bbp_5o1_toolbar_custom_help'); endif; ?>
				</div>
			</div>
			<div id="post-toolbar-item-about" class="panel">
				<h4 style="display: inline-block;"><?php _e('About', 'bbp_5o1_toolbar'); ?> bbPress Post Toolbar</h4><span style="line-height: 16px; margin: auto 5px;">&mdash; <a onclick="switch_panel('post-toolbar-item-help');" style="cursor: pointer;"><?php _e('Help', 'bbp_5o1_toolbar'); ?></a></span>
				<p><?php _e("This toolbar allows simple click-to-add HTML elements.", 'bbp_5o1_toolbar'); ?></p>
				<span><?php printf( __('Version %s by %s.', 'bbp_5o1_toolbar'), bbp_5o1_toolbar::version(), ((get_option('bbp_5o1_toolbar_show_credit') ? '<a href="http://master5o1.com/" title="master5o1&#39;s website">master5o1</a>' : 'master5o1') ) ); ?></span>
			</div>
		</div>
		<?php
	}
	
	function post_form_toolbar_delete() {
		// I couldn't figure out a better way to hide the bar when it's not needed.
		?>
		<script type="text/javascript"><!--
			post_form = document.getElementById('bbp_reply_content');
			if (post_form==null) post_form = document.getElementById('bbp_topic_content');
			if (post_form==null)
				if (document.getElementById('post-toolbar') != null)
					document.getElementById('post-toolbar').parentNode.removeChild(document.getElementById('post-toolbar'));
			if (post_form != null) {
				k = 0;
				toolbars = [];
				divs = document.getElementsByTagName('div');
				for (var i = 0; i < divs.length; i++) {
					if (divs[i].hasAttribute('id')) {
						if (divs[i].getAttribute('id') == 'post-toolbar') {
							toolbars[k] = divs[i];
							k++;
						}
					}
				}
				for (var i = 0; i < toolbars.length-1; i++) {
					var throwAway = toolbars[i].parentNode.removeChild(toolbars[i]);
				}
			}
		//--></script>
		<?php
	}

	function post_form_toolbar_script() {
		?>
		<script type="text/javascript" src="<?php print site_url(); ?>/wp-content/plugins/bbpress-post-toolbar/toolbar.js"></script>
		<style type="text/css">/*<![CDATA[*/
			@import url( <?php print site_url(); ?>/wp-content/plugins/bbpress-post-toolbar/toolbar.css );
		/*]]>*/</style>
		<?php
	}
	
}

// Extend kses to allow <span>
if ( !CUSTOM_TAGS ) {
	$allowedtags['span'] = array(
			'style' => array());
	if ( get_option('bbp_5o1_toolbar_use_images') ) {
	$allowedtags['img'] = array(
			'src' => array (),
			'alt' => array (),
			'width' => array (),
			'class' => array (),
			'style' => array ());
	}
}
 ?>
