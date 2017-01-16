<?php
/**
 * Crawler Name: Crawler: 
 * Crawler Description: Ayuntamiento Valencia.
 * Version: 1.0.0
 * Author: Sergio Moreno Albert
 * Author URI: http://wwww.sergiomorenoalbert.com
 * License: GPL2
 */
	if (isset($_POST['action'])) {
	    switch ($_POST['action']) {
	        case 'execute':
	            main();
	            break;
	    }
	}

	function main() {	
		require (plugin_dir_path( __FILE__ )."/lib/simple_html_dom.php");
		$url='http://www.valencia.es/ayuntamiento/agenda_accesible.nsf/BuscarAgenda?ReadForm&nivel=4&lang=1';
		$short_url='http://www.valencia.es/ayuntamiento/agenda_accesible.nsf';
		/*
		* Creamos una variable DOM a partir del html: $url
		*/
		$html = file_get_html($url);
		foreach($html->find('.entrada_agenda') as $element){ 
			if ($element) {
				$texto = $element->find('.texto',0);
				if ($texto) {
					$categoria = $texto->find('.agenda_lugar',0)->plaintext;
					if (filterEvents(trim($categoria))) {
						/*
						* Categoria, ubicacion, y titulo del evento
						*/
						$titulo = $texto->find('a',0)->plaintext;
						$longitudTitulo = strlen($titulo);
						$findme   = "\r\n";
						$pos = strpos($titulo, $findme);
						$ubicacion = substr($titulo, 0, $pos);
						$tituloEvento = substr($titulo, $pos, $longitudTitulo);

						/*
						* Buscamos la fecha Inicio y la fecha Fin
						*/ 
						$titulo2 = $texto->find('.agenda_info',0)->plaintext;
						$findme1   = "Inicio: ";
						$findme1Inicial = utf8_encode($findme1);
						$pos1 = strpos($titulo2, $findme1Inicial);
						$stringFechaInicio = substr($titulo2, $pos1+strlen($findme1Inicial), 10);
						$findme2   = "Finalización: ";
						$findme2Inicial = utf8_encode($findme2);
						$pos2 = strpos($titulo2, $findme2Inicial);
						$stringFechaFin = substr($titulo2, $pos2+strlen($findme2Inicial), 10);
						/*
						* Datos restantes:
						* Descripcion, telefono, url, email, y direccion
						*/
						$link = $texto->find('a',0)->href;
						$link = $short_url . substr($link, 1, strlen($link));
						$html_detalle = file_get_html($link);
						foreach($html_detalle->find('#columna_centro') as $principal){ 
							$all_description = "";
							foreach ($principal->find('.bloque_texto') as $descripcion) {
								$all_description .= $descripcion->plaintext;
							}
							foreach ($principal->find('ul') as $lista) {
								$stringDireccion = "";
								foreach ($lista->find('li') as $elementoLista) {
									$elementoListaString = trim($elementoLista->plaintext);
									$elementoListaString = $elementoListaString;
									$findme1 = utf8_encode("fono: ");
									$pos1 = strpos($elementoListaString, $findme1);
									if ($pos1!==false) {
										$stringTelefon = preg_replace("/[A-Z,a-z.&;:() ?]/", "", utf8_encode($elementoListaString));
									} else {
										$findme1 = utf8_encode("Url: ");
										$pos1 = strpos($elementoListaString, $findme1);
										if ($pos1!==false) {
											$stringUrl = preg_replace("/Url: /", "", $elementoListaString);
										} else {
											$findme1 = utf8_encode("e-mail: ");
											$pos1 = strpos($elementoListaString, $findme1);
											if ($pos1!==false) {
												$stringEmail = preg_replace("/e-mail: /", "", $elementoListaString);
											} else {
												if ($stringDireccion=="") {
													$stringDireccion = $elementoListaString;
												} else {
													$stringDireccion .= "-".$elementoListaString;
												}	
											}
										}
									}
								}
							}
						}
						/*
						* Comprobamos con is_repeated() si el evento ya esta puesto en la web
						* Si no esta repetido lo insertamos con insertEvents()
						* Si esta repetido, nos olvidamos de el
						*/
						if (!isRepeated($tituloEvento,$all_description,$stringFechaInicio,$stringFechaFin,$stringTelefon,$stringUrl,$stringEmail,$stringDireccion)) {
							insertEvents ($tituloEvento,$all_description,$stringFechaInicio,$stringFechaFin,$stringTelefon,$stringUrl,$stringEmail,$stringDireccion);
							echo "No insertado: " . $tituloEvento."--- Categoria: ".$categoria. "<br>";
						}else {
							echo "Repetido: " . $tituloEvento."--- Categoria: ".$categoria. "<br>";
						}
					}
				}
			}
		}
	}
	/*
	* Cogemos la query de Wordpress con todos los eventos disponibles a dia de hoy
	* Y comprobamos si los titulos de la matriz de nuestros eventos y los que vamos a insertar cuales son un 80% similares
	*/
	function isRepeated ($tituloEvento,$all_description,$stringFechaInicio,$stringFechaFin,$stringTelefon,$stringUrl,$stringEmail,$stringDireccion) {
		$founded = false;
		$currentdate = date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")));
        $args = array(
            'post_type' => 'event_type',
            'meta_query'=> array(
                array(
                  'key' => 'fecha_fin',
                  'compare' => '>=',
                  'value' => $currentdate,
                  'type' => 'DATE',
                )),
            'meta_key' => 'fecha_fin',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'posts_per_page' => 100,
            'paged' => $paged,
        );
		 $query = new WP_Query( $args );
		 	$eventos = [];

         	if( $query->have_posts() ) { 
	         	while ($query->have_posts()):
	                    $query->the_post(); 
	                    	$evento = [];
	                    	$evento["Titulo"] = get_the_title();
	                    	$evento["fecha-inicio"] = get_field("fecha_inicio");
	                    	$evento["fecha-fin"] = get_field("fecha_fin");
	                    	$evento["email"] = get_field("correo_electronico");
	                    	$evento["telefono"] = get_field("telefono");
	                        $evento["web"] = get_field("pagina_web");
	                        $eventos[]=$evento;
	                endwhile;
					$count = count($eventos);
					for ($i = 0; $i < $count; $i++) {
						similar_text($eventos[$i]["Titulo"],  utf8_encode($tituloEvento), $percent);
				        if($percent >= 80){
				      		$founded = true;
				        	break;
				        }else {} 
					}
	               	wp_reset_query(); 
         	}
         return $founded;
	}
	/*
	* Insertamos los eventos que no esten ya puestos, en eventos guardados como borrador por si queremos comprobar los datos
	*/ 
	function insertEvents ($tituloEvento,$all_description,$stringFechaInicio,$stringFechaFin,$stringTelefon,$stringUrl,$stringEmail,$stringDireccion) {

		$eventosCrawler = [];
		$eventosCrawler["titulo"] =  $tituloEvento;
		$eventosCrawler["descripcion"] = $all_description;
		$eventosCrawler["fecha-inicio"] = $stringFechaInicio;
		$eventosCrawler["fecha-fin"] = $stringFechaFin;
		$eventosCrawler["telefono"] = $stringTelefon;
		$eventosCrawler["url"] = $stringUrl;
		$eventosCrawler["email"] = $stringEmail;
		$eventosCrawler["direccion"] = $stringDireccion;
		//$descripcionMejorada= utf8_encode($eventosCrawler["descripcion"]);
		//Insertamos
		$post_id = wp_insert_post(array(
			'post_title'=> $eventosCrawler["titulo"],
		 	'post_type'=>'event_type', 
		 	'post_content'=> utf8_encode($eventosCrawler["descripcion"])
		));
		//Field de fecha-inicio
		$field_key = "field_580f1e002b870";
		update_field( $field_key, $eventosCrawler["fecha-inicio"], $post_id );
		//Field de fecha-fin
		$field_key = "field_580f1e102b871";
		update_field( $field_key, $eventosCrawler["fecha-fin"], $post_id );
		//Field de telefono
		$field_key = "field_5791d71df29d5";
		update_field( $field_key, $eventosCrawler["telefono"], $post_id );
		//Field de url
		$field_key = "field_5791d74fb905a";
		update_field( $field_key, $eventosCrawler["url"], $post_id );
		//Field de email
		$field_key = "field_5791d739f29d6";
		update_field( $field_key, $eventosCrawler["email"], $post_id );
		//Field de direccion
		$field_key = "field_58735c36217ef";
		update_field( $field_key, $eventosCrawler["direccion"], $post_id ); 
	}
	/*
	* Filtro de la categoria de cada evento, ahora mismo coje eventos que contengan agenda infantil, circo y teatro
	*/ 
	function filterEvents ($categoria){
		$resultado = false;
		if (strcasecmp($categoria,"AGENDA INFANTIL") == 0) {
			$resultado = true;
		}
		if (strcasecmp($categoria,"CIRCO") == 0) {
			$resultado = true;
		}
		if (strcasecmp($categoria,"TEATRO") == 0) {
			$resultado = true;
		}

		return $resultado;
	}
