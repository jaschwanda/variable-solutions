<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

if (!class_exists('USI_Settings')) { class USI_Settings {

   const VERSION = '1.0.0 (2017-10-29)';

   public static $options = array();

} /* Class USI_Settings; */ }

if (!class_exists('USI_Sort_Solutions_Settings')) {
   final class USI_Sort_Solutions_Settings {
      const VERSION = '1.0.0 (2017-10-29)';
      function __construct() {
         add_filter('custom_menu_order', function() { 
            return(true); 
         });
         add_filter('menu_order', function($menu_order) {
            global $submenu;
            $keys = array();
            $names = array();
            $options = array();
            if (!empty($submenu['options-general.php'])) {
               foreach ($submenu['options-general.php'] as $key => $option) {
                  if (!empty($option[2]) && preg_match('/^usi\-\w+-settings/', $option[2])) {
                     $keys[] = $key;
                     $names[] = $option[0];
                     $options[] = $option;
                     unset($submenu['options-general.php'][$key]);
                  }
               }
            }
            asort($names);
            foreach ($names as $index => $value) {
               $submenu['options-general.php'][$keys[$index]] = $options[$index];
            }
            return($menu_order);
         });
      } // __construct();
   } // Class USI_Sort_Solutions_Settings;
   new USI_Sort_Solutions_Settings();
} // ENDIF USI_Sort_Solutions_Settings exists;

if (!function_exists('usi_history')) {
   function usi_history($action) {
      global $wpdb;
      $wpdb->insert($wpdb->prefix . 'USI_history', 
         array(
            'action' => $action,
            'user_id' => get_current_user_id(), 
         )
      );
   } // usi_history();
} // ENDIF function_exists('usi_history');

if (!function_exists('usi_log')) {
   function usi_log($action) {
      global $wpdb;
      $wpdb->insert($wpdb->prefix . 'USI_log', 
         array(
            'action' => $action,
            'user_id' => get_current_user_id(), 
         )
      );
   } // usi_log();
} // ENDIF function_exists('usi_log');

// --------------------------------------------------------------------------------------------------------------------------- // ?>