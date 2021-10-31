<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/* 
Author:            Jim Schwanda
Author URI:        https://www.usi2solve.com/leader
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
Version:           2.4.1
*/

/*
Variable-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
Variable-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with Variable-Solutions. If not, see 
https://github.com/jaschwanda/variable-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

// IF required plugins are not available;
if (!is_dir(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions')) {
   add_action('admin_notices', function () {
      global $pagenow;
      if ('plugins.php' == $pagenow) echo '<div class="notice notice-warning is-dismissible"><p>' . 
         __('The <b>WordPress-Solutions</b> plugin is required for <b>Variable-Solutions</b> to run properly.') . '</p></div>';
   });
   goto END_OF_FILE;
} // ENDIF required plugins are not available;

class USI_Variable_Solutions {

   const VERSION    = '2.4.1 (2021-10-30)';

   const NAME       = 'Variable-Solutions';
   const PREFIX     = 'usi-variable';
   const TEXTDOMAIN = 'usi-variable-solutions';

   const VARYADD    = 'usi-variable-add';
   const VARYLIST   = 'usi-variable-list';

   public static $capabilities = array(
      'view-variables'    => 'View Variables|administrator',
      'change-values'     => 'Change Values|administrator',
      'add-variables'     => 'Add Variables|administrator',
      'edit-variables'    => 'Edit Variables|administrator',
      'delete-variables'  => 'Delete Variables|administrator',
      'publish-variables' => 'Publish Variables|administrator',
      'view-settings'     => 'View Settings|administrator',
      'edit-preferences'  => 'Edit Preferences|administrator',
   );

   public static $options = array();

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
         $defaults['preferences']['file-location']      = 'plugin';
         $defaults['preferences']['menu-icon']          = 'dashicons-controls-repeat';
         $defaults['preferences']['menu-position']      = 'null';
         $defaults['preferences']['shortcode-function'] = 'usi_variable_shortcode';
         $defaults['preferences']['shortcode-prefix']   = 'variable';
         $defaults['preferences']['variable-prefix']    = $wpdb->prefix;
         self::$options = get_option(self::PREFIX . '-options', $defaults);
      }

      $shortcode_prefix   = USI_Variable_Solutions::$options['preferences']['shortcode-prefix'];
      $shortcode_function = USI_Variable_Solutions::$options['preferences']['shortcode-function'];
      add_shortcode($shortcode_prefix, $shortcode_function);

      switch ($location = self::get_variables_folder()) {
      default: 
      case 'plugin': @ include_once('variables.php'); break;
      case 'root'  : @ include_once(ABSPATH . 'variables.php'); break;
      case 'theme' : @ include_once(get_theme_root() . '/variables.php'); break;
      }

      if (is_admin()) {

         self::$variables_add     = USI_WordPress_Solutions_Capabilities::current_user_can(self::PREFIX, 'add-variables');
         self::$variables_change  = USI_WordPress_Solutions_Capabilities::current_user_can(self::PREFIX, 'change-values');
         self::$variables_delete  = USI_WordPress_Solutions_Capabilities::current_user_can(self::PREFIX, 'delete-variables');
         self::$variables_edit    = USI_WordPress_Solutions_Capabilities::current_user_can(self::PREFIX, 'edit-variables');
         self::$variables_publish = USI_WordPress_Solutions_Capabilities::current_user_can(self::PREFIX, 'publish-variables');

         require_once('usi-variable-solutions-table.php');
         require_once('usi-variable-solutions-variable.php');

         if (!defined('WP_UNINSTALL_PLUGIN')) {
            require_once('usi-variable-solutions-settings.php'); 
            if (!empty(USI_Variable_Solutions::$options['updates']['git-update'])) {
               require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-update.php');
               new USI_WordPress_Solutions_Update_GitHub(__FILE__, 'jaschwanda', 'variable-solutions', null, !empty(USI_Variable_Solutions::$options['updates']['force-update']));
            }
         }

      }

   } // _init();

   public static function get_variables_folder() {
      if (!empty(self::$options['preferences']['file-location'])) {
         return(self::$options['preferences']['file-location']);
      }
      return('plugin');
   } // get_variables_folder();

} // Class USI_Variable_Solutions;

// Fires after the plugin is activated;
register_activation_hook(__FILE__, array('USI_Variable_Solutions', '_activation'));

// Fires after WordPress has finished loading but before any headers are sent;
add_action('init', array('USI_Variable_Solutions', '_init'), 1);

END_OF_FILE: // -------------------------------------------------------------------------------------------------------------- // ?>