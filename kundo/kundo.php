<?php
/*
Plugin Name: Kundo
Plugin URI: http://simsons.se/blog/kundo-plugin/
Description: Kundo plugin for Wordpress
Version: 0.1
Author: Simson Creative Solutions
Author URI: http://simsons.se
*/

add_action('admin_init', 'kundo_admin_init');
 
function kundo_admin_init()
{
    wp_register_script('tabs', WP_PLUGIN_URL . '/kundo/js/tabs.js');
	wp_enqueue_script('jquery-ui');   
	wp_enqueue_script('jquery-ui-core');  
	wp_enqueue_script('jquery-ui-widget');  
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('tabs');
}

add_action('admin_menu', 'kundo_plugin_menu');

function kundo_plugin_menu() {

  	add_options_page('Kundo Options', 'Kundo', 'manage_options', 'kundo', 'kundo_plugin_options');

	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}

function register_mysettings() {
	//register our settings
	register_setting( 'kundo-settings-group', 'kundoid' );
	register_setting( 'kundo-settings-group', 'kundo_nbr_posts', 'intval');
}

function kundo_plugin_options() {
	
  	if (!current_user_can('manage_options'))  {
    	wp_die( __('You do not have sufficient permissions to access this page.') );
  	}
	
  	echo '<div class="wrap">';
  	echo '<h2>Kundo</h2>';
  	wp_nonce_field('update-options');
	?>
	<p>Fyll i ditt kundoinformation</p>
	<form method="post" action="options.php">
	    <?php settings_fields( 'kundo-settings-group' ); ?>
	<table class="form-table">
	<tr valign="top">
	<th scope="row">Slug</th>
	<td><input type="text" name="kundoid" value=" <?php echo get_option('kundoid');?> "/></td>
	</tr>
	<tr>
	<th scope="row">Antal poster</th>
	<td><input type="text" name="kundo_nbr_posts" value=" <?php echo get_option('kundo_nbr_posts');?> "/></td>
	</tr>
	</table>
	<p class="submit">
	    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	</form>
	</div>
	<?php
}

/**
 * Dashboard widget starts here
 */

function kundo_dashboard_widget_function() {
	// Do stuff here
	?>
	<link type="text/css" href="http://jqueryui.com/themes/base/jquery.ui.all.css" rel="stylesheet" /> 
	<style>
	#tabs {
	   border: none;
	}

	#tabs ul {
	   background: none;
	   border-top: 0;
	   border-left: 0;
	   border-right: 0;
	}
	
	.displaying {
	}
	</style>
	<center><img src="<?=get_bloginfo('url')?>/wp-content/plugins/kundo/ajax-loader.gif" id="loading" class="displaying"/></center>
	<div id="tabs">
	   <ul>
	       <li><a href="admin-ajax.php?action=kundo_get&type=q">Frågor</a></li>
	       <li><a href="admin-ajax.php?action=kundo_get&type=p">Problem</a></li>
	       <li><a href="admin-ajax.php?action=kundo_get&type=s">Förslag</a></li>
	       <li><a href="admin-ajax.php?action=kundo_get&type=b">Beröm</a></li>
	   </ul>
	</div>
	<?php
} 

function kundo_add_dashboard_widgets() {
	wp_add_dashboard_widget('kundo_dashboard_widget', 'Kundo Dashboard Widget', 'kundo_dashboard_widget_function');	
} 

add_action('wp_dashboard_setup', 'kundo_add_dashboard_widgets' );

/**
 * Ajax stuff starts here
 **/
add_action('wp_ajax_kundo_get', 'kundo_get_stuff');

function kundo_get_stuff($type) {
	// check_ajax_referer() here....
	sleep(5);
	$type = $_GET['type'];
	if($type != 'q' && $type != 'p' && $type != 's' && $type != 'b') { die("Ogiltig parameter"); }
	$url = 'http://kundo.se/api/' . get_option("kundoid") . '/' . $type . '.json';
	$json = file_get_contents($url);
	$data = json_decode($json);
	$amount = get_option("kundo_nbr_posts", 5);
	$i = 0;
	if (count($data) == 0) { echo "Not a valid slug: " . get_option("kundoid"); }
	foreach ($data as $value) {
		if ($i > $amount) { break; }
		?>
		<h4><a href="<?=$value->absolute_url?>" target="_blank"><?=$value->title?></a> av <?=$value->user->first_name?></h4>
		<span style="font-size: 10px;"><?=$value->pub_date?></span>
		<span>
		<p><?=$value->text?></p>
		</span>
		<?php
		$i++;
	}
	
	$link = "<a class='button rbutton' href='http://kundo.se/org/" . get_option("kundoid") . "/inlagg/";
	switch ($type) {
		case "q": 
			 echo $link . "fragor' target='_blank'>Mer</a>"; break;
		case "p": 
			 echo $link  . "problem' target='_blank'>Mer</a>"; break;
		case "s": 
		 	echo $link  . "forslag' target='_blank'>Mer</a>"; break;
		case "b": 
		 	echo $link . "berom' target='_blank'>Mer</a>"; break;
	}
	die();
}
?>
