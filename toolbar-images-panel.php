<?php
/**
 Plugin Name: Toolbar Images Panel
 Plugin URI: http://wordpress.org/extend/plugins/bbpress-post-toolbar/
 Description: Allows images to be posted and uploaded into bbPress + WordPress.
 Version: 0.5.6
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

// Add panel entry to toolbar:
add_filter( 'bbp_5o1_toolbar_add_items' , array('bbp_5o1_images_panel', 'panel_entry'), 1 );

add_action( 'bbp_init' , array('bbp_5o1_images_panel', 'script_and_style') );

// Image Uploading from the bar:
if ( get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) ) {
	add_filter('query_vars',array('bbp_5o1_images_panel','fileupload_trigger'));
	add_action('template_redirect', array('bbp_5o1_images_panel','fileupload_trigger_check'));
	add_action( 'wp_footer' , array('bbp_5o1_images_panel', 'fileupload_start') );
}

class bbp_5o1_images_panel {

	function panel_entry($items) {
		$items[] = array( 'action' => 'switch_panel',
			 'inside_anchor' => '<img src="' . plugins_url( '/images', __FILE__ ) . '/image.png" title="Image" alt="Image" />',
			 'data' => '<div><span>Image URL:</span>
<input style="display:inline-block;width:300px;" type="text" id="image_url" value="" />
<input type="hidden" id="image_title" value="" />
<a class="toolbar-apply" onclick="insert_panel(\'image\');">Apply Image</a></div>
<div id="post-form-image-uploader"><noscript><p>Please enable JavaScript to use file uploader.</p></noscript></div>');
		return $items;
	}
	
	function script_and_style() {
		wp_register_script( 'bbp_5o1_post_toolbar_uploader_script', plugins_url('includes/fileuploader.js', __FILE__) );
		wp_register_style( 'bbp_5o1_post_toolbar_uploader_style', plugins_url('includes/fileuploader.css', __FILE__) );

		if ( get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) && ( is_user_logged_in() || get_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads' ) ) ) {
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
			if ( ! ( get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) && ( is_user_logged_in() || get_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads' ) ) ) ) {
				echo htmlspecialchars(json_encode(array("error"=>__("You are not permitted to upload images.", 'bbp_5o1_toolbar'))), ENT_NOQUOTES);
				exit;
			}
			require_once( dirname(__FILE__) . '/includes/fileuploader.php' );
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
		if ( ! ( get_option( 'bbp_5o1_toolbar_allow_image_uploads' ) && ( is_user_logged_in() || get_option( 'bbp_5o1_toolbar_allow_anonymous_image_uploads' ) ) ) )
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

if ( !CUSTOM_TAGS ) {
$allowedtags['img'] = array(
		'src' => array (),
		'alt' => array (),
		'width' => array (),
		'class' => array (),
		'style' => array ());
}

?>