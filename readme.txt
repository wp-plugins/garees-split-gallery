=== Garee's Split Gallery ===
Contributors: garee
Donate link: https://flattr.com/donation/give/to/garee/
Tags: image, random, picture
Requires at least: 3.0.0
Tested up to: 3.2.1
Stable tag: trunk

Easily split WordPress-galleries without any fancy css or js.

== Description ==
*Garee's Split Gallery* allows you to number the images to be shown (e.g. show='1-3′) and uses the native WordPress-gallery-shortcode to do the rest. Break your galleries apart - but don't insert hardcoded img-tags directly. In addition you are able to set default-values for the gallery-shortcode. E.g. if you'd like your gallery-items to link to the image-file directly, you can set that default in the settings-page and no longer need to insert `file="link"` into the shortcode.

Main-advantages:

* easily split your galleries
* show only images of a certain filetype
* set default-values
* uses native Wordpress-gallery (no additional code)

Just insert the shortcode anywhere on your blog: `[split_gallery show='1-3']`

With the exception of 'include' and 'exclude' you can use all the official shortcode-attributes of the [native-gallery-shortcode](http://codex.wordpress.org/Gallery_Shortcode) to further customize the gallery:

`[split_gallery id="2144" show="4-6" order="DESC" orderby="ID" size="medium" icontag="span" link="file"]`


== Installation ==

1. Download the plugin and unzip it
1. Upload the entire folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Goto the new plugin-page 'Garee's Split Gallery' to get your shortcode
1. Goto the new settings-page 'Garee's Split Gallery' to set default-values
1. Place the shortcode anywhere in your blog

== Screenshots ==

1. Easily split your galleries!
2. Description-page with the shortcode
3. Settings-page with default-values for the gallery-shortcode

== Frequently Asked Questions ==


== Changelog ==

= 0.5 =
* first official release
* bugfixes, documentation
* remove settings on delete

= 0.4 =
* settings-page with default-values

= 0.3 =
* preserve shortcode-attributes for the gallery

== Upgrade Notice ==
