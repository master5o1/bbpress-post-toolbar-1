<?php
/**
 Plugin Name: bbPress Post Toolbar
 Plugin URI: http://wordpress.org/extend/plugins/bbpress-post-toolbar/
 Description: Post toolbar for click-to-insert HTML elements, as well as [youtube][/youtube] shortcode handling.
 Version: 0.5.1
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
add_action('admin_menu', array('bbp_5o1_toolbar', 'admin_add_config_link') );
add_filter( 'plugin_action_links', array('bbp_5o1_toolbar', 'admin_add_settings_link') , 10, 2 );



if ( !get_option( 'bbp_5o1_toolbar_manual_insertion' ) )
	add_action( 'wp_footer' , array('bbp_5o1_toolbar', 'post_form_toolbar_delete') );




if ( get_option('bbp_5o1_toolbar_use_custom_smilies') ) {
	add_filter( 'smilies_src', array('bbp_5o1_toolbar', 'switch_smileys_url'), 0, 3 );
	if ( file_exists(WP_CONTENT_DIR . '/smilies/package-config.php') )
		require_once(WP_CONTENT_DIR . '/smilies/package-config.php');
	elseif ( file_exists(dirname(__FILE__) . '/smilies/package-config.php') )
		require_once(dirname(__FILE__) . '/smilies/package-config.php');
}

// Image Uploading from the bar:
if ( ( get_option( 'bbp_5o1_toolbar_use_images' ) && get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) ) ) {
	add_filter('query_vars',array('bbp_5o1_toolbar','fileupload_trigger'));
	add_action('template_redirect', array('bbp_5o1_toolbar','fileupload_trigger_check'));
	add_action( 'wp_footer' , array('bbp_5o1_toolbar', 'fileupload_start') );
}

// bbPress 2.0 Actions & Filters:
add_action( 'bbp_init' , array('bbp_5o1_toolbar', 'script_and_style') );
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
		add_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads', false, '', 'yes' );
		add_option( 'bbp_5o1_toolbar_allow_image_uploads', true, '', 'yes' );
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
		delete_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads' );
		delete_option( 'bbp_5o1_toolbar_allow_image_uploads' );
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
		$anonymous_image_uploads = false;
		if ( get_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads' ) )
			$anonymous_image_uploads = true;
		$image_uploads = false;
		if ( get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) )
			$image_uploads = true;
		?>
		<div class="wrap">
			<div style="max-width: 700px;">
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
						<div style="margin: 0 50px;"><small><?php printf( __('Note: It is recommended that the %s directory is copied or moved to the %s directory.  This is to prevent any custom smilies that you may have added from being lost on an upgrade to this plugin.', 'bbp_5o1_toolbar'), '<code>' . dirname(__FILE__) . '/smilies/</code>', '<code>' . WP_CONTENT_DIR . '/smilies/</code>'); ?></small></div>
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
					<div style="margin: 0 0 0 10px; padding: 0 0 0 10px; border-left: solid 3px #eee;">
						<p>
							<strong><?php _e('Allow users to upload images?', 'bbp_5o1_toolbar'); ?></strong><br /><br />
							<span style="margin: 0 50px;">
							<label style="display: inline-block; width: 150px;"><input name="bbp_5o1_toolbar_allow_image_uploads" type="radio" value="1" <?php print (($image_uploads) ? 'checked="checked"' : '' ) ?> /> <?php _e('Yes (default)', 'bbp_5o1_toolbar'); ?></label>
							<label><input name="bbp_5o1_toolbar_allow_image_uploads" type="radio" value="0" <?php print ((!$image_uploads) ? 
	'checked="checked"' : '' ) ?> /> <?php _e('No', 'bbp_5o1_toolbar'); ?></label>
							</span><br />
							<div style="margin: 0 50px;">
								<span><strong><?php _e('Upload Directory', 'bbp_5o1_toolbar'); ?>:</strong> <code><?php $directory = wp_upload_dir(); print $directory['path'].'/'; ?></code></span><br />
								<small><?php _e('Note: This is only relevant if you allow images to be posted in the forum.', 'bbp_5o1_toolbar'); ?></small>
							</div>
						</p>
						<div style="margin: 0 0 0 10px; padding: 0 0 0 10px; border-left: solid 3px #eee;">
							<p>
								<strong><?php _e('Allow unregistered users to upload images?', 'bbp_5o1_toolbar'); ?></strong><br /><br />
								<span style="margin: 0 50px;">
								<label style="display: inline-block; width: 150px;"><input name="bbp_5o1_toolbar_allow_anonymous_image_uploads" type="radio" value="1" <?php print (($anonymous_image_uploads) ? 'checked="checked"' : '' ) ?> /> <?php _e('Yes', 'bbp_5o1_toolbar'); ?></label>
								<label><input name="bbp_5o1_toolbar_allow_anonymous_image_uploads" type="radio" value="0" <?php print ((!$anonymous_image_uploads) ? 
		'checked="checked"' : '' ) ?> /> <?php _e('No (default)', 'bbp_5o1_toolbar'); ?></label>
								</span><br />
								<div style="margin: 0 50px;"><small><?php _e('Note: This is only be relevant if you allow unregistered users to post replies in the forum, and allow images to be uploaded and posted in the forum.', 'bbp_5o1_toolbar'); ?></small></div>
							</p>
						</div>
					</div>
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
		if ( !is_admin() || !current_user_can('manage_options') )
			return;
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

			if ($_POST['bbp_5o1_toolbar_allow_anonymous_image_uploads'] == 1)
				update_option('bbp_5o1_toolbar_allow_anonymous_image_uploads', true);
			elseif ($_POST['bbp_5o1_toolbar_allow_anonymous_image_uploads'] == 0)
				update_option('bbp_5o1_toolbar_allow_anonymous_image_uploads', false);	
				
			if ($_POST['bbp_5o1_toolbar_allow_image_uploads'] == 1)
				update_option('bbp_5o1_toolbar_allow_image_uploads', true);
			elseif ($_POST['bbp_5o1_toolbar_allow_image_uploads'] == 0)
				update_option('bbp_5o1_toolbar_allow_image_uploads', false);				
			

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
		if ( file_exists(WP_CONTENT_DIR . '/smilies/package-config.php') )
			return content_url( '/smilies/' . $img );
		elseif ( file_exists(dirname(__FILE__) . '/smilies/package-config.php') )
			return plugins_url( '/smilies/' . $img, __FILE__ ); 
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
			$data = '<div><span>Image URL:</span>
<input style="display:inline-block;width:300px;" type="text" id="image_url" value="" />
<input type="hidden" id="image_title" value="" />
<a class="toolbar-apply" onclick="insert_panel(\'image\');">Apply Image</a></div>
<div id="post-form-image-uploader"><noscript><p>Please enable JavaScript to use file uploader.</p></noscript></div>';
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
	
	function post_form_toolbar_bar($param = null) {
		global $wpsmiliestrans;
		$items = array();
		$items[] = array( 'action' => 'insert_data',
						 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/bold.png" title="Bold" alt="Bold" />',
						 'data' => 'strong');
		$items[] = array( 'action' => 'insert_data',
						 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/italic.png" title="Italics" alt="Italics" />',
						 'data' => 'em');
		$items[] = array( 'action' => 'insert_data',
						 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/underline.png" title="Underline" alt="Underline" />',
						 'data' => 'underline');
		$items[] = array( 'action' => 'insert_data',
						 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/strikethrough.png" title="Strike through" alt="Strike through" />',
						 'data' => 'strike');
		$items[] = array( 'action' => 'switch_panel',
						 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/fontcolor.png" title="Color" alt="Color" />',
						 'data' => bbp_5o1_toolbar::switch_panel('color'));
		$items[] = array( 'action' => 'switch_panel',
						 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/font.png" title="Size" alt="Size" />',
						 'data' => bbp_5o1_toolbar::switch_panel('size'));
		if ( get_option( 'use_smilies' ) ) {
			$items[] = array( 'action' => 'switch_panel',
							  'inside_anchor' => str_replace("class='wp-smiley' ", '', convert_smilies(':)')),
							  'data' => bbp_5o1_toolbar::switch_panel('smiley'));
		}
		$items[] = array( 'action' => 'switch_panel',
						 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/link.png" title="Link" alt="Link" />',
						 'data' => bbp_5o1_toolbar::switch_panel('link'));
		if ( get_option('bbp_5o1_toolbar_use_images')) {
			$items[] = array( 'action' => 'switch_panel',
							 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/image.png" title="Image" alt="Image" />',
							 'data' => bbp_5o1_toolbar::switch_panel('image'));
		}
		if ( get_option('bbp_5o1_toolbar_use_youtube') ) {
			$items[] = array( 'action' => 'switch_panel',
							  'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/youtube.png" title="Youtube" alt="Youtube" />',
							  'data' => bbp_5o1_toolbar::switch_panel('youtube'));
		}
		$items[] = array( 'action' => 'insert_data',
						  'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/quote.png" title="Quote" alt="Quote" />',
						  'data' => 'blockquote');
		$items[] = array( 'action' => 'insert_data',
						  'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/code.png" title="Code" alt="Code" />',
						  'data' => 'code');
		if ( get_option('bbp_5o1_toolbar_use_textalign') ) {
			$items[] = array( 'action' => 'insert_data',
							 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/fontleft.png" title="Left Align" alt="Left Align" />',
							 'data' => 'fontleft');
			$items[] = array( 'action' => 'insert_data',
							 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/fontcenter.png" title="Center Align" alt="Center Align" />',
							 'data' => 'fontcenter');
			$items[] = array( 'action' => 'insert_data',
							 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/fontjustify.png" title="Justified Align" alt="Justified Align" />',
							 'data' => 'fontjustify');
			$items[] = array( 'action' => 'insert_data',
							 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/fontright.png" title="Right Align" alt="Right Align" />',
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
			<?php if ( ! get_option('bbp_5o1_toolbar_custom_help') ) : ?>
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
		return $param;
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

	function script_and_style() {
		wp_register_script( 'bbp_5o1_post_toolbar_uploader_script', plugins_url('fileuploader.js', __FILE__) );
		wp_register_style( 'bbp_5o1_post_toolbar_uploader_style', plugins_url('fileuploader.css', __FILE__) );
		wp_register_script( 'bbp_5o1_post_toolbar_script', plugins_url('toolbar.js', __FILE__) );
		wp_register_style( 'bbp_5o1_post_toolbar_style', plugins_url('toolbar.css', __FILE__) );
		





		wp_enqueue_script( 'bbp_5o1_post_toolbar_script' );
		wp_enqueue_style( 'bbp_5o1_post_toolbar_style' );
		if ( ( get_option( 'bbp_5o1_toolbar_use_images' ) && get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) ) && ( is_user_logged_in() || get_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads' ) ) ) {
			wp_enqueue_script( 'bbp_5o1_post_toolbar_uploader_script' );
			wp_enqueue_style( 'bbp_5o1_post_toolbar_uploader_style' );
		}







	}
	
	function fileupload_trigger($vars) {
		$vars[] = 'postform_fileupload';
		return $vars;
	}
	
	function fileupload_trigger_check() {
		if ( intval(get_query_var('postform_fileupload')) == 1 ) {
			if ( ! ( ( get_option( 'bbp_5o1_toolbar_use_images' ) && get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) ) && ( is_user_logged_in() || get_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads' ) ) ) ) {
				echo htmlspecialchars(json_encode(array("error"=>__("You are not permitted to upload images.", 'bbp_5o1_toolbar'))), ENT_NOQUOTES);
				exit;
			}
			require_once( dirname(__FILE__) . '/fileuploader.php' );
			// list of valid extensions, ex. array("jpeg", "xml", "bmp")
			$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
			// Because using Extensions only is very bad.
			$allowedMimes = array(IMAGETYPE_JPEG, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);
			// max file size in bytes
			$sizeLimit = 5 * 1024 * 1024;
			$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
			$directory = wp_upload_dir();
			$result = $uploader->handleUpload( $directory['path'].'/' );
			$mime = exif_imagetype($result['file']);
			if ( !$mime || ! in_array($mime, $allowedMimes) ) {
				$deleted = unlink($result['file']);
				echo htmlspecialchars(json_encode(array("error"=>__("Disallowed file type.", 'bbp_5o1_toolbar'))), ENT_NOQUOTES);
				exit;
			}
			// Construct the attachment array
			$attachment = array(
				'post_mime_type' => $mime ? image_type_to_mime_type($mime) : '',
				'guid' => $directory['url'] . '/' . $result['filename'],
				'post_parent' => 0,
				'post_title' => $result['name'],
				'post_content' => 'Image uploaded for a forum topic or reply.',
			);
			
			// Save the data
			$id = wp_insert_attachment($attachment, $result['file'], 0);
			$result['id'] = $id;
			$result['attachment'] = $attachment;
			
			$result = array(
				"success" => true,
				"file" => $attachment['guid']
			);
			echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
			exit;
		}
	}
	
	function fileupload_start() {
		if ( ! ( ( get_option( 'bbp_5o1_toolbar_use_images' ) && get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) ) && ( is_user_logged_in() || get_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads' ) ) ) )
			return;
		?>
		<script type="text/javascript">
		function createUploader() {
			var uploader = new qq.FileUploader({
				element: document.getElementById('post-form-image-uploader'),
				action: '<?php print get_site_url() . '/?postform_fileupload=1'; ?>',
				allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],        
				sizeLimit: 5*1024*1024, // max size   
				onComplete: function(id, fileName, responseJSON){
					if (responseJSON.success != true) return
					post_form = document.getElementById('bbp_reply_content');
					if (post_form==null) post_form = document.getElementById('bbp_topic_content');
					post_form.value += ' <img src="' + responseJSON.file + '" alt="" /> '
				},
			});
		}
		window.onload = createUploader;
		</script>
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
