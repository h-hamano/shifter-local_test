<?php
/*
Plugin Name:  EVA
Plugin URI:   https://github.com/getshifter
Description:  Must-use Shifter functions
Version:      1.1.0
Author:       DigitalCube
Author URI:   http://getshifter.io
License:      MIT License
*/


/**
 * It's a Trap
 * -----------
 * Abort if called directly or by a script
 *
 * @since  1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}


/**
 * Update Password Hash
 * Opt for sha256 over the WordPress default MD5
 *
 * @link   http://stackoverflow.com/questions/23949502/wordpress-sha-256-login
 * @since  1.1.0
*/
if ( !function_exists( 'wp_hash_password' ) ) {

  function wp_hash_password($password){

    return hash('sha256', $password);

  }
}

if ( !function_exists( 'wp_check_password' ) ) {

  function wp_check_password( $password, $hash, $user_id = '' ) {

    return wp_hash_password($password) == $hash;

  }
}



/**
 * Return if blog is not installed
 * Prevents errors from displaying on install page.
 *
 * @since  1.0.0 [<description>]
 */
if ( !is_blog_installed() ) {

  return;

}


/**
 * Register Default Theme Directory
 * --------------------------------
 * Add default WP themes directory so default themes are available
 *
 * @since  1.0.0
 */
register_theme_directory(ABSPATH . 'wp-content/themes');


/**
 * Autoload MU-Plugins
 * -------------------
 * Brilliant plugin by the roots.io team. Autoloads MU-plugins
 * that are loaded in directories.
 *
 * Original plugin: Bedrock Autoloader v1.0.0
 * @link https://github.com/roots/bedrock/
 * @link https://github.com/roots/bedrock/blob/master/web/app/mu-plugins/bedrock-autoloader.php
 *
 * @since  1.0.0
 */


class Shifter_Autoloader {
  private static $cache; // Stores our plugin cache and site option.
  private static $auto_plugins; // Contains the autoloaded plugins (only when needed).
  private static $mu_plugins; // Contains the mu plugins (only when needed).
  private static $count; // Contains the plugin count.
  private static $activated; // Newly activated plugins.
  private static $relative_path; // Relative path to the mu-plugins dir.
  private static $_single; // Let's make this a singleton.

  public function __construct() {

    if ( isset( self::$_single ) ) {
      return;
    }

    self::$_single       = $this; // Singleton set.
    self::$relative_path = '/../' . basename(__DIR__); // Rel path set.

    $this->checkInstall();
  }

  public function checkInstall() {

      if ( is_admin() ) {
        add_filter('show_advanced_plugins', array($this, 'showInAdmin'), 0, 2); // Admin only filter.
      }

      $this->loadPlugins();
  }

  /**
   * Run some checks then autoload our plugins.
   */
  public function loadPlugins() {

    $this->checkCache();
    $this->validatePlugins();
    $this->countPlugins();

    foreach ( self::$cache['plugins'] as $plugin_file => $plugin_info ) {
      include_once(WPMU_PLUGIN_DIR . '/' . $plugin_file);
    }

    $this->pluginHooks();
  }

  /**
   * Filter show_advanced_plugins to display the autoloaded plugins.
   */
  public function showInAdmin($bool, $type) {

    $screen  = get_current_screen();
    $current = is_multisite() ? 'plugins-network' : 'plugins';

    if ( $screen->{'base'} != $current || $type != 'mustuse' || !current_user_can('activate_plugins') ) {
      return $bool;
    }

    $this->updateCache(); // May as well update the transient cache whilst here.

    self::$auto_plugins = array_map( function ($auto_plugin) {
      $auto_plugin['Name'] .= ' *';
      return $auto_plugin;
    }, self::$auto_plugins);

    $GLOBALS['plugins']['mustuse'] = array_unique(array_merge(self::$auto_plugins, self::$mu_plugins), SORT_REGULAR);

    return false; // Prevent WordPress overriding our work.

  }

