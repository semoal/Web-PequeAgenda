<?php
 /**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * Template name: homepage
 * @subpackage Twenty_SixteenChild
 * @since Twenty Sixteen 1.0
 */  ?>
    <?php  get_header(); ?>
    <?php
            $locations =[];
            $i=0;
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
                  if( $query->have_posts() ) {  
                    while ($query->have_posts()) :
                            $query->the_post(); 
                                $tmp_loc = [];
                                $tmp_loc["id"] = $i;
                                $tmp_loc["Titulo"] = get_the_title();
                                $tmp_loc["contenido"] =    preg_replace('~[[:cntrl:]]~', '', get_the_content());
                                $address = get_field( "direccion" );
                                $coordenadas_tmp = get_field("coord");
                                if (isset($coordenadas_tmp["address"],$coordenadas_tmp["lng"],$coordenadas_tmp["lng"])) {
                                    $tmp_loc["direccion"] = $coordenadas_tmp["address"];
                                    $tmp_loc["latitude"] = $coordenadas_tmp["lat"];
                                    $tmp_loc["longitude"] = $coordenadas_tmp["lng"];
                                }
                                $tmp_loc["telefono"] = get_field( "telefono" );
                                $tmp_loc["pases"] = get_field("fecha_pases");
                                if ($tmp_loc["pases"]) {
                                	$tmp_loc["pases_html"] = "<span>";
	                                foreach ($tmp_loc["pases"] as $pase) {
	                                	$tmp_loc["pases_html"] .= "<span>" . implode ($pase) . "</span>". ', ';
	                                }
	                                $tmp_loc["pases_html"] .= "</span>";
	                            } else {
                                    //$tmp_loc["pases"]["pase"][]= get_field( "fecha_inicio" );
                                    //$tmp_loc["pases_html"] = "<span>" . implode ($tmp_loc["pases"]) . "</span>";
                                }
                                
                                $tmp_loc["fecha-inicio"] = get_field( "fecha_inicio" );
                                $tmp_loc["fecha-fin"] = get_field( "fecha_fin" );
                                $tmp_loc["icono"] = get_field("imagen_evento");
                                $tmp_loc["precio"] = get_field("precio");
                                $tmp_loc["email"] = get_field( "correo_electronico" );
                                $tmp_loc["web"] = get_field( "pagina_web" );
                                $tmp_loc["edad"] = get_field( "edad" );
                                $nombres[$i] = get_the_title();
                                $tmp_loc["foto"] = get_the_post_thumbnail_url();
                                $locations[] = $tmp_loc;
                                
                                $i++;
                    // End of the loop.
                    endwhile;
                    wp_reset_query();
        ?>
    <script>
        var locations = JSON.parse('<?php echo json_encode($locations); ?>');
        
        var getMarkers = function(i) {
            return markers[i];
            }   
        var markers = [];

        function initMap() {
            var myLatLng = {
                lat: 40.4637,
                lng: -3.703790,
            };
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 5,
                  zoomControl: true,
                  disableDefaultUI: true,
                  center: myLatLng,
                  gestureHandling: 'greedy'

            });

                  
            var isMobile = {
                Android: function() {
                    return navigator.userAgent.match(/Android/i);
                },
                BlackBerry: function() {
                    return navigator.userAgent.match(/BlackBerry/i);
                },
                iOS: function() {
                    return navigator.userAgent.match(/iPhone|iPod/i);
                },
                Opera: function() {
                    return navigator.userAgent.match(/Opera Mini/i);
                },
                Windows: function() {
                    return navigator.userAgent.match(/IEMobile/i);
                },
                any: function() {
                    return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
                }
            };
            
            var getLocations = function(i) {
                return $.grep(locations, function(e){ return e.id == i; });
            }

            var infowindow = new google.maps.InfoWindow;
            var temp_loc;
            var marker, i;
            var displayResults;
            
            //Any mobile
        if( isMobile.any() )  
            for (i = 0; i < locations.length; i++) {
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(locations[i]["latitude"], locations[i]["longitude"]),
                    map: map,
                    icon: locations[i]['icono'],
                    optimized: false
                });
                google.maps.event.addListener(marker, 'click', (function(marker, i) {
                    return function() {
                        map.setZoom(15);
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                        setTimeout(function(){ marker.setAnimation(null); }, 5000);
                        document.getElementById("titulo_info").innerHTML=
                        locations[i]["Titulo"] + '<br>';
                        document.getElementById("titulo_pie").innerHTML=
                        '<img class="imageDown" src="' + locations[i]['foto'] + '" />' +
                        '<b> Descripción:</b>' + ' ' +
                        locations[i]["contenido"] + '<br>' +
                        '<b> Dirección:</b>' + ' ' +
                        locations[i]["direccion"] + '<br>' +
                         '<b> Pases:</b>' + ' ' +
                        locations[i]["pases_html"] + '<br>' +
                        '<b> Fecha inicio:</b>' + ' ' +
                        locations[i]["fecha-inicio"] + '<br>' +
                        '<b> Fecha fin:</b>' + ' ' +
                        locations[i]["fecha-fin"] + '<br>' +
                        '<b> Telefono:</b>' + ' ' +
                        '<a href="tel:"'+locations[i]["telefono"]+'">' + locations[i]["telefono"] + '</a>' + '<br>' +
                        '<b> Correo electronico:</b>' + ' ' +
                        locations[i]["email"] + '<br>' +
                        '<b> Web:</b>' + ' ' +
                        '<a href=' + locations[i]["web"] + '>' + locations[i]["web"] + '</a>' + '<br>' +
                        '<b> Precio:</b>' + ' ' +
                        locations[i]["precio"] + '€' + '<br>' + 
                        '<b> Edad recomendada:</b>' + ' ' + 'De ' + locations[i]["edad"]["min"] + ' años, a ' +locations[i]["edad"]["max"]+ ' años. '+ '<br>';
                        map.setCenter(marker.getPosition());
                        $('#botInfW').stop().animate({
                            opacity: '1',
                            height: '25%'
                        });
                        $('#container').stop().animate({
                            opacity: '1',
                            height: '75%'
                        });
                        $('.rectangle-bottom').stop().animate({
                            opacity: '0',
                         });

                         $('#botInfW').one("click", function (e) {
                                e.preventDefault();
                               $('#botInfW').stop().animate({opacity: '1', height: '50%', width: '100%;' },250, function(){
                                });

                               $('#container').stop().animate({opacity: '1', height: '50%', width: '100%;' },250, function(){
                                });
                            });
                         $('#buttonclose').stop().on("click", function (event) {
                               event.stopPropagation();
                               $('#botInfW').stop().animate({opacity: '1', height: '0%', width: '100%;', display: 'none;' },250, function(){
                                });
                               $('#container').stop().animate({opacity: '1', height: '100%', width: '100%;' },250, function(){
                                });
                               $('.rectangle-bottom').stop().animate({opacity: '1',});
                           });

                }

                })(marker, i));

                markers.push(marker);
                //Función para geolocalizar la ubicación del usuario
                 $( ".fa-location-arrow" ).click(function() {
                        (function(geolocatingUser) {
                            if(!!navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(function(position) {
                                    var geolocate = new google.maps.LatLng(position.coords.latitude, position.coords.longitude); 
                                    var marker = new google.maps.Marker({
                                        map: map,
                                        position: geolocate
                                    });

                                    map.setCenter(geolocate);
                                    map.setZoom(16);    
                                    marker.setAnimation(google.maps.Animation.BOUNCE);
                                    setTimeout(function(){ marker.setAnimation(null); });                               
                                });
                                
                            } else {
                                document.getElementById('map').innerHTML = 'No Geolocation Support.';
                            }
                            
                        })();
                    });
               //Version inicial para menu
                    $('#button-slide-menu-pint').stop().hide();
                    $('.first-div-close').stop().hide();
                    $('.second-div-close').stop().hide();
                    $('#rangoLocalizacion').stop().hide();
            //Abrimos el listado derecho y cambiamos el icono para activar una nueva funcionalidad
                 $('#button-slide-menu').on("click", function (open) {
                        open.preventDefault();
                    $('#button-slide-menu').stop().hide();
                    $('#button-slide-menu-pint').stop().show(); 
                    $('#mySidenav').stop().animate({width: '100%', opacity: '1'});
                    $('#container').stop().animate({opacity: '1',width: '100%'});
                    $('.first-div-close').stop().show();
                    $('#right-second').stop().animate({width: '0'});
                    $('#button-slide-menu').stop().hide();
                    $('#button-slide-menu-pint').stop().show();
                    $('.second-div-close').stop().hide();
                    $('#rangoLocalizacion').stop().hide();
                    $('#botInfW').stop().hide();
                    $('.rectangle-bottom').stop().hide();


                });
            //Abrimos el pinterest style 
                $('#button-slide-menu-pint').on("click", function (change) {
                    change.preventDefault();
                    $('.first-div-close').stop().hide();
                    $('.second-div-close').stop().show();
                    $('#mySidenav').stop().animate({width: '0'});
                    $('#right-second').stop().animate({width: '100%', opacity: '1'});
                    $('#container').stop().animate({width: '100%'});
                    $('#button-slide-menu-pint').stop().hide();
                    $('#button-slide-menu').stop().show();
                    $('#rangoLocalizacion').stop().show();
                    $('#botInfW').stop().hide();
                    $('.rectangle-bottom').stop().hide();
                }); 
            //Cerramos el listado derecho y cambiamos el icono para volver al origen
                $('.first-div-close').stop().on("click", function (close) {
                    close.preventDefault();
                    $('#mySidenav').stop().animate({width: '0'});
                    $('#button-slide-menu').stop().show();
                    $('#button-slide-menu-pint').stop().hide();
                    $('.second-div-close').stop().hide();
                    $('.first-div-close').stop().hide();
                    $('#container').stop().animate({width: '100%'});
                    $('#botInfW').stop().show();
                    $('.rectangle-bottom').stop().show();                    
                });
            //Cerramos el pinterest style
                $('.second-div-close').stop().on("click", function (close2) {
                    close2.preventDefault();
                    $('#right-second').stop().animate({width: '0'});
                    $('#button-slide-menu').stop().hide();
                    $('#button-slide-menu-pint').stop().show();
                    $('.second-div-close').stop().hide();
                    $('#rangoLocalizacion').stop().hide();
                    $('#container').stop().animate({width: '100%'});
                    $('#botInfW').stop().show();
                    $('.rectangle-bottom').stop().show();
                });

                $('#results').stop().on("click", function(concadenar) {
                    $('#mySidenav').stop().animate({width: '0'});
                    $('#right-second').stop().animate({width: '0'});
                    $('#button-slide-menu').stop().hide();
                    $('#button-slide-menu-pint').stop().show();
                    $('.second-div-close').stop().hide();
                    $('#rangoLocalizacion').stop().hide();
                    $('#container').stop().animate({width: '100%'});
                    $('#botInfW').stop().show();
                    $('.rectangle-bottom').stop().show();
                    $('#button-slide-menu').stop().show();
                    $('#button-slide-menu-pint').stop().hide();
                    $('.second-div-close').stop().hide();
                    $('.first-div-close').stop().hide();
                    $('#container').stop().animate({width: '100%'});
                    $('#botInfW').stop().show();
                    $('.rectangle-bottom').stop().show();   
                    $('.rectangle-bottom').stop().animate({opacity: '0',});
                    $('#botInfW').stop().animate({opacity: '1', height: '25%', width: '100%;' },250, function(){});
                    $('#container').stop().animate({opacity: '1', height: '75%', width: '100%;' },250, function(){});
                });
            }
        //Computer
            else {  
                for (i = 0; i < locations.length; i++) {
                    var infowindow_content =
                    '<div class="main-info">' +
                    '<h1 class="firstHeading">' + locations[i]["Titulo"] + '</h1>' +
                    '<div class="bodyContent">' +
                    '<img class="imageContent" src="' + locations[i]['foto'] + '" />' +
                    '<b> Descripción:</b>' + ' ' +
                    locations[i]["contenido"] + '<br>' +
                    '<b> Dirección:</b>' + ' ' +
                    locations[i]["direccion"] + '<br>' +
                    '<b> Pases:</b>' + ' ' +
                    locations[i]["pases_html"] + '<br>' +
                    '<b> Fecha inicio:</b>' + ' ' +
                    locations[i]["fecha-inicio"] + '<br>' +
                    '<b> Fecha fin:</b>' + ' ' +
                    locations[i]["fecha-fin"] + '<br>' +
                    '<b> Telefono:</b>' + ' ' +
                    locations[i]["telefono"] + '<br>' +
                    '<b> Correo electronico:</b>' + ' ' +
                    locations[i]["email"] + '<br>' +
                    '<b> Página web:</b>' + ' ' +
                    '<a href="'+locations[i]["web"]+'">' + locations[i]["web"] + '</a>' + '<br>' +
                    '<b> Precio:</b>' + ' ' +
                    locations[i]["precio"] + '€' + '<br>' +
                    '<b> Edad recomendada:</b>' + ' ' +
                    'De '+ locations[i]["edad"]["min"] + ' años, a ' +locations[i]["edad"]["max"]+ ' años. '+ '<br>' + '</div>';

                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(locations[i]["latitude"], locations[i]["longitude"]),
                        map: map,
                        icon: locations[i]['icono'],
                        optimized: false
                    });
                    google.maps.event.addListener(marker, 'click', (function(marker, i, infowindow_content) {
                        return function() {
                            infowindow.setContent(infowindow_content);
                            infowindow.open(map, marker);
                            map.setZoom(15);
                            map.setCenter(marker.getPosition());
                            }
                        google.maps.event.addDomListener(window, 'resize', function() {
                                infowindow.open(map, marker);
                                 });
                    })(marker, i, infowindow_content));

                    markers.push(marker); 
                    } 
                        $( ".fa-location-arrow").click(function() {
                            var marcador;
                            (function(geolocatingUser) {
    						        navigator.geolocation.getCurrentPosition(function(position) {	
    						            var geolocate = new google.maps.LatLng(position.coords.latitude, position.coords.longitude); 
    				
                                        if (marcador) {
                                            marcador.setMap(geolocate);
                                        }else {
                                            marcador = new google.maps.Marker({
                                            map: map,
                                            position: geolocate });
                                        }			            
    						        });
    						});
                        });
                 //Version inicial para menu
                    $('#button-slide-menu-pint').stop().hide();
                    $('#info-left-close').stop().hide();
                    $('#info-left-close-second').stop().hide();
                    $('#rangoLocalizacion').stop().hide();
            //Abrimos el listado derecho y cambiamos el icono para activar una nueva funcionalidad
                 $('#button-slide-menu').on("click", function (open) {
                        open.preventDefault();
                    $('#button-slide-menu').stop().hide();
                    $('#button-slide-menu-pint').stop().show(); 
                    $('#mySidenav').stop().animate({width: '50%', opacity: '1'});
                    $('#container').stop().animate({opacity: '1',width: '50%'});
                    setTimeout(function(){
                        $('#info-left-close').stop().fadeIn(); 
                    },300); 
                    $('#right-second').stop().animate({width: '0'});
                    $('#button-slide-menu').stop().hide();
                    $('#button-slide-menu-pint').stop().show();
                    $('#info-left-close-second').stop().hide();
                    $('#rangoLocalizacion').stop().hide();
                    $('#botInfW').stop().hide();
                });
            //Abrimos el pinterest style 
                $('#button-slide-menu-pint').on("click", function (change) {
                    change.preventDefault();
                    $('#info-left-close').stop().hide();
                    setTimeout(function(){
                        $('#info-left-close-second').stop().fadeIn(); 
                    },300); 
                    $('#mySidenav').stop().animate({width: '0'});
                    $('#right-second').stop().animate({width: '50%', opacity: '1'});
                    $('#container').stop().animate({width: '50%'});
                    $('#button-slide-menu-pint').stop().hide();
                    $('#button-slide-menu').stop().show();
                    $('#rangoLocalizacion').stop().show();
                    $('#botInfW').stop().hide();
                }); 
            //Cerramos el listado derecho y cambiamos el icono para volver al origen
                $('#info-left-close').stop().on("click", function (close) {
                    close.preventDefault();
                    $('#mySidenav').stop().animate({width: '0'});
                    $('#button-slide-menu').stop().show();
                    $('#button-slide-menu-pint').stop().hide();
                    $('#info-left-close.second').stop().hide();
                    $('#info-left-close').stop().hide();
                    $('#container').stop().animate({width: '100%'});
                    $('#botInfW').stop().show();
                    $('.rectangle-bottom').stop().show();                    
                });
                $('#columns-pictures').stop().on("click", function (ouyhyea) {
                    ouyhyea.preventDefault();
                    $('#right-second').stop().animate({width: '0'});
                    $('#button-slide-menu').stop().hide();
                    $('#button-slide-menu-pint').stop().show();
                    $('#info-left-close-second').stop().hide();
                    $('#rangoLocalizacion').stop().hide();
                    $('#container').stop().animate({width: '100%'});
                    $('#botInfW').stop().show();
                    $('.rectangle-bottom').stop().show();
                });
            //Cerramos el pinterest style
                $('#info-left-close-second').stop().on("click", function (close2) {
                    close2.preventDefault();
                    $('#right-second').stop().animate({width: '0'});
                    $('#button-slide-menu').stop().hide();
                    $('#button-slide-menu-pint').stop().show();
                    $('#info-left-close-second').stop().hide();
                    $('#rangoLocalizacion').stop().hide();
                    $('#container').stop().animate({width: '100%'});
                    $('#botInfW').stop().show();
                    $('.rectangle-bottom').stop().show();
                });
            //Cerramos side nav cuando map click
            	
            };

            <?php  } ?>
        }

    </script>
    <script type="text/javascript">
    //Javascript for searching
    document.addEventListener("DOMContentLoaded", function(event) { 
      //do work
        searchInput = document.getElementById('search');
        resultsOutput = document.getElementById('results');
        maxResults = 6;
       
        if (searchInput) {
                searchInput.addEventListener('keyup', (function(_this) {
                    var value;
                    value = searchInput.value.toLowerCase().split(' ');
                    var resultados= buscar_eventos(value);
                    displayResults(resultados);
                    
                }));
            }

        function buscar_eventos(cadena){
            var results=[];
            for (var i = 0, len = locations.length; i < len; i++) {
                if (locations[i]["Titulo"].toLowerCase().search(cadena)>=0) {
                    results.push(locations[i]);
                }
              
            }
            return results;
        }

         function displayResults(results) {
            var output= "";
            var total =(results.length>maxResults? maxResults:results.length);
            for (var i = 0, len = total; i < len; i++) {
                output+='<li id="result_'+ results[i]["id"]+'">' + results[i]["Titulo"] + ' <img class="imageUl" src="'+ results[i]["icono"] +'" /></li>';
            }
            resultsOutput.innerHTML = output;
            anyadirListener(results);
          };

          function anyadirListener(results) {
            var total =(results.length>maxResults? maxResults:results.length);

            for (var i = 0, len = total; i < len; i++) {
                (function (i) {
                        var result_id='result_'+ results[i]["id"];
                        var result_temp = document.getElementById(result_id);
                        result_temp.addEventListener ("click",function() {
                            google.maps.event.trigger(getMarkers(results[i]["id"]), 'click');
                        },false);                   
                }(i));
            }
          }

    });
    
    </script>
    <div class="bottomInfoWindowDown" id="botInfW"> 
        <img id="buttonclose" src="/wp-content/themes/twentysixteen-child/cross.png"></img>
        <p id="titulo_info"></p>
        <p id="titulo_pie"> </p>
    </div>
    <img id="info-left-close" style="right: 50%;position: absolute;z-index: 1;" src="/wp-content/themes/twentysixteen-child/cerrar.png"></img>
    <div id="mySidenav" class="sidenav">
        <img id="info-left-close-mobile" class="first-div-close" src="/wp-content/themes/twentysixteen-child/cross.png"></img>
            <h2 class="title-info"> ¡Eventos para hoy! </h2>
            <ul class="side-nav-ul"> 
                <?php
                    // Almacenamos los eventos en 3 arrays
                    $today_events =[];
                    $week_events = [];
                    $more_events = [];
                    $today = date("y/m/d");


                    //Calculamos el domingo de esta semana para poder meterlo dentro del array correspondiente
                    $nextSunday = date("y/m/d", strtotime("next sunday"));

                    foreach ($locations as $location) {
                    	if ($location["pases"]){
                       		foreach ($location["pases"] as $pase) {
		                       $temp_date = date("y/m/d", strtotime(str_replace('/', '-', implode($pase))));
		                       $temp_loc = $location;
		                       $temp_loc ["pases"] = implode($pase);
		                       if ($temp_date == $today) {
                                 $item = null;
                                 $i=0;
                                foreach($today_events as $struct) {
                                    if ($temp_loc ["id"] == $struct["id"]) {
                                        $item = $struct;
                                        break;
                                    }
                                    $i++;
                                }
                                 if (!$item){
                                      $temp_loc ["pases"] = date("H:i", strtotime(str_replace('/', '-', implode($pase))));
		                              $today_events[] = $temp_loc;
                                 } else {
                                    $today_events[$i]["pases"] .= ", " . date("H:i", strtotime(str_replace('/', '-', implode($pase))));
                                 }
		                       } else if (($temp_date<= $nextSunday) && ($temp_date>$today)) {
                                    $item = null;
                                    $i=0;
                                    foreach($week_events as $struct) {
                                        if ($temp_loc ["id"] == $struct["id"]) {
                                            $item = $struct;
                                            break;
                                        }
                                        $i++;
                                    }
                                    if (!$item){
    	                                  $week_events[] = $temp_loc;
                                    } else {
                                        $week_events[$i]["pases"] .= ", " . implode($pase);
                                    }
		                       } else if ($temp_date > $nextSunday) {
                                    $item = null;
                                    $i=0;
                                    foreach($more_events as $struct) {
                                        if ($temp_loc ["id"] == $struct["id"]) {
                                            $item = $struct;
                                            break;
                                        }
                                        $i++;
                                    }
                                    if (!$item){
                                          $more_events[] = $temp_loc;
                                    } else {
                                        $more_events[$i]["pases"] .= ", " . implode($pase);
                                    }
    	                      }
	                     	}
                       }else {


                       } 
                    }

                    foreach ($today_events as $location) {
                        echo '<li><div id="dateside-'.$location["id"].'" style="white-space: normal">';
                        echo '<img class="image-right-info" src="'.$location["foto"].'"> </img>'; 
                        echo '<h3 class="titulo-right-info">'.substr($location["Titulo"],0,50)."...".'</h3>';
                        echo '<p>'.substr($location["contenido"],0,150).'</p>';
                        echo '<p>'."Fechas:".''.$location["pases"].'</p>';
                        echo '</div></li>';
                    }
                    
                ?>
            </ul>
            <h2 class="title-info"> ¡Eventos para esta semana! </h2>
            <ul>
                <?php
                    foreach ($week_events as $location) {
                        echo '<li><div id="dateside-'.$location["id"].'" style="white-space: normal">';
                        echo '<img class="image-right-info" src="'.$location["foto"].'"> </img>'; 
                        echo '<h3 class="titulo-right-info">'.substr($location["Titulo"],0,50)."...".'</h3>';
                        echo '<p>'.substr($location["contenido"],0,150).'</p>';
                        echo '<p>'."Fechas:".''.$location["pases"].'</p>';
                        echo '</div></li>';
                    }
                    
                ?>
            </ul>
            <h2 class="title-info"> ¡Más eventos! </h2>
            <ul>
                <?php
                    foreach ($more_events as $location) {
                        echo '<li><div id="dateside-'.$location["id"].'" style="white-space: normal">';
                        echo '<img class="image-right-info" src="'.$location["foto"].'"> </img>'; 
                        echo '<h3 class="titulo-right-info">'.substr($location["Titulo"],0,50)."...".'</h3>';
                        echo '<p>'.substr($location["contenido"],0,150).'</p>';
                        echo '<p>'."Fechas:".''.$location["pases"].'</p>';
                        echo '</div></li>';
                    }
                ?>
            </ul>
    </div>
    <!--Div con columnas de los eventos --> 
    <img id="info-left-close-second" style="right: 50%;position: absolute;z-index: 1;" src="/wp-content/themes/twentysixteen-child/cerrar.png"></img>
    <div id="right-second">
    <img id="info-left-close-second-mobile" class="second-div-close" src="/wp-content/themes/twentysixteen-child/cross.png"></img>
          <div id="container-columns"> 
          	<div id="columns">
          </div> </div>
          <script type="text/javascript">
                // Your code here.
                    var loc = JSON.parse('<?php  echo json_encode($locations); ?>');
                    var lat, long;
                      //Gets user current position automatically
                      navigator.geolocation.getCurrentPosition(function(position) {
                            
                            lat = position.coords.latitude;
                            long = position.coords.longitude;
                            //prueba 
                            for (i = 0; i < loc.length; i++) {
                                loc[i]["distance"] = distance(loc[i]["latitude"],loc[i]["longitude"],lat,long);             
                              }
                            function compare(a,b) {
                              if (a.distance < b.distance) {
                                return -1;
                              } else return 1;
                            }

                            loc.sort(compare);

                            for (i = 0; i < loc.length; i++) {
                            document.getElementById("columns").innerHTML += 
                                '<figure>'+ 
                                '<div class="map-button" id="map-button'+loc[i]["id"] +'"> Mapa' +
                                '<i class="fa fa-globe" aria-hidden="true"></i>' +
                                '</div>' +
                                '<div id="columns-pictures" style="background: url('+loc[i]["foto"]+')center no-repeat; background-size:cover;"> </div>'+
                                '<figcaption>'+loc[i]["Titulo"]+'</figcaption>' +
                                '<figcaption>' +
                                loc[i]["contenido"].substring(0,150)+'...'+
                                '</figcaption>' +
                                '<figcaption>' +
                                +loc[i]["distance"].toFixed(2)+" Kilometros"+
                                '</figcaption>' +
                                '</figure>';
                            }

                        });

                      function distance(lat1, lon1, lat2, lon2) {
                          var theta = lon1 - lon2;
                          var dist = Math.sin(deg2rad(lat1)) * Math.sin(deg2rad(lat2)) +  Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.cos(deg2rad(theta));
                          dist = Math.acos(dist);
                          dist = rad2deg(dist);
                          var miles = dist * 60 * 1.1515 * 1.609344;

                          return miles;
                       }

                       function deg2rad (angle) {
                          return angle * 0.017453292519943295 // (angle / 180) * Math.PI;
                        }

                        function rad2deg (dist) {
                          return dist / 0.017453292519943295 // (angle / 180) * Math.PI;
                        }            
            </script>
            <script type="text/javascript">
            var button_temp;
            var i;
            window.onload = function(){
                if (loc){
                    for (i = 0; i < loc.length; i++) {
                        button_temp = document.getElementById("map-button"+loc[i]["id"]);
                        console.log(button_temp);
                        if (typeof window.addEventListener === 'function'){
                            (function (button_temp,i) {
                                button_temp.addEventListener('click', function(){
                                    google.maps.event.trigger(getMarkers(i), 'click');
                                    });
                                })(button_temp,i);
                            }
                            $('.map-button').stop().on("click", function (close_almao) {
                                close_almao.preventDefault();
                                $('#right-second').stop().animate({width: '0'});
                                $('#button-slide-menu').stop().hide();
                                $('#button-slide-menu-pint').stop().show();
                                $('#info-left-close-second').stop().hide();
                                $('#rangoLocalizacion').stop().hide();
                                $('#container').stop().animate({width: '100%'});
                                $('#botInfW').stop().show();
                                $('.rectangle-bottom').stop().show();
                            });
                    }

                }
                
               } 
            </script>
        </div>         
    <!-- BEGIN the party -->
