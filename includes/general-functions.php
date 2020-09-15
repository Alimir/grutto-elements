<?php

/**
 * Check variable status
 *
 * @return void
 */
function grutto_is_true( $var ) {
    if ( is_bool( $var ) ) {
        return $var;
    }
    if ( is_string( $var ) ){
        $var = strtolower( $var );
        if( in_array( $var, array( 'yes', 'on', 'true', 'checked' ) ) ){
            return true;
        }
    }
    if ( is_numeric( $var ) ) {
        return (bool) $var;
    }
    return false;
}

/**
 * Send daily report via email
 *
 * @return void
 */
function grutto_send_daily_report() {

    global $woocommerce, $wpdb, $product;

    if( ! is_object( $woocommerce ) ){
        return;
    }

    include_once($woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php');

    // WooCommerce Admin Report
    $wc_report = new WC_Admin_Report();

    // Set date parameters for the current day
    $current_date = strtotime( date('Y-m-d', current_time('timestamp') ) );
    $wc_report->start_date = $current_date;
    $wc_report->end_date   = $current_date;

    // Avoid max join size error
    $wpdb->query('SET SQL_BIG_SELECTS=1');

    // Get data for current month sold products
    $sold_products = $wc_report->get_order_report_data(array(
        'data' => array(
            '_product_id' => array(
                'type' => 'order_item_meta',
                'order_item_type' => 'line_item',
                'function' => '',
                'name' => 'product_id'
            ),
            '_qty' => array(
                'type' => 'order_item_meta',
                'order_item_type' => 'line_item',
                'function' => 'SUM',
                'name' => 'quantity'
            ),
            '_line_total' => array(
                'type' => 'order_item_meta',
                'order_item_type' => 'line_item',
                'function' => 'SUM',
                'name' => 'gross_after_discount'
            )
        ),
        'query_type'   => 'get_results',
        'group_by'     => 'product_id',
        'where_meta'   => '',
        'order_by'     => 'quantity DESC',
        'order_types'  => wc_get_order_types('order_count'),
        'filter_range' => TRUE,
        'order_status' => array('completed'),
    ));

    if( ! empty( $sold_products ) ){

        $table_info = '';
        foreach ( $sold_products as $key => $data ) {
            $product    = wc_get_product( $data->product_id );

            if( ! $product ){
                continue;
            }

            $table_info .= sprintf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $product->get_sku(),
                $product->get_title(),
                $product->get_price(),
                $data->quantity
            );
        }

        if( empty( $table_info ) ){
            return;
        }

        $body = sprintf( '
            <html>
            <head>
                <title>%s</title>
            </head>
            <body>
                <table>
                    <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr></thead>
                    <tbody>%s</tbody>
                </table>
            </body>
            </html>',
            __( 'Grutto Daily Report', GRUTTO_DOMAIN ),
            __( 'Product SKU', GRUTTO_DOMAIN ),
            __( 'Product Name', GRUTTO_DOMAIN ),
            __( 'Product Price', GRUTTO_DOMAIN ),
            __( 'Sold Items Quantity', GRUTTO_DOMAIN ),
            $table_info
        );

        wp_mail( get_option( 'admin_email', 'info@grutto.com' ), __( 'Grutto Daily Report', GRUTTO_DOMAIN ), $body );
    }

}