<?php
/*
Plugin Name: Garee's Split Gallery
Plugin URI: http://www.garee.ch/wordpress/garees-split-gallery/
Description: Garee's Split Gallery allows you to number the images to be shown (e.g. show='1-3′) and uses the native WordPress-gallery-shortcode to do the rest. In addition you are able to set default-values for the gallery-shortcode.
Version: 0.5
Author: Sebastian Forster
Author URI: http://www.garee.ch/
License: GPL2
*/

/*  Copyright 2011  Sebastian Forster  (email : garee@gmx.net)

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

if(!defined('GAREE_FLATTRSCRIPT')) {
	define('GAREE_FLATTRSCRIPT', 'http://www.garee.ch/js/flattr/flattr.js');
}

/**
 * Main-Function: Get the IDs and run gallery-shortcode
 **/ 
function garees_split_gallery($atts, $content = "") {

	// insert default-values from settings
	$options = get_option('garees_split_gallery_settings');
	
	foreach($options as $key => $value) {
		if ($value != "" && $value != "default" && !isset($atts[$key]))
			$atts[$key] = $value;
	}
	
	extract(shortcode_atts(array(
	  'show' => null,
	  'id' => get_the_ID(),
	  'orderby' => "menu_order",
	  'order' => "ASC",
	  'filetype' => "",
	), $atts));	
	
	if ($filetype == "jpg")
		$filetype = "jpeg";
	
	$shortcode = "[gallery";
	
	if ($show) {                  // show-attribute is set
		
		$shortcode .= " include='";
		
		$chunks = explode(",", $show);         // break into smaller chunks (comma-separated)
		$first = true;
		
		foreach($chunks as $chunk) {
		
			
			$range = explode("-", $chunk);        // further split to get start and end of range
			 
			$start = intval(trim($range[0]));      // trim spaces
			if (count($range) > 1) 
				$end = intval(trim($range[1]));    // trim spaces
			else 
				$end = $start;
			$count = $end-$start+1;
			unset($range);
			
			// prepare arguments for query
			$args = array(
			   'post_parent' => $id,
			   'post_type' => 'attachment',
			   'post_mime_type' => 'image/'.$filetype,
			   'numberposts' => $count,
			   'offset' => $start-1,
			   'orderby' => $orderby,
			   'order' => $order,
			);
			$images = get_posts($args);
			unset($args);			
																		
			foreach ($images as $image) {
				if ($first) {
					$shortcode .= $image->ID;       // add image-id to include-attribute (first image)
					$first = false;
				} else {
					$shortcode .= ",".$image->ID;   // add image-id to include-attribute
				}
			}
				
			
		}	
		$shortcode .= "'";  // end includes-listing
		
		// exclude the following attributes from gallery-shortcode
		$forget = array('include', 'exclude', 'show', 'filetype');
		// include other attributes in gallery-shortcode
		foreach ($atts as $key => $value) {
			if (!in_array($key, $forget))
				$shortcode .= " ".$key."='".$value."'";
		}	
	
			
	} else if ($filetype != "") {        // no show-attribute, but filetype set
	
		$shortcode .= " include='";
		$first = true;
		
		// prepare arguments for query
		$args = array(
		   'post_parent' => $id,
		   'post_type' => 'attachment',
		   'post_mime_type' => 'image/'.$filetype,
		   'numberposts' => -1,
		   'orderby' => $orderby,
		   'order' => $order,
		);
		$images = get_posts($args);
		unset($args);			
																	
		foreach ($images as $image) {
			if ($first) {
				$shortcode .= $image->ID;       // add image-id to include-attribute (first image)
				$first = false;
			} else {
				$shortcode .= ",".$image->ID;   // add image-id to include-attribute
			}
		}
					
		$shortcode .= "'";  // end includes-listing	
		
		// exclude the following attributes from gallery-shortcode
		$forget = array('include', 'exclude', 'show', 'filetype');
		// include other attributes in gallery-shortcode
		foreach ($atts as $key => $value) {
			if (!in_array($key, $forget))
				$shortcode .= " ".$key."='".$value."'";
		}
				
	} else {      // show- and filetype-attribute are missing: just call gallery-shortcode
		
		// exclude the following attributes from gallery-shortcode
		$forget = array('show', 'filetype');
		// include other attributes in gallery-shortcode
		foreach ($atts as $key => $value) {
			if (!in_array($key, $forget))
				$shortcode .= " ".$key."='".$value."'";
		}
		
	}

	$shortcode .= "]";   // terminate shortcode
	//return $shortcode;
	return do_shortcode($shortcode);
	
}


/**
 * Register the Plugin-Description and -Settings-Page
 **/
