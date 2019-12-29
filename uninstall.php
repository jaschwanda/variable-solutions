<?php // ------------------------------------------------------------------------------------------------------------------------ //

require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-uninstall.php');

require_once('usi-variable-solutions.php');

final class USI_Variable_Solutions_Uninstall {

   const VERSION = '1.2.0 (2019-12-29)';

   private function __construct() {
   } // __construct();

   static function uninstall() {

      global $wpdb;

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

      $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}USI_variables");

   } // uninstall();

} // Class USI_Variable_Solutions_Uninstall;

USI_WordPress_Solutions_Uninstall::uninstall(USI_Variable_Solutions::PREFIX);

USI_Variable_Solutions_Uninstall::uninstall();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
