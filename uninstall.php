<?php // ------------------------------------------------------------------------------------------------------------------------ //

require_once('usi-variable-solutions.php');

final class USI_Variable_Solutions_Uninstall {

   const VERSION = '1.1.0 (2019-05-14)';

   private function __construct() {
   } // __construct();

   static function uninstall() {

      global $wpdb;

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

//      USI_Settings_Uninstall::uninstall(
//         USI_Variable_Solutions::NAME, 
//         USI_Variable_Solutions::PREFIX, 
//         USI_Variable_Solutions::$capabilities
//      );

      $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}USI_variables");

      delete_metadata('user', null, $wpdb->prefix . USI_Variable_Solutions::PREFIX . '-options-category', null, true);

   } // uninstall();

} // Class USI_Variable_Solutions_Uninstall;

USI_Variable_Solutions_Uninstall::uninstall();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
