<?php
/**
 * Crawler Name: deceroadoce
 * Crawler Description: Crawler de la web deceroadoce.com
 * Version: 0.0.1
 * Author: Jose Antonio Cruz Moya
 * Author URI: http://wwww.sergiomorenoalbert.com
 * License: GPL2
 */
	if (isset($_POST['action'])) {
	    switch ($_POST['action']) {
	        case 'execute':
	            execute_();
	            break;
	    }
	}


	function execute_()
	{	
	//require (plugin_dir_path( __FILE__ )."/lib/simple_html_dom.php");
	//require (plugin_dir_path( __FILE__ )."/lib/class.iCalReader.php");

	//$url='http://www.deceroadoce.es/eventos/';

	//$url = plugin_dir_path( __FILE__ )."/prueba.ics"
	//$short_url='http://www.valencia.es/ayuntamiento/agenda_accesible.nsf/';
	// Create DOM from URL or file
	//$html = file_get_html($url);
	//var_dump($html);

	// Find all images 
	/*foreach($html->find('.entrada_agenda') as $element){ 
		foreach($element->find('img') as $image){
			$image->src = $short_url.$image->src;
			echo $image;
		}
		foreach($element->find('.texto a') as $eventlink){
			$eventlink->href = $short_url.$eventlink->href;
			$html_event = file_get_html($eventlink->href);
			foreach($html_event->find('.bloque_subtitulo') as $bloque_subtitulo){
				echo $bloque_subtitulo->plaintext;
			}
			
		
			flush();
			die();
			
		}
		   $imagen = $element->find('img');
		   $imagen->src = $short_url.$imagen->src;
	       echo $imagen;
	}*/

	}
