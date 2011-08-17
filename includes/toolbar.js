/**
 * Plugin Name: bbPress Post Toolbar
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
var post_toolbar_element_stack = new Array();
var post_toolbar_attribute_stack = new Array();
var post_toolbar_button_stack = new Array();
var post_toolbar_clicked_button = null;

function addCloseTagsToSubmit() {
	var submitButton = document.getElementById('bbp_reply_submit');
	if (submitButton==null) submitButton = document.getElementById('bbp_topic_submit');
	if (submitButton==null) return;
	onclick = submitButton.getAttribute('onclick');
	if (onclick == null) { onclick = ''; }
	onclick = 'closeTags(post_toolbar_element_stack);' + onclick;
	submitButton.setAttribute('onclick', onclick);
}

function get_post_form() {
	var post_form = document.getElementById('bbp_reply_content');
	if (post_form==null) post_form = document.getElementById('bbp_topic_content');
	return post_form;
}

function switch_panel(panel) {
	var post_form = get_post_form();
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
	var post_form = get_post_form();
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
	} else if (tag == 'video') {
		video_url = document.getElementById('video_url');
		link = '[video]' + video_url.value + '[/video]';
		post_form.value += link;
		video_url.value = "";
	}
	document.getElementById(toolbar_active_panel).style.display='none';
	toolbar_active_panel='';
}

function insert_data(tag) {
	var post_form = get_post_form();
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
	var post_form = get_post_form();
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	testText('[' + tag + ']','[/' + tag + ']');
}

function insert_smiley(smiley) {
	var post_form = get_post_form();
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	post_form.value += ' ' + smiley + ' ';
}

function insert_size(size) {
	var post_form = get_post_form();
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	insert_tag('<span style="font-size: ' + size + ';">','</span>');
}

function testTest(tag_s, tag_e) {
	insert_tag(tag_s, tag_e);
}

/////////////////////////////////////////////////////////////////////
// I'm trying to clean this up by making 'better' functions below: //
/////////////////////////////////////////////////////////////////////

function insert_tag(tag_s, tag_e) {
	var post_form = get_post_form();
	if (!formatText(tag_s,tag_e)) {
		post_form.value += tag_s + ' ' + tag_e;
	}
}

function insert_font(font) {
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	insert_tag('<span style="font-family: ' + font + ';">','</span>');
}

function insert_color(color) {
	if (toolbar_active_panel != '') { document.getElementById(toolbar_active_panel).style.display='none'; toolbar_active_panel=''; }
	insert_tag('<span style="color: ' + color + ';">','</span>');
}

function do_button(button_e, args, f) {
	post_toolbar_clicked_button = button_e;
	if (args.action == 'switch_panel') {
		var tagComplete = formatText('','');
		if (tagComplete && f() == 'links') {
			href = prompt('Link URL:', '');
			if (href != null && href != '')
				insertHTML(post_toolbar_element_stack, 'a', [['href',href]]);
		} else {
			switch_panel(args.panel);
		}
	}
	if (args.action == 'insert_data')
		insert_data(f());
	if (args.action == 'insert_shortcode')
		insert_shortcode(f());
	if (args.action == 'api_item')
		f(post_toolbar_element_stack);
	if (args.action == 'api_panel')
		f();
	
}

function insertHTML(stack, tag, attributes) {
	insertTag(stack,  '<', '>', tag, attributes);
}

function insertShortcode(stack, tag, attributes) {
	insertTag(stack, '[', ']', tag, attributes);
}

function insertTag(stack, start, end, tag, attributes) {
	var post_form = get_post_form();
	stack_atts = post_toolbar_attribute_stack;
	atts = '';
	for (i=0; i<attributes.length; i++) {
		atts += ' '+attributes[i][0]+'="'+attributes[i][1]+'"';
	}
	last_element = stack.pop();
	if (typeof last_element != 'undefined') {
		if (last_element == start+'/'+tag+end) {
			last_attribute = stack_atts.pop();
			if (typeof last_attribute != 'undefined') {
				if (last_attribute == atts) {
					post_form.value += last_element+' ';
					highlightCloseButton();
					return;
				} else {
					stack_atts.push(last_element);
					stack.push(last_element);
				}
			}
		} else {
			stack.push(last_element);
		}
	}
	var tagComplete = formatText(start+tag+atts+end,start+'/'+tag+end);
	if (!tagComplete) {
		post_form.value += ' '+start+tag+atts+end;
		stack.push(start+'/'+tag+end);
		stack_atts.push(atts);
		highlightOpenButton();
	}
}

function closeTags(stack) {
	var post_form = get_post_form();
	while(stack.length > 0) {
		post_form.value += stack.pop()+' ';
		post_toolbar_attribute_stack.pop();
		highlightCloseButton();
	}
	post_form.value += " ";
}

function highlightCloseButton() {
	var element = post_toolbar_button_stack.pop();
	if (element.innerHTML.substring(0,12) == '<sup>/</sup>')
		element.innerHTML = element.innerHTML.substring(12);
}

function highlightOpenButton() {
	post_toolbar_button_stack.push(post_toolbar_clicked_button);
	post_toolbar_clicked_button.innerHTML = '<sup>/</sup>' + post_toolbar_clicked_button.innerHTML;
}

function formatText(tagstart,tagend) {
	var post_form = get_post_form();
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
