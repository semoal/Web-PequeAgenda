<?php


// Modify Responses WP Rest Api
add_action( 'rest_api_init', 'featured_image_thumbnail_url' );

// Modifying Responses
function featured_image_thumbnail_url() {

	// More info http://v2.wp-api.org/extending/modifying/
	register_rest_field(
		'event_type',
		'thumbnail_url',
		array(
			'get_callback'    => 'get_featured_image_url',
			'update_callback' => null,
			'schema'          => null,
		)
	);
}

/**
 * Get the value of the "featured_image_url" field
 *
 * @param array $object Details of current post.
 * @param string $field_name Name of field.
 * @param WP_REST_Request $request Current request
 *
 * @return mixed
 */
function get_featured_image_url( $object, $field_name, $request ) {
	$thumbnail_id = get_post_thumbnail_id( $object->ID );
	return wp_get_attachment_image_src($thumbnail_id, 'full');
}

//Filtro

function my_allow_meta_query( $valid_vars ) {
	$valid_vars = array_merge( $valid_vars, array( 'meta_key', 'meta_value', 'meta_query' ) ); // Omit meta_key, meta_value if you don't need them
	return $valid_vars;
}
add_filter( 'rest_query_vars', 'my_allow_meta_query' );