<div id="container">
    <div id="geolocating-container"> 
        <div id="geolocating"> <h4> Localizando</h4> </div>
    </div>
         <div class="search-container">
                <div class="search-box">
                    <div class="search-icon"> <i class="icon ion-ios-search-strong"></i></div>
                    <input id="search" type="text" placeholder="Busca en Peque Agenda..." class="search-input" />
                    <ul id="results" class="search-results"></ul>
                </div>
            </div>
            <img class="rectangle-bottom" src="/wp-content/themes/twentysixteen-child/prueba2.png"></img>
            <div id="button-geolocate">
                <span class="fa-stack fa-lg">
                  <i class="fa fa-circle fa-stack-2x fa-inverse"></i>
                  <i class="fa fa-location-arrow fa-stack-1x"></i>
                </span>
            </div> 
             <div id="button-slide-menu">
                <span class="fa-stack fa-lg">
                  <i class="fa fa-circle fa-stack-2x fa-inverse"></i>
                  <i class="fa fa-calendar fa-stack-1x"></i>
                </span>
            </div>
            <div id="button-slide-menu-pint">
                <span class="fa-stack fa-lg">
                  <i class="fa fa-circle fa-stack-2x fa-inverse"></i>
                  <i class="fa fa-th fa-stack-1x"></i>
                </span>
            </div>
            <div class="se-pre-con classname">
                <img class="rectangle-mid" src="/wp-content/themes/twentysixteen-child/prueba2.png"></img>
            </div>     
    </div>
    <div id="download-store-container"> 
        <a href="https://itunes.apple.com/es/app/pequeagenda/id1181396255?mt=8"> <img class="appstore-logo" src="http://cclasarenas.com/wp-content/uploads/consiguelo-app-store.png" /> </a>
        <a href="https://play.google.com/store/apps/details?id=com.geotap.pequeagenda" > <img class="googleplay-logo" src="/wp-content/themes/twentysixteen-child/google.png" />  </a>
    </div> 
    <img style="
    right: 8px;
    bottom: 72px;
    position: absolute;
    z-index: 1;
    border-radius: 16%;
    width: 30px;
    height: 30px;
    box-shadow: 0px 2px 2px 0.1px #8c8c8c;" class="logopequeagendabottom" src="/wp-content/themes/twentysixteen-child/vector.png"></img>
<div id="map"> </div>
    <!-- JQUERY -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <!-- apikey google maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyANszbzxhpGtf3R30J0NG6FaSqKk_oOMis&callback=initMap"
        async defer> </script>
    <!-- font awesome icons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" />
    <!--Script javascript -->
    <script src="/wp-content/themes/twentysixteen-child/javascript.js"></script>