function garees_split_gallery_menu() {
	add_plugins_page("Garee's Split Gallery", "Garee's Split Gallery", 'read', 'garees_split_gallery', 'garees_split_gallery_show_menu');
	add_options_page("Garee's Split Gallery", "Garee's Split Gallery", "manage_options", "garees_split_gallery", "garees_split_gallery_options_page");
	
}

/**
 * Include CSS- and JS-File in the header
 **/ 
function garees_split_gallery_head() {
		
	if(is_admin()) {
	
		// load admin css
		if(!defined('GAREE_ADMINCSS_IS_LOADED')) {
			echo '<link rel="stylesheet" id="garees-admin-css"  href="' . plugins_url('garee_admin.css', __FILE__) . '" type="text/css" media="all" />'. "\n";
			define('GAREE_ADMINCSS_IS_LOADED', true);
		}		
		
		// Javascript für Flattr einfügen
		if(!defined('GAREE_FLATTRSCRIPT_IS_LOADED')) {
			echo '<script type="text/javascript" src="' . GAREE_FLATTRSCRIPT . '"></script>';
			define('GAREE_FLATTRSCRIPT_IS_LOADED', true);
		}
	}
}

/**
 * Insert a Description and Settings-Link on the plugin-overview
 **/    
function garees_split_gallery_plugin_actions( $links, $file ){
	$this_plugin = plugin_basename(__FILE__);
	
	if ( $file == $this_plugin ){
		$settings_link = '<a href="plugins.php?page=garees_split_gallery">' . __('Description') . '</a>';
		array_unshift( $links, $settings_link );
		$description_link = '<a href="options-general.php?page=garees_split_gallery">' . __('Settings') . '</a>';
		array_unshift( $links, $description_link );
	}
	return $links;
}

/**
 * Generate GareeBox
 **/
function gareeBoxSplitGallery() {
?>

<div id="gareeBox"> <small>If you like Garee's Split Gallery plugin, you can buy me a coffee!<br />
  </small><br />
  <a class="FlattrButton" style="display:none;" href="http://www.garee.ch/wordpress/garees-split-gallery/"></a>
  <noscript>
  <a href="http://flattr.com/thing/435525/Wordpress-plugin-Garees-Split-Gallery" target="_blank"> <img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
  </noscript>
  <br />
  or<br />
  <a href="https://flattr.com/donation/give/to/garee" title="Donate (via Flattr)" id="flattrDonate" target="_blank"></a>
  or<br />
 <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>  
</div>
<?php
}

/**
 * Generate Description-Page for the Admin
 **/
function garees_split_gallery_show_menu() {
	gareeBoxSplitGallery();
?>
<div id='gareeMain'>
  <h1>Garee's Split Gallery</h1>
  Easily split WordPress-galleries without any fancy css or js. This plugin allows you to number the images to be shown (e.g. show='1-3') and uses the native WordPress-gallery-shortcode to do the rest.
  <h2>Description | <a href="options-general.php?page=garees_split_gallery">Settings</a></h2>
  <h3>Usage</h3>
  To show just the first 3 pictures of your gallery just insert:
  <pre>[split_gallery show='1-3']</pre>
  The show-attribute allows more complex notation:
  <pre>[split_gallery show='3,4-6,8-10,12,14-16']</pre>
  There's even another way to filter the pictures shown in your gallery &ndash; by filetype:
  <pre>[split_gallery filetype='jpg']</pre>
  With the exception of 'include' and 'exclude' all other attributes of the original <a href="http://codex.wordpress.org/Gallery_Shortcode" title="gallery-shortcode" target="_blank">Wordpress-gallery-shortcode</a> are preserved.
  <pre>[split_gallery id="2144" show="4-6" order="DESC" orderby="ID" size="medium" icontag="span" link="file"]</pre>
  
<h3>Settings</h3>
The plugin-settings-page allows default-values to be defined for the gallery-shorcode attributes. E.g. if you prefer your gallery-links to link to the image-file instead of its permalink, you can set default value for link="file" and all split_gallery-shortcodes will behave just like you had set each's option link="file".

<h3>How it works</h3>
The final output is generated by the originial Wordpress-gallery -- my plugin just translates 'show' and 'filetype' into a list of IDs for the gallery-shortcode to use. So if you enter the following shortcode
<pre>[split_gallery show='1-3' orderby='title']</pre>
my plugin gets the IDs of the first 3 images attached to the current post or page. Lets assume there's a default-setting for link="file". Then my plugin inserts this attribute as well and finally calls:
<pre>[gallery include='234,213,239' orderby='title' link='file']</pre>
where 234, 213 and 239 are the IDs of the first 3 pictures of the gallery.
</div>
<?php
}

