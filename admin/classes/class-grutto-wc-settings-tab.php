<?php

class Grutto_WC_Settings_Tab {

    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_grutto_tab', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_grutto_tab', __CLASS__ . '::update_settings' );
    }

    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param [type] $settings_tabs
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['grutto_tab'] = __( 'Grutto Elements', GRUTTO_DOMAIN );
        return $settings_tabs;
    }


    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @return void
     */
    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }


    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function
     *
     * @return void
     */
    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }

    /**
     * et all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {

        $settings = array(
            'section_ftp' => array(
                'name'     => __( 'FTP Connect', GRUTTO_DOMAIN ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'grutto_wc_settings_tab_section_title'
            ),
            'server_address' => array(
                'name' => __( 'Server Address', GRUTTO_DOMAIN ),
                'type' => 'text',
                'id'   => 'grutto_wc_settings_tab_server_ip'
            ),
            'server_port' => array(
                'name' => __( 'Server Port', GRUTTO_DOMAIN ),
                'type' => 'text',
                'id'   => 'grutto_wc_settings_tab_server_port'
            ),
            'user_name' => array(
                'name' => __( 'User Name', GRUTTO_DOMAIN ),
                'type' => 'text',
                'id'   => 'grutto_wc_settings_tab_user_name'
            ),
            'user_pass' => array(
                'name' => __( 'Password', GRUTTO_DOMAIN ),
                'type' => 'password',
                'id'   => 'grutto_wc_settings_tab_user_pass'
            ),
            'directory_path' => array(
                'name' => __( 'File Directory Path', GRUTTO_DOMAIN ),
                'type' => 'text',
                'id'   => 'grutto_wc_settings_tab_directory_path'
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'grutto_wc_settings_tab_section_end'
            )
        );

        return apply_filters( 'grutto_wc_settings_tab', $settings );
    }

}