<?php


/**
 * Hook our function , grutto_init_daily_scheduled(), into the action grutto_create_daily_scheduled
 *
 * @return void
 */
function grutto_init_daily_scheduled(){
    // Download FTP Files
    $ftp = new Grutto_FTP;
    $ftp->download_files();
    // Update product info
    new Grutto_Product_Updater;
    // Send daily report
    grutto_send_daily_report();
}
add_action( 'grutto_create_daily_scheduled', 'grutto_init_daily_scheduled' );