// add shortcuts
add_shortcode( 'split_gallery', 'garees_split_gallery' );

// actions for admins
if(is_admin()) {
	add_action('admin_menu', 'garees_split_gallery_menu');
	add_action('admin_head', 'garees_split_gallery_head');
	add_action('plugin_action_links','garees_split_gallery_plugin_actions',10, 2);
	add_action('admin_init', 'garees_split_gallery_init');
}

/**
 * Generate Settings-Page for the Admin
 **/
function garees_split_gallery_options_page() {
	gareeBoxSplitGallery();
?>
<div id='gareeMain'>
  <h1>Garee's Split Gallery</h1>
  Easily split WordPress-galleries without any fancy css or js. This plugin allows you to number the images to be shown (e.g. show='1-3') and uses the native WordPress-gallery-shortcode to do the rest.
  <h2><a href="plugins.php?page=garees_split_gallery">Description</a> | Settings</h2>
  <form action="options.php" method="post">
    <?php
settings_fields('garees_split_gallery_settings');
do_settings_sections('garees_split_gallery');
do_settings_sections('garees_split_gallery_query');
do_settings_sections('garees_split_gallery_xhtml');
?>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"/>
    </p>
  </form>
</div>
<?php
}

/**
 * init the plugin: register settings
 **/
function garees_split_gallery_init() {
	// register the settings
	register_setting( 'garees_split_gallery_settings', 'garees_split_gallery_settings');
	
	add_settings_section('plugin_main', 'General Settings', 'plugin_section_main', 'garees_split_gallery');
	
	add_settings_field('link', 'link="file"', 'plugin_setting_link', 'garees_split_gallery', 'plugin_main');
	add_settings_field('size', 'size', 'plugin_setting_size', 'garees_split_gallery', 'plugin_main');	
	add_settings_field('columns', 'columns', 'plugin_setting_columns', 'garees_split_gallery', 'plugin_main');	
	
	add_settings_section('plugin_main', 'Query-Settings', 'plugin_section_query', 'garees_split_gallery_query');
	
	add_settings_field('orderby', 'orderby', 'plugin_setting_orderby', 'garees_split_gallery_query', 'plugin_main');	
	add_settings_field('order', 'order', 'plugin_setting_order', 'garees_split_gallery_query', 'plugin_main');	
	
	add_settings_section('plugin_main', 'XHTML-Settings', 'plugin_section_xhtml', 'garees_split_gallery_xhtml');
	
	add_settings_field('itemtag', 'itemtag', 'plugin_setting_itemtag', 'garees_split_gallery_xhtml', 'plugin_main');	
	add_settings_field('icontag', 'icontag', 'plugin_setting_icontag', 'garees_split_gallery_xhtml', 'plugin_main');	
	add_settings_field('captiontag', 'captiontag', 'plugin_setting_captiontag', 'garees_split_gallery_xhtml', 'plugin_main');	

}
 

function plugin_section_main() {
	echo '<p>You can select default-values for the shortcode-attributes to be passed on to the gallery-shortcode. For more information about the values check out the <a href="http://codex.wordpress.org/Gallery_Shortcode" target="_blank" title="gallery shortcode">gallery-shortcode-syntax on Wordpress</a>. All these settings will apply to every split-gallery-shortcode inserted unless the specific shortcode states other values for these options!</p>';
	}
	
function plugin_setting_link() {
	$options = get_option('garees_split_gallery_settings');
	echo '<input name="garees_split_gallery_settings[link]" id="garees_split_gallery_settings[link]" type="checkbox" value="file" class="code" ' . checked( "file", $options['link'], false ) . " /> <span class='description'>If set each image will link to the image file. The default value (not set) links to the attachment's permalink</span>";
}

function plugin_setting_size() {
	$options = get_option('garees_split_gallery_settings');	
	echo '<label><input type="radio" name="garees_split_gallery_settings[size]" value="default" id="garees_split_gallery_settings[size]_0" ' . checked( "default", $options['size'], false ) . ' /> default</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[size]" value="thumbnail" id="garees_split_gallery_settings[size]_1" ' . checked( "thumbnail", $options['size'], false ) . ' /> thumbnail</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[size]" value="medium" id="garees_split_gallery_settings[size]_2" ' . checked( "medium", $options['size'], false ) . ' /> medium</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[size]" value="large" id="garees_split_gallery_settings[size]_3" ' . checked( "large", $options['size'], false ) . ' /> large</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[size]" value="full" id="garees_split_gallery_settings[size]_4" ' . checked( "full", $options['size'], false ) . ' /> full</label><br />';
	echo '<span class="description">specify the image size to use for the thumbnail display. Valid values include "thumbnail", "medium", "large" and "full". The default is "thumbnail". The size of the images for "thumbnail", "medium" and "large" can be configured in WordPress admin panel under Settings > Media.</span>';
}

