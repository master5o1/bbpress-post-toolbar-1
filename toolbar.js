/**
 * Plugin Name: bbPress Post Toolbar
 * Plugin URI: http://wordpress.org/extend/plugins/bbpress-post-toolbar/
 * Description: Post toolbar for click-to-insert HTML.
 * Version: 0.2
 * Author: master5o1
 * Author URI: http://master5o1.com
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

var post_form = document.getElementById('bbp_reply_content');
if (post_form==null) post_form = document.getElementById('bbp_topic_content');
var toolbar_active_panel = "";

function switch_panel(panel) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; }
	if (toolbar_active_panel == panel) {
		document.getElementById(toolbar_active_panel).style.display='none';
		toolbar_active_panel='';
		return;
	}
	document.getElementById(panel).style.display='block';
	toolbar_active_panel = panel;
}

function insert_panel(tag) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	if (tag == 'link') {
		link_name = document.getElementById('link_name');
		link_url = document.getElementById('link_url');
		if (link_name.value != "") {
			link = '<a href="' + link_url.value + '">' + link_name.value + '</a>';
			post_form.value += link;
		} else if (!formatText('<a href="' + link_url.value + '">','</a>')) {
			link = ' ' + link_url.value + ' ';
			post_form.value += link;
		}
		link_url.value = "";
		link_name.value = "";
	} else if (tag == 'image') {
		image_title = document.getElementById('image_title');
		image_url = document.getElementById('image_url');
		link = '<img src="' + image_url.value + '" alt="' + image_title.value + '" /'+'>';
		post_form.value += " " + link + " ";
		image_url.value = "";
		image_title.value = "";
	} else if (tag == 'youtube') {
		youtube_url = document.getElementById('youtube_url');
		link = '[youtube]' + youtube_url.value + '[/youtube]';
		post_form.value += link;
		youtube_url.value = "";
	}
	document.getElementById(toolbar_active_panel).style.display='none';
	toolbar_active_panel='';
}

function insert_data(tag) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	if (tag == 'underline') {
		testText('<span style="text-decoration: underline;">','</span>');	
		return;
	} else if (tag == 'fontleft') {
		testText('<span style="text-align: left;">','</span>');	
		return;
	} else if (tag == 'fontright') {
		testText('<span style="text-align: right;">','</span>');	
		return;
	} else if (tag == 'fontcenter') {
		testText('<span style="text-align: center;">','</span>');	
		return;
	} else if (tag == 'fontjustify') {
		testText('<span style="text-align: justify;">','</span>');	
		return;
	} else if (tag == 'strike') {
		testText('<span style="text-decoration: line-through;">','</span>');	
		return;
	}
	testText('<' + tag + '>','</' + tag + '>');
}

function insert_shortcode(tag) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	testText('[' + tag + ']','[/' + tag + ']');
}

function insert_smiley(smiley) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	post_form.value += ' ' + smiley + ' ';
}

function insert_color(color) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	testText('<span style="color: ' + color + ';">','</span>');
}

function insert_size(size) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	testText('<span style="font-size: ' + size + ';">','</span>');
}

function testText(tag_s, tag_e) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	if (!formatText(tag_s,tag_e)) {
		post_form.value += tag_s + ' ' + tag_e;
	}
}

function do_button(args, f) {
	if (args.action == 'switch_panel')
		switch_panel(args.panel);
	if (args.action == 'insert_data')
		insert_data(f());
	if (args.action == 'insert_shortcode')
		insert_shortcode(f());
	if (args.action == 'api_item')
		f();
	
}

function formatText(tagstart,tagend) {
	post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	el = post_form;
	if (el.setSelectionRange) {
		selectedText = el.value.substring(el.selectionStart,el.selectionEnd);
		if (selectedText == "") { return false; }
		el.value = el.value.substring(0,el.selectionStart) + tagstart + selectedText + tagend + el.value.substring(el.selectionEnd,el.value.length);
		return true;
	}
	else {
		var selectedText = document.selection.createRange().text;
		if (selectedText != "") { 
			var newText = tagstart + selectedText + tagend; 
			document.selection.createRange().text = newText; 
			return true;
		} else { return false; }
	}
}
