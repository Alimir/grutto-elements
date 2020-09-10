<?php

class Grutto_Product_Updater{

    protected $wpdb;
    protected $query_list;
    protected $local_path = GRUTTO_DIR . 'admin/temp/';

	public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->set_query_list();
        $this->update_product();
    }

    private function set_query_list(){
        $this->query_list = array_slice( scandir( $this->local_path ), 2 );
    }

    private function update_product(){
        if( ! is_array( $this->query_list ) ){
            return;
        }

        foreach ($this->query_list as $key => $file) {
            $file_data = $this->get_file_data( $file );
            if( ! $file_data ){
                return;
            }

            $sku = pathinfo( $file, PATHINFO_FILENAME );

            $product = $this->get_product_by_sku( $sku );

            if ( $product && isset( $file_data[1] ) ) {
                $product->set_regular_price( $file_data[1] );
                $product->save();
            }
        }

    }

    private function get_product_by_sku( $sku ) {
        $product_id = $this->wpdb->get_var(
            $this->wpdb->prepare( "SELECT post_id FROM {$this->wpdb->postmeta} WHERE meta_key='_sku' AND meta_value = '%s' LIMIT 1", $sku )
        );

        if ( $product_id && function_exists( 'wc_get_product' ) ){
            return wc_get_product( $product_id );
        }

        return false;
    }

    private function get_file_data( $file_name ){
        $file_content = @fopen( $this->local_path . $file_name, 'r');
        if ( $file_content ) {
           return explode( ";", fread( $file_content, filesize( $this->local_path . $file_name ) ) );
        }

        return false;
    }

}