function plugin_setting_columns() {
	$options = get_option('garees_split_gallery_settings');
	echo "<input id='garees_split_gallery_settings[columns]' name='garees_split_gallery_settings[columns]' size='10' type='text' value='{$options['columns']}' /> ";
	echo "<span class='description'>specify the number of columns. The gallery will include a break tag at the end of each row, and calculate the column width as appropriate. The default value is 3. If columns is set to 0, no row breaks will be included.</span>";
}

// QUERY-Setting
function plugin_section_query() {
	echo '<p>With the following settings you can change the order in which the images are displayed.</p>';
	}
function plugin_setting_order() {
	$options = get_option('garees_split_gallery_settings');	
	echo '<label><input type="radio" name="garees_split_gallery_settings[order]" value="default" id="garees_split_gallery_settings[order]_0" ' . checked( "default", $options['order'], false ) . ' /> default</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[order]" value="asc" id="garees_split_gallery_settings[order]_1" ' . checked( "asc", $options['order'], false ) . ' /> ascending</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[order]" value="desc" id="garees_split_gallery_settings[order]_2" ' . checked( "desc", $options['order'], false ) . ' /> descending</label><br />';
	echo '<span class="description">specify the sort order used to display thumbnails. The default is "ascending"</span>';
}
function plugin_setting_orderby() {
	$options = get_option('garees_split_gallery_settings');	
	echo '<label><input type="radio" name="garees_split_gallery_settings[orderby]" value="default" id="garees_split_gallery_settings[orderby]_0" ' . checked( "default", $options['orderby'], false ) . ' /> default</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[orderby]" value="menu_order" id="garees_split_gallery_settings[orderby]_1" ' . checked( "menu_order", $options['orderby'], false ) . ' /> menu order</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[orderby]" value="title" id="garees_split_gallery_settings[orderby]_2" ' . checked( "title", $options['orderby'], false ) . ' /> title</label><br />';
	echo '<label><input type="radio" name="garees_split_gallery_settings[orderby]" value="id" id="garees_split_gallery_settings[orderby]_3" ' . checked( "id", $options['orderby'], false ) . ' /> date/time</label><br />';	
	echo '<label><input type="radio" name="garees_split_gallery_settings[orderby]" value="rand" id="garees_split_gallery_settings[orderby]_4" ' . checked( "rand", $options['orderby'], false ) . ' /> random</label><br />';
	echo '<span class="description">specify the item used to sort the display thumbnails. The default is "menu_order".</span>';
}

// XHTML-Settings
function plugin_section_xhtml() {
	echo '<p>With the following 3 settings you can change the XHMTL-output generated by gallery.</p>';
	}

function plugin_setting_itemtag() {
	$options = get_option('garees_split_gallery_settings');
	echo "<input id='garees_split_gallery_settings[itemtag]' name='garees_split_gallery_settings[itemtag]' size='10' type='text' value='{$options['itemtag']}' /> ";
	echo '<span class="description">the name of the XHTML tag used to enclose each item in the gallery. The default is "dl"</span>';
}

function plugin_setting_icontag() {
	$options = get_option('garees_split_gallery_settings');
	echo "<input id='garees_split_gallery_settings[icontag]' name='garees_split_gallery_settings[icontag]' size='10' type='text' value='{$options['icontag']}' /> ";
	echo '<span class="description">the name of the XHTML tag used to enclose each thumbnail icon in the gallery. The default is "dt"</span>';
}

function plugin_setting_captiontag() {
	$options = get_option('garees_split_gallery_settings');
	echo "<input id='garees_split_gallery_settings[captiontag]' name='garees_split_gallery_settings[captiontag]' size='10' type='text' value='{$options['captiontag']}' /> ";
	echo '<span class="description">the name of the XHTML tag used to enclose each caption. The default is "dd"</span>';
}



// register uninstall
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__,'garee_split_gallery_uninstall');

// register activate
if ( function_exists('register_activation_hook') )
	register_activation_hook( __FILE__, 'garee_split_gallery_activate' );


/**
 * add default-settings
 **/    
function garee_split_gallery_activate() {
	//delete_option('garees_split_gallery_settings'); 
	$myOptions = array(
		'size' => "default",
		'orderby' => "default",
		'order' => "default",
	);
	add_option('garees_split_gallery_settings', $myOptions);
}

/**
 * Delete settings
 **/    
function garee_split_gallery_uninstall() {
    delete_option('garees_split_gallery_settings'); 
}

 ?>
