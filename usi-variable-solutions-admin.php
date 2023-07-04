<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

final class USI_Variable_Solutions_Admin {

   const VERSION = '2.5.0 (2023-07-04)';

   private function __construct() {
   } // __construct();

   public static function _init() {

      if (is_admin()) {

         USI_Variable_Solutions::$variables_add     = USI_WordPress_Solutions_Capabilities::current_user_can(USI_Variable_Solutions::PREFIX, 'add-variables');
         USI_Variable_Solutions::$variables_change  = USI_WordPress_Solutions_Capabilities::current_user_can(USI_Variable_Solutions::PREFIX, 'change-values');
         USI_Variable_Solutions::$variables_delete  = USI_WordPress_Solutions_Capabilities::current_user_can(USI_Variable_Solutions::PREFIX, 'delete-variables');
         USI_Variable_Solutions::$variables_edit    = USI_WordPress_Solutions_Capabilities::current_user_can(USI_Variable_Solutions::PREFIX, 'edit-variables');
         USI_Variable_Solutions::$variables_publish = USI_WordPress_Solutions_Capabilities::current_user_can(USI_Variable_Solutions::PREFIX, 'publish-variables');

         if (!defined('WP_UNINSTALL_PLUGIN')) {
            require_once('usi-variable-solutions-settings.php'); 
            require_once('usi-variable-solutions-table.php');
            require_once('usi-variable-solutions-variable.php');
         }

      } // ENDIF is_admin();

   } // _init();

} // Class USI_Variable_Solutions_Admin;

// Fires after WordPress has finished loading but before any headers are sent;
add_action('init', array('USI_Variable_Solutions_Admin', '_init'), 1);

// --------------------------------------------------------------------------------------------------------------------------- // ?>