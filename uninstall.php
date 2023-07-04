<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

require_once(WP_PLUGIN_DIR . '/usi-wordpress-solutions/usi-wordpress-solutions-capabilities.php');
require_once(WP_PLUGIN_DIR . '/usi-wordpress-solutions/usi-wordpress-solutions-uninstall.php');

require_once('usi-variable-solutions.php');

final class USI_Variable_Solutions_Uninstall {

   const VERSION = '2.5.0 (2023-07-04)';

   private function __construct() {
   } // __construct();

   static function uninstall() {

      global $wpdb;

      $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}USI_variables");

      USI_WordPress_Solutions_Capabilities::remove(USI_Variable_Solutions::PREFIX, USI_Variable_Solutions::$capabilities);

   } // uninstall();

} // Class USI_Variable_Solutions_Uninstall;

USI_WordPress_Solutions_Uninstall::uninstall(USI_Variable_Solutions::PREFIX);

USI_Variable_Solutions_Uninstall::uninstall();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
