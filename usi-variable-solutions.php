<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/* 
Author:            Jim Schwanda
Author URI:        https://www.usi2solve.com/leader
Copyright:         2023 by Jim Schwanda.
Description:       The Variable-Solutions plugin extends WordPress enabling the creation and management of variables that can be referenced as short codes in WordPress content and/or as defined variables in the supporting PHP files. It is a thin plugin that loads only one file when running in end user mode. The Variable-Solutions plugin is developed and maintained by Universal Solutions.
Donate link:       https://www.usi2solve.com/donate/variable-solutions
License:           GPL-3.0
License URI:       https://github.com/jaschwanda/variable-solutions/blob/master/LICENSE.md
Plugin Name:       Variable-Solutions
Plugin URI:        https://github.com/jaschwanda/variable-solutions
Requires at least: 5.0
Requires PHP:      5.6.25
Tested up to:      5.3.2
Text Domain:       usi-variable-solutions
Version:           2.5.0
Warranty:          This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

if (!class_exists('USI')) {
   add_action('admin_notices', function() { echo'<div class="notice notice-error"><p>WordPress-Solutions plugin missing.</p></div>'; }); goto END_OF_FILE;
}

class USI_Variable_Solutions {

   const VERSION    = '2.5.0 (2023-07-04)';

   const NAME       = 'Variable-Solutions';
   const PREFIX     = 'usi-variable';
   const TEXTDOMAIN = 'usi-variable-solutions';

   const VARYADD    = 'usi-variable-add';
   const VARYLIST   = 'usi-variable-list';

   public static $capabilities = [
      'view-variables'    => 'View Variables|administrator',
      'change-values'     => 'Change Values|administrator',
      'add-variables'     => 'Add Variables|administrator',
      'edit-variables'    => 'Edit Variables|administrator',
      'delete-variables'  => 'Delete Variables|administrator',
      'publish-variables' => 'Publish Variables|administrator',
      'view-settings'     => 'View Settings|administrator',
      'edit-preferences'  => 'Edit Preferences|administrator',
   ];

   public static $options = [];

   public static $variables_add     = false;
   public static $variables_change  = false;
   public static $variables_delete  = false;
   public static $variables_edit    = false;
   public static $variables_publish = false;

   private function __construct() {
   } // __construct();

   public static function _activation() {
      require_once('usi-variable-solutions-install.php');
   } // _activation();

   public static function _init() {

      if (empty(self::$options)) {
         global $wpdb;
         $defaults['preferences']['file-location']      = 'root';
         $defaults['preferences']['menu-icon']          = 'dashicons-controls-repeat';
         $defaults['preferences']['menu-position']      = 'null';
         $defaults['preferences']['shortcode-function'] = 'usi_variable_shortcode';
         $defaults['preferences']['shortcode-prefix']   = 'variable';
         $defaults['preferences']['variable-prefix']    = $wpdb->prefix;
         self::$options = get_option(self::PREFIX . '-options', $defaults);
      }

      $shortcode_prefix   = self::$options['preferences']['shortcode-prefix'];
      $shortcode_function = self::$options['preferences']['shortcode-function'];
      add_shortcode($shortcode_prefix, $shortcode_function);

      switch (self::get_variables_folder()) {
      default: 
      case 'plugin': @ include_once('variables.php'); break;
      case 'root'  : @ include_once(ABSPATH . 'variables.php'); break;
      case 'theme' : @ include_once(get_theme_root() . '/variables.php'); break;
      }

      if (is_admin()) {
         require_once('usi-variable-solutions-admin.php');
         // Fires after the plugin is activated;
         register_activation_hook(__FILE__, ['USI_Variable_Solutions', '_activation']);
      } // ENDIF is_admin();

   } // _init();

   public static function get_variables_folder() {
      if (!empty(self::$options['preferences']['file-location'])) return(self::$options['preferences']['file-location']);
      return('root');
   } // get_variables_folder();

} // Class USI_Variable_Solutions;

USI_Variable_Solutions::_init();

END_OF_FILE: // -------------------------------------------------------------------------------------------------------------- // ?>