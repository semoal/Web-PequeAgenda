<?php
/**
 * Plugin Name: Wordpress Crawler de Sergio
 * Plugin URI: https://www.geotapgroup.com
 * Description: This plugin steal information of people.
 * Version: 0.0.1
 * Author: Sergio Moreno Albert
 * Author URI: http://wwww.sergiomorenoalbert.com
 * License: GPL2
 */

add_action( 'admin_menu', 'crawler_s_menu' );

function crawler_s_menu() {
	add_menu_page( 'Crawler Sergio Menu', 'Crawler', 'manage_options', 'wp_s_crawler/wp_s_crawler.php', 'wp_s_crawler_admin_page', 'dashicons-hammer', 6  );
}

add_action('admin_post_execute', '_handle_form_action'); // If the user is logged in
add_action('admin_post_nopriv_execute', '_handle_form_action'); // If the user in not logged in
function _handle_form_action(){
	$path = plugin_dir_path( __FILE__ );
	$files = scandir($path);
	$files = array_diff(scandir($path), array('.', '..'));
	$crawlers = [];
	$crawler_ext = "crawl.php";
	foreach ($files as $file) {
		if (strpos($file, $crawler_ext)) {
				$crawlers[] = $file;
		}
	}
	foreach ($crawlers as $crawler) {
		include ($path.$crawler); 
	}
}


function get_crawlers() {
	$output = "";
	$path = plugin_dir_path( __FILE__ );
	$files = scandir($path);
	$files = array_diff(scandir($path), array('.', '..'));
	$crawlers = [];
	$crawler_ext = ".crawl.php";
	foreach ($files as $file) {
		if (strpos($file, $crawler_ext)) {
				$crawlers[] = $file;
		}
	}
	$crawler_name_search ="Crawler Name: ";
	$crawler_desc_search ="Crawler Description: ";
	$output;
	foreach ($crawlers as $crawler) {
		$found = 0;
		
		//require ($path."/".$crawler);
		foreach(file($path."/".$crawler) as $line) {
			if (strpos($line, $crawler_name_search)) {
				$crawler_name = substr($line, strlen ($crawler_name_search)+3);
				$found++;
			}
			if (strpos($line, $crawler_desc_search)) {
				$crawler_desc = substr($line, strlen ($crawler_desc_search)+3);
				$found++;
			}
			if ($found==2) break;
		}

		$output .= '<tr>'.'<td>'.$crawler_name.'</td>'.
						  '<td>'.$crawler_desc.'</td>'.
						  '<td>'.'<form action="'.get_admin_url().'admin-post.php" method="post"><input type="hidden" name="action" value="execute" /><input id="button_'. $crawler .'" type="submit"></form>'
						  .'</td>'
				  .'</tr>';
	}
	return $output;
}


function wp_s_crawler_admin_page(){
	?>
	<div class="wrap">
		<h2>Sitios disponibles: </h2>
		<table>
			<thead>
			  <tr>
			     <th>Función: </th>
			     <th>Dirección web: </th>
			     <th>Acción: </th>
			  </tr>
			 </thead>
			  <tbody>
				  <?php echo(get_crawlers()); ?>
				</tbody>
		</table>
	</div>
	<?php

}