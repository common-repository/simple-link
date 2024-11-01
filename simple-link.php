<?php

/*
Plugin Name: Simple Link
Description: Enable you to add hyperlinks to other posts on your blog with just a simple click.
Author: Awaken
Version: 0.0.8
Author URI: http://www.renzuocheng.com
*/

/*  Copyright 2011  Awaken (email : i@renzuocheng.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/*
**********************************************
stop editing here unless you know what you do!
**********************************************
*/

class SimpleLink{
	var $info;
	/*
	 *	Constructor
	 */
	function SimpleLink(){
		//Initiallizations
		
		/*
		 *	Initaillize some variables may be used frequently later.
		 *	$info(
		 *		home
		 *		siteurl
		 *		install_url
		 *		install_dir
		 *	)
		 *	
		 */
		$path = str_replace('\\','/',dirname(__FILE__));
		$path = substr($path, strpos($path, 'plugins') + 8, strlen($path));

		$info['siteurl'] = get_option('siteurl');
		if ( $this->isMuPlugin() ) {
			$info['install_url'] = $info['siteurl'] . '/wp-content/mu-plugins';
			$info['install_dir'] = ABSPATH . 'wp-content/mu-plugins';

			if ( $path != 'mu-plugins' ) {
				$info['install_url'] .= '/' . $path;
				$info['install_dir'] .= '/' . $path;
			}
		} else {
			$info['install_url'] = $info['siteurl'] . '/wp-content/plugins';
			$info['install_dir'] = ABSPATH . 'wp-content/plugins';

			if ( $path != 'plugins' ) {
				$info['install_url'] .= '/' . $path;
				$info['install_dir'] .= '/' . $path;
			}
		}
		$this->info = array(
			'home' => get_option('home'),
			'siteurl' => $info['siteurl'],
			'install_url' => $info['install_url'],
			'install_dir' => $info['install_dir']
		);
		unset($info);
		
		/*
		 *	Add action hooks.
		 *	
		 */
		add_action('init', array(&$this, 'ajaxCheck'));
		add_action('admin_head', array(&$this, 'sl_head'), 1);
		add_action('edit_form_advanced', array(&$this, 'sl_filter'), 1);
		add_action('edit_page_form', array(&$this, 'sl_filter'), 1);
		//add_action('admin_head', array(&$this, 'sl_filter'), 1);
		return true;
	}
	/*
	 *	Verify whether the user is using WordPress_MU
	 */
	function isMuPlugin() {
		if ( strpos(dirname(__FILE__), 'mu-plugins') ) {
			return true;
		}
		return false;
	}
	/*
	 *	Add header when SimpleLink is loaded.
	 */
	function sl_filter(){
		
		// Filtering
		echo '
		<div id="sl_filters">';
		//Category drop-down
		$cats=get_categories();
		echo '
			<div class="sl_filter">
				<label>Category:</label>
					<select id="sl_cat">
						<option value="0"></option>
		';
		foreach($cats as $cat){
			echo '<option value="' .get_cat_id($cat->cat_name).'">'.$cat->cat_name.'</option>
			';
		}
		echo '		</select>
			</div>
		';
		// ...then the keyword search.
		echo '
			<div class="sl_filter">
				<label>Keyword:</label>
				<input type="text" id="sl_keyword" class="sl_filter_input" value="" title="Use % for wildcards." />
			</div>
		';
		// ...then the tag filter.
		echo '
			<div class="sl_filter">
				<label>Tag:</label>
				<input type="text" id="sl_tag" class="sl_filter_input" value="" title="\'foo, bar\': posts tagged with \'foo\' or \'bar\'. \'foo+bar\': posts tagged with both \'foo\' and \'bar\'." />
			</div>
		';
		echo '
			<div class="sl_filter">
				<label>New Window:</label>
				<input type="checkbox" id="sl_target" />
			</div>
		';
		echo '</div>';
		
	}
	function sl_head(){	
		echo '<link rel="stylesheet" type="text/css" href='.$this->info['install_url'].'/css/simple-link.css?ver=20080922" />';
		echo '<script type="text/javascript" src="'.$this->info['install_url'].'/js/simple-link.js?ver=20100117"></script>';
		echo '<script type="text/javascript" src="'.$this->info['install_url'].'/js/click-simple-link.js?ver=20080922"></script>';
		// CSS Optimization For IE6
		$strUserAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
  		if (strpos($strUserAgent, 'compatible; msie 6')&&(!strpos($strUserAgent, 'compatible; msie 7'))&&(!strpos($strUserAgent, 'compatible; msie 8'))){
			echo '<link rel="stylesheet" type="text/css" href='.$this->info['install_url'].'/css/simple-link-ie6.css?ver=20081020" />';
		}
		// These variables will be used later
		?>
		
		<script type="text/javascript">
	    // <![CDATA[
			var site_clicklink = '<?php echo $this->info['siteurl']; ?>';
			var refresh_text = 'Refresh';
			var show_clicklink = '<?php echo 'Display click links'; ?>';
			var hide_clicklink = '<?php echo 'Hide click links'; ?>';
		// ]]>
	    </script>
		<?php
	}
	/*
	 *	Check which ajax action is called.
	 */
	function ajaxCheck(){
		if ( $_GET['sl_ajax_action'] == 'click_links') {
			$cat=$_GET['sl_cat'];
			$keyword=$_GET['sl_keyword'];
			$tag=$_GET['sl_tag'];
			$this->ajaxClickLinks($cat,$keyword,$tag);
		}
	}
	/*
	 *	Display ClickLink buttons using ajax.
	 */
	function ajaxClickLinks($cat,$keyword,$tag){	
		$orderby="date";
		$order="desc";
		$per_page=15;
    $post_status="publish";
    $post_type="post";
		//Query posts from the $wpdb.
		$q="post_type=$post&orderby=$orderby&order=$order&post_status=$publish&posts_per_page=$per_page";
		if($cat){
			$cat=intval($cat);
			$q .= "&cat=$cat";
		}
		if($keyword){
			$q .= "&s=$keyword";
		}
		if($tag){
			$q .= "&tag=$tag";
		}
		//print($q);
		$query = new WP_Query;
		$posts = $query->query($q);
		foreach ((array)$posts as $post ) {	
			echo '<input type="button" class="button-secondary localpost" id="'.get_permalink($post->ID).'" value="'.$post->post_title.'"/>'."\n";
		}
	}
}


global $simpleLink;
function sl_init() {
	global $simpleLink;
	$simpleLink = new SimpleLink();
}
/*function sl_option(){
}
function sl_menu(){
	add_options_page('Simple Link Options', 'Simple Link', 4, basename(__FILE__), 'sl_option');
}*/
//add_action('admin_menu', 'sl_menu');
add_action('plugins_loaded', 'sl_init');
?>
