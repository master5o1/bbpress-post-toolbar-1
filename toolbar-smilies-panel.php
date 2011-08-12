<?php
/**
 Plugin Name: Toolbar Smilies Panel
 Plugin URI: http://wordpress.org/extend/plugins/bbpress-post-toolbar/
 Description: Providing the custom smilies panel and smilies handling for bbPress Post Toolbar
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
add_filter( 'bbp_5o1_toolbar_add_items' , array('bbp_5o1_smilies_panel', 'panel_entry'), 3 );

if ( get_option('bbp_5o1_toolbar_use_custom_smilies') ) {
	add_filter( 'smilies_src', array('bbp_5o1_smilies_panel', 'switch_url'), 0, 3 );
	if ( file_exists(WP_CONTENT_DIR . '/smilies/package-config.php') )
		require_once(WP_CONTENT_DIR . '/smilies/package-config.php');
	elseif ( file_exists(dirname(__FILE__) . '/smilies/package-config.php') )
		require_once(dirname(__FILE__) . '/smilies/package-config.php');
}

class bbp_5o1_smilies_panel {

	function switch_url($link, $img, $url) {
		if ( file_exists(WP_CONTENT_DIR . '/smilies/package-config.php') )
			return content_url( '/smilies/' . $img );
		elseif ( file_exists(dirname(__FILE__) . '/smilies/package-config.php') )
			return plugins_url( '/smilies/' . $img, __FILE__ ); 
		return $link;	
	}

	function panel_entry($items) {
		global $wpsmiliestrans;
		if ( get_option( 'use_smilies' ) ) {
			$item['action'] = 'switch_panel';
			$item['inside_anchor'] = str_replace( "class='wp-smiley' ", '', convert_smilies(':)') );
			$item['data'] = "";
			foreach ($wpsmiliestrans as $code => $name) {
				$js = "insert_smiley('${code}');";
				$item['data'] .= '<a class="smiley" onclick="' . $js . '">' . str_replace("class='wp-smiley' ", '', convert_smilies($code)) . '</a>';
			}
			$items[] = $item;
		}
		return $items;
	}	
}