  /**
   * This sets the cache or calls for an update
   */
  private function checkCache() {

    $cache = get_site_option('shifter_autoloader');

    if ($cache === false) {
      return $this->updateCache();
    }

    self::$cache = $cache;

  }

  /**
   * Get the plugins and mu-plugins from the mu-plugin path and remove duplicates.
   * Check cache against current plugins for newly activated plugins.
   * After that, we can update the cache.
   */
  private function updateCache() {

    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    self::$auto_plugins = get_plugins( self::$relative_path );
    self::$mu_plugins   = get_mu_plugins( self::$relative_path );
    $plugins            = array_diff_key( self::$auto_plugins, self::$mu_plugins );
    $rebuild            = ! is_array( self::$cache['plugins'] );
    self::$activated    = ( $rebuild ) ? $plugins : array_diff_key( $plugins, self::$cache['plugins'] );
    self::$cache        = array( 'plugins' => $plugins, 'count' => $this->countPlugins() );

    update_site_option( 'shifter_autoloader', self::$cache );
  }

  /**
   * This accounts for the plugin hooks that would run if the plugins were
   * loaded as usual. Plugins are removed by deletion, so there's no way
   * to deactivate or uninstall.
   */
  private function pluginHooks() {

    if ( ! is_array( self::$activated ) ) {
      return;
    }

    foreach ( self::$activated as $plugin_file => $plugin_info ) {
      do_action( 'activate_' . $plugin_file );
    }

  }

  /**
   * Check that the plugin file exists, if it doesn't update the cache.
   */
  private function validatePlugins() {

    foreach ( self::$cache['plugins'] as $plugin_file => $plugin_info ) {

      if ( ! file_exists(WPMU_PLUGIN_DIR . '/' . $plugin_file ) ) {
        $this->updateCache();
        break;
      }

    }

  }

  /**
   * Count our plugins (but only once) by counting the top level folders in the
   * mu-plugins dir. If it's more or less than last time, update the cache.
   */
  private function countPlugins() {

    if ( isset( self::$count ) ) { return self::$count; }

    $count = count( glob( WPMU_PLUGIN_DIR . '/*/', GLOB_ONLYDIR | GLOB_NOSORT ) );

    if ( !isset( self::$cache['count'] ) || $count != self::$cache['count'] ) {
      self::$count = $count;
      $this->updateCache();
    }

    return self::$count;

  }

}

if ( is_blog_installed() ) {
  new Shifter_Autoloader();
}

/**
 * Hide upgrade notice
 *
 * @since  1.0.3
 */
function hide_update_notice() {
  remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action( 'admin_head', 'hide_update_notice', 1 );

/**
 * Remove Core Update
 *
 * @since  1.0.4
 */

function remove_core_updates () {
  global $wp_version;
  return(object) array(
       'last_checked'=> time(),
       'version_checked'=> $wp_version,
       'updates' => array()
  );
}
add_filter('pre_site_transient_update_core','remove_core_updates');


/**
 * Add all-in-one-wp-migration-unlimited-extension
 *
 * @since  1.1.0
 */

register_activation_hook( ABSPATH.'wp-content/plugins/all-in-one-wp-migration/all-in-one-wp-migration.php', function(){
  $newpath = 'all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension.php';
  if (! file_exists(ABSPATH.'wp-content/plugins/'.$newpath)) {
      $zip = new ZipArchive;
      if ($zip->open('/opt/dc_addon/all-in-one-wp-migration-unlimited-extension.zip') === TRUE) {
          $zip->extractTo(ABSPATH.'wp-content/plugins/');
          $zip->close();
          $cache_plugins = wp_cache_get( 'plugins', 'plugins' );

          $new_plugin = array(
            'Name' => '',
            'PluginURI' => '',
            'Version' => '',
            'Description' => '',
            'Author' => '',
            'AuthorURI' => '',
            'TextDomain' => '',
            'DomainPath' => '',
            'Network' => '',
            'Title' => '',
            'AuthorName' => '',
          );
          $cache_plugins[''][$newpath] = $new_plugin;
          wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
      }
  }

  activate_plugin($newpath);
} );
