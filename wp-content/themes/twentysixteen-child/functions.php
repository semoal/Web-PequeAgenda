<?php

function include_libraries()
{
    // Here we load from our includes directory
    // This considers parent and child themes as well    

    locate_template( array( 'includes/api_extension.php' ), true, true );

}
add_action( 'after_setup_theme', 'include_libraries' );


add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style')
    );
}

/*add_action( 'wp_enqueue_scripts', 'add_my_script' );
function add_my_script() {
    wp_enqueue_script(
        'main-javascript-jquery', // name your script so that you can attach other scripts and de-register, etc.
        get_template_directory_uri() . '/js/javascript.js', // this is the location of your script file
        array('jquery') // this array lists the scripts upon which your script depends
    );
}*/



if ( ! function_exists('event_type') ) {

// Register Custom Post Type
function event_type() {

	$labels = array(
		'name'                  => _x( 'Eventos', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Evento', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Eventos', 'text_domain' ),
		'name_admin_bar'        => __( 'Eventos', 'text_domain' ),
		'archives'              => __( 'Archivo de eventos', 'text_domain' ),
		'parent_item_colon'     => __( 'Evento padre', 'text_domain' ),
		'all_items'             => __( 'Todos los eventos', 'text_domain' ),
		'add_new_item'          => __( 'Añadir nuevo evento', 'text_domain' ),
		'add_new'               => __( 'Añadir evento', 'text_domain' ),
		'new_item'              => __( 'Nuevo evento', 'text_domain' ),
		'edit_item'             => __( 'Editar evento', 'text_domain' ),
		'update_item'           => __( 'Actualizar evento', 'text_domain' ),
		'view_item'             => __( 'Ver evento', 'text_domain' ),
		'search_items'          => __( 'Buscar evento', 'text_domain' ),
		'not_found'             => __( 'No encontrado', 'text_domain' ),
		'not_found_in_trash'    => __( 'No encontrado en la papelera', 'text_domain' ),
		'featured_image'        => __( 'Imagen destacada', 'text_domain' ),
		'set_featured_image'    => __( 'Poner imagen destacada', 'text_domain' ),
		'remove_featured_image' => __( 'Quitar imagen destacada', 'text_domain' ),
		'use_featured_image'    => __( 'Usar como imagen destacada', 'text_domain' ),
		'insert_into_item'      => __( 'Insertar en el elemento', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Subir este elemento', 'text_domain' ),
		'items_list'            => __( 'Lista de elementos', 'text_domain' ),
		'items_list_navigation' => __( 'Navegación de la lista de elementos', 'text_domain' ),
		'filter_items_list'     => __( 'Filtrado de lista de elementos', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Evento', 'text_domain' ),
		'description'           => __( 'Eventos para niños', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-calendar-alt',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,		
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
  		'publicly_queryable' => true, //A partir de aqui para el REST
  		'query_var'          => true,
  		'rewrite'            => array( 'slug' => 'event_type' ),
  		'show_in_rest'       => true,
  		'rest_base'          => 'events-api',
  		'rest_controller_class' => 'WP_REST_Posts_Controller',
	);
	register_post_type( 'event_type', $args );

}
add_action( 'init', 'event_type', 0 );

}

// Show posts of 'post', 'page' and 'movie' post types on home page
add_action( 'pre_get_posts', 'add_my_post_types_to_query' );

function add_my_post_types_to_query( $query ) {
  if ( is_home() && $query->is_main_query() )
    $query->set( 'post_type', array( 'event_type' ) );
  return $query;
}

// API de google para los campos personalizados
function my_acf_google_map_api( $api ){
	
	$api['key'] = 'AIzaSyD7elnKl2JLAqsKm25jZ8VTqh_D1sb6KV0';
	
	return $api;
	
}

add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');



