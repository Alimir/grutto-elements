<?php

use altayalp\FtpClient\Servers\FtpServer;
use altayalp\FtpClient\FileFactory;


class Grutto_FTP{

    protected $session;
    protected $local_path = GRUTTO_DIR . 'admin/temp/';

	public function __construct(){
        $this->connect();
    }

    /**
     * Connect to ftp server
     *
     * @return void
     */
    private function connect(){
        try {
            $server = new FtpServer( $this->get_option( 'server_ip' ), $this->get_option( 'server_port' ) );
            $server->login( $this->get_option('user_name'), $this->get_option('user_pass') );
            $server->turnPassive();
            $this->session = $server;
        } catch (Exception $e) {
            error_log( $e->getMessage() );
        }
    }

    /**
     * Simple get option function
     *
     * @param string $option_name
     * @return string
     */
    private function get_option( $option_name, $force_empty = false ){
        $option_value = get_option( 'grutto_wc_settings_tab_' . $option_name );

        if( ! $force_empty && empty( $option_value ) ){
            throw new Exception( 'grutto_wc_settings_tab_' . $option_name );
        }

        return $option_value;
    }

    /**
     * Download all files to local path
     *
     * @return void
     */
    public function download_files(){
        try {
            $file = FileFactory::build( $this->session );
            $list = $file->ls( $this->get_option( 'directory_path' , true ) );

            foreach ( $list as $key => $file_name ) {
                if( ! file_exists( $this->local_path . $file_name ) ){
                    $file->download( $file_name, $this->local_path . $file_name );
                }
            }
        } catch (Exception $e) {
            error_log( $e->getMessage() );
        }
    }

}