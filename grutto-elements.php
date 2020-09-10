<?php
/**
 * Plugin Name:       Grutto Elements
 * Plugin URI:        https://alimir.ir
 * Description:       Grutto Elements Toolkit
 * Version:           1.0.0
 * Author:            Alimir
 * Author URI:        https://alimir.ir
 * Text Domain:       grutto-elements
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die('No Naughty Business Please !');
}

// Abort loading if WordPress is upgrading
if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
    return;
}

// Define path and text domain
define( 'GRUTTO_API_VERSION'  , '1.0.0' );
define( 'GRUTTO_DIR'          , plugin_dir_path( __FILE__ ) );
define( 'GRUTTO_URL'          , plugin_dir_url(  __FILE__ ) );
define( 'GRUTTO_DOMAIN'       , 'grutto-elements' );
define( 'GRUTTO_NAME'         , 'Grutto Elements' );
define( 'GRUTTO_VERSION_SLUG' , 'v1' );

class Grutto_Elements_Init {

  /**
   * Unique identifier for your plugin.
   *
   * The variable name is used as the text domain when internationalizing strings of text.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected $plugin_slug = GRUTTO_DOMAIN;

  /**
   * Instance of this class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected static $instance = null;


  /**
   * Instance of Admin class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  public $admin = null;

  /**
   * Initialize the plugin
   *
   * @since     1.0.0
   */
  private function __construct() {

    $this->includes();

    add_action( 'init', array( $this, 'init' ) );

    // Activate plugin when new blog is added
    add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

    // Loaded action
    do_action( 'grutto_elements_loaded' );
  }


  /**
   * Init the plugin when WordPress Initialises.
   *
   * @return void
   */
  public function init(){}


  /**
   *
   * @return [type] [description]
  */
  private function includes() {

      // Composer include
      require __DIR__ . '/vendor/autoload.php';

      // Auto-load classes on demand
      if ( function_exists( "__autoload" ) ) {
          spl_autoload_register( "__autoload" );
      }
      spl_autoload_register( array( $this, 'autoload' ) );

      // load common functionalities
      include_once( GRUTTO_DIR . '/includes/index.php' );

      if ( is_admin() ) {
        include_once( GRUTTO_DIR . '/admin/index.php' );
      }

  }

  /**
   * Auto-load classes on demand to reduce memory consumption
   *
   * @param mixed $class
   * @return void
   */
  public function autoload( $class ) {
      $path  = null;
      $class = strtolower( $class );
      $file = 'class-' . str_replace( '_', '-', $class ) . '.php';

      // the possible pathes containing classes
      $possible_pathes = array(
          GRUTTO_DIR . '/includes/classes/',
          GRUTTO_DIR . '/admin/classes/'
      );

      foreach ( $possible_pathes as $path ) {
          if( is_readable( $path . $file ) ){
              include_once( $path . $file );
              return;
          }

      }
  }

    /**
    * Fired when the plugin is activated.
    *
    * @since    3.1
    *
    * @param    boolean    $network_wide    True if WPMU superadmin uses
    *                                       "Network Activate" action, false if
    *                                       WPMU is disabled or plugin is
    *                                       activated on an individual blog.
    */
    public static function activate( $network_wide ) {

      if ( function_exists( 'is_multisite' ) && is_multisite() ) {

        if ( $network_wide  ) {

          // Get all blog ids
          $blog_ids = self::get_blog_ids();

          foreach ( $blog_ids as $blog_id ) {

            switch_to_blog( $blog_id );
            self::single_activate();
          }

          restore_current_blog();

        } else {
          self::single_activate();
        }

      } else {
        self::single_activate();
      }
  }

  /**
   * Fired when the plugin is deactivated.
   *
   * @since    3.1
   *
   * @param    boolean    $network_wide    True if WPMU superadmin uses
   *                                       "Network Deactivate" action, false if
   *                                       WPMU is disabled or plugin is
   *                                       deactivated on an individual blog.
   */
    public static function deactivate( $network_wide ) {

      if ( function_exists( 'is_multisite' ) && is_multisite() ) {

          if ( $network_wide ) {

              // Get all blog ids
              $blog_ids = self::get_blog_ids();

              foreach ( $blog_ids as $blog_id ) {
                  switch_to_blog( $blog_id );
                  self::single_deactivate();
              }

              restore_current_blog();

          } else {
              self::single_deactivate();
          }

      } else {
          self::single_deactivate();
      }

  }


  /**
   * Fired for each blog when the plugin is activated.
   *
   * @since    3.1
   */
  private static function single_activate() {
    //Use wp_next_scheduled to check if the event is already scheduled
    $timestamp = wp_next_scheduled( 'grutto_create_daily_scheduled' );

    //If $timestamp == false schedule daily backups since it hasn't been done previously
    if( $timestamp == false ){
      //Schedule the event for right now, then to repeat daily using the hook 'grutto_create_daily_scheduled'
      wp_schedule_event( time(), 'daily', 'grutto_create_daily_scheduled' );
    }

    do_action( 'wp_ulike_activated', get_current_blog_id() );
  }


  /**
   * Fired for each blog when the plugin is deactivated.
   *
   * @since    3.1
   */
  private static function single_deactivate() {
    wp_clear_scheduled_hook( 'grutto_create_daily_scheduled' );

    do_action( 'wp_ulike_deactivated' );
  }


  /**
   * Fired when a new site is activated with a WPMU environment.
   *
   * @since    3.1
   *
   * @param    int    $blog_id    ID of the new blog.
  */
  public function activate_new_site( $blog_id ) {
      if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
        return;
      }

      switch_to_blog( $blog_id );
      self::single_activate();
      restore_current_blog();
  }

  /**
   * Get all blog ids of blogs in the current network that are:
   * - not archived
   * - not spam
   * - not deleted
   *
   * @since    3.1
   *
   * @return   array|false    The blog ids, false if no matches.
   */
  private static function get_blog_ids() {
      global $wpdb;

      // get an array of blog ids
      $sql = "SELECT blog_id FROM $wpdb->blogs
      WHERE archived = '0' AND spam = '0'
      AND deleted = '0'";

      return $wpdb->get_col( $sql );
  }

	/**
	* Return an instance of this class.
	*
	* @since     1.0.0
	*
	* @return    object    A single instance of this class.
	*/
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
		  self::$instance = new self;
		}

	return self::$instance;
  }


}

// Register hooks that are fired when the plugin is activated or deactivated.
register_activation_hook  ( __FILE__, array( 'Grutto_Elements_Init', 'activate'   ) );
register_deactivation_hook( __FILE__, array( 'Grutto_Elements_Init', 'deactivate' ) );

Grutto_Elements_Init::get_instance();