=== bbPress Post Toolbar ===
Contributors: master5o1
Donate link: http://master5o1.com/donate/
Tags: bbPress, bbPress 2.0, toolbar, youtube, smilies, smileys, emoticons, 5o1, master5o1
Requires at least: WordPress 3.1+ and bbPress 2.0+
Tested up to: 3.2.1
Stable tag: 0.4.1

Post toolbar for bbPress 2.0.

== Description ==

Post toolbar for bbPress 2.0.

* Toolbar is automatically shown, though it can be set to manual insertion.
* Enables embedding of images in a bbPress post (turn it on in the settings).
* Allows &lt;span style=""&gt; in a bbPress posts.
* Allows embedding of youtube videos using [youtube]http://...[/youtube] shortcode.

Has a weak ability to allow plugins to extend the toolbar.

You might also be interested in my other bbPress 2.0 related plugin: [bbPress Ignore User](http://wordpress.org/extend/plugins/bbpress-ignore-user/)

== Installation ==

1. Make sure you have bbPress 2.0 (or better) plugin activated.
1. Upload `bbpress-post-toolbar` folder to the `/wp-content/plugins/` directory
1. Move or copy the `smilies` folder to `/wp-content/` directory. (So it doesn't get changed on upgrades).
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the options in the bbPress Post Toolbar settings page.

If you choose to set the bar to manual insertion rather than automatic, then you will need to add this to your theme file, or where ever you might want to show the bar:

* `<?php do_action('bbp_post_toolbar_insertion'); ?>`


It is a good idea to copy or move the smilies directory included with this plugin to the wp-content directory.

== Frequently Asked Questions ==

= Could you explain how to use customised smilies? =

I have included a simple set of smilies with this plugin in the `bbpress-post-toolbar/smilies/` directory.  Changing the files will obviously change what the smilies look like.  But edit the package-config.php file inside this directory to change the code binding to a particular image.

I recommend that this folder is either copied or moved to the `/wp-content/` directory so that any customised smilies that you have added are not lost on an upgrade to this plugin.

== Screenshots ==

1. The toolbar displaying the opened Links panel.
1. The YouTube panel opened.
1. Toolbar options in WP Admin.

== Changelog ==

= 0.5.0-alpha =

* Just added some file-upload but haven't tested it much.  Comitting to 
trunk because calling it a day and leaving it where it is.

= 0.4.0 =

* Allowing the insertion of the bar set to manual, use `<?php do_action('bbp_post_toolbar_insertion'); ?>` in your theme file where ever you want the bar to appear.
* Allowed the Help panel to be customised.

= 0.3.3 =

* Programmatically determined what the plugin version is so that I can't forget to update the version in the About panel, etc.

= 0.3.2 =

* Added `/languages/bbpress-post-toolbar.pot` file to the plugin for translations to be done.
* Adding __() and _e() to allow for translations.
* Haha, turns out I forgot the version info in about plugin again. -_-

= 0.3.1 =

* Changing the plugin header to try and get the Active Versions pie 
chart working.

= 0.3.0 =

* Reorganised the plugin options page and added some notes about each option.  Suggestion to move `/smilies/`, etc.
* Allowed the option to have the master5o1 credit be linked back to my website.  Default = not linked.
* Made smilies directory preference be `wp-content/smilies/`, then fall back to `wp-content/plugins/bbpress-post-toolbar/smilies/`, then fall back to WordPress' default set.

= 0.2.1 =

* Accidentally forgot to increase the version that was displayed in the About panel on the toolbar.

= 0.2.0 =

* Add Button API is actually usable now.
* Allowed custom javascript functions to be run through the Add Button api so that adding a button is actually doable.

= 0.1.0 =

* First release.

== Upgrade Notices ==

= 0.3.3 =
You can actually ignore this update, it's just me getting some minor things done.

= 0.3.2 =
Added translation .pot file so that people can have custom translations.

== Other Notes ==

= To Do =

* Read up about translations and stuff.  Figure out how to have translations be possible.
* ???
* Relax and have a cup of hot chocolate.

= Custom Buttons =

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
