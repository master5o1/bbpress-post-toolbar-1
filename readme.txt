=== bbPress Post Toolbar ===
Contributors: master5o1
Donate link: http://master5o1.com/donate/
Tags: bbPress, bbPress 2.0, toolbar, youtube, 5o1, master5o1
Requires at least: 3.1 or higher
Tested up to: 3.2
Stable tag: 0.2.1

Post toolbar for bbPress 2.0.

== Description ==

Post toolbar for bbPress 2.0.

* Enables embedding of images in a bbPress post (turn it on in the settings).
* Allows &lt;span style=""&gt; in a bbPress posts.
* Allows embedding of youtube videos using [youtube]http://...[/youtube] shortcode.

Has a weak ability to allow plugins to extend the toolbar.

This is my first upload to WordPress Extend :D

You might also be interested in my other bbPress 2.0 related plugin: [bbPress Ignore User](http://wordpress.org/extend/plugins/bbpress-ignore-user/)

== Installation ==

1. Make sure you have bbPress 2.0 (or better) plugin activated.
1. Upload `bbpress-post-toolbar` folder to the `/wp-content/plugins/` directory
1. Move or copy the `smilies` folder to `/wp-content/` directory. (So it doesn't get changed on upgrades).
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the options in the bbPress Post Toolbar settings page.

== Frequently Asked Questions ==

= Question =

Answer

== Screenshots ==

1. The toolbar displaying the opened Links panel.
1. The YouTube panel opened.
1. Toolbar options in WP Admin.

== Changelog ==

= 0.2 =

* Add Button API is actually usable now.
* Allowed custom javascript functions to be run through the Add Button api so that adding a button is actually doable.

= 0.1 =

* First release.

== Upgrade Notice ==

= 0.1 =

* None

== Custom Buttons ==

The following is about standard push buttons, not panel opening buttons.  To see how a panel opening button works just view the bbpress-post-toolbar.php source.

Adding custom buttons to the toolbar is done using by making a plugin and adding a filter to hook into the button.
My example below is how I added the Spoiler button to the toolbar, which is my modification of the [Tiny Spoiler](http://wordpress.org/extend/plugins/tiny-spoiler/) plugin.

Note: This isn't my only modification to Tiny Spoiler.  I had to get also build a function to parse the `[spoiler]` shortcode inside a bbPress post.

`function bbp_5o1_spoiler_add_to_toolbar($items) {
	$javascript = <<<JS
function(){ insert_shortcode('spoiler') }
JS;
	$items[] = array( 'action' => 'api_item',
		'inside_anchor' => '<img src="'. site_url() . '/wp-content/plugins/tiny-spoiler/spoiler_btn.png" title="Spoiler" alt="Spoiler" />',
		'data' => $javascript);
	return $items;
}
add_filter( 'bbp_5o1_toolbar_add_items' , 'bbp_5o1_spoiler_add_to_toolbar' );`

= Available JavaScript Functions =

Really, just look inside toolbar.js

* Insert an HTML tag: `insert_data('tag')`
	* (returns <tag></tag>, potentially wrapped around text)
* Insert a shortcode tag: `insert_shortcode('tag')`
	* (returns [tag][/tag], potentially wrapped around text)
* Insert a smiley: `insert_smiley(':)')`
* Insert a color: `insert_color('red')`
* Insert a size: `insert_size('5pt')`
* `testText(tag_s, tag_e)` can be used to try to wrap a start- and end-tag around selected text.  If there is text selected then the tag will be applied at the end of the post content wrapped around a single space.
