<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die('No Naughty Business Please !');
}

/**
 * Add fields to product advanced tab
 *
 * @return void
 */
function grutto_add_product_options(){

    echo '<div class="options_group">';

	woocommerce_wp_checkbox( array(
        'id'      => 'enable_product_export',
		'value'   => get_post_meta( get_the_ID(), 'enable_product_export', true ),
		'label'   => __( 'Enable Product Export', GRUTTO_DOMAIN )
    ));

    echo '</div>';

 }
add_action( 'woocommerce_product_options_advanced', 'grutto_add_product_options');

/**
 * Save custom fields
 *
 * @param integer $post_id
 * @param object  $post
 * @return void
 */
function grutto_product_custom_fields_save( $post_id, $post ){

	$woocommerce_product_checkbox = isset( $_POST['enable_product_export'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, 'enable_product_export', $woocommerce_product_checkbox );

}
add_action( 'woocommerce_process_product_meta', 'grutto_product_custom_fields_save', 10, 2 );