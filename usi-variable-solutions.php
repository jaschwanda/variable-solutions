<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/* 
Plugin Name: Variable-Solutions
Plugin URI:  https://github.com/jaschwanda/variable-solutions
Description: The Variable-Solutions plugin extends WordPress enabling the creation and management of variables that can be referenced as short codes in WordPress content and/or as defined variables in the supporting PHP files. It is a thin plugin that loads only one file when running in end user mode. The Variable-Solutions plugin is developed and maintained by Universal Solutions.
Version:     1.1.0 (2019-05-14)
Author:      Jim Schwanda
Author URI:  https://www.usi2solve.com/leader
Text Domain: usi-variable-solutions
*/

// cannot add category;
// cannot update capabilites, for user they don't stick;

/*
The Variable-Solutions plugin adds global variables to the WordPress content management system.
Copyright (C) 2017 Jim Schwanda

Variable-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Variable-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Variable-Solutions.  If not, see 
<http://www.gnu.org/licenses/>.
*/

class USI_Variable_Solutions {

   const VERSION = '1.1.0 (2019-05-14)';
   const NAME = 'Variable-Solutions';
   const PREFIX = 'usi-variable';
   const TEXTDOMAIN = 'usi-variable-solutions';

   public static $capabilities = array(
      'View-Variables' => 'View variables',
      'Change-Values' => 'Change values',
      'Add-Variables' => 'Add variables',
      'Edit-Variables' => 'Edit variables',
      'Delete-Variables' => 'Delete variables',
      'Publish-Variables' => 'Publish variables',
      'View-Settings' => 'View settings',
      'Edit-Preferences' => 'Edit preferences',
   );

   public static $options = array();

   function __construct() {
      if (empty(USI_Variable_Solutions::$options)) {
         global $wpdb;
         $defaults['preferences']['file-location'] = 'plugin';
         $defaults['preferences']['menu-icon'] = 'dashicons-controls-repeat';
         $defaults['preferences']['menu-position'] = 'null';
         $defaults['preferences']['shortcode-function'] = 'usi_variable_shortcode';
         $defaults['preferences']['shortcode-prefix'] = 'variable';
         $defaults['preferences']['variable-prefix'] = $wpdb->prefix;
         USI_Variable_Solutions::$options = get_option(self::PREFIX . '-options', $defaults);
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
   } // __construct();

   static function get_variables_folder() {
      if (!empty(USI_Variable_Solutions::$options['preferences']['file-location'])) {
         return(USI_Variable_Solutions::$options['preferences']['file-location']);
      }
      return('plugin');
   } // get_variables_folder();

   static function action_admin_notices() {
      global $pagenow;
      if ('plugins.php' == $pagenow) {
        $text = sprintf(
           __('The %s plugin is required for the %s plugin to run properly.', self::TEXTDOMAIN), 
           '<b>Settings-Solutions</b>',
           '<b>Variable-Solutions</b>'
        );
        echo '<div class="notice notice-warning is-dismissible"><p>' . $text . '</p></div>';
      }
   } // action_admin_notices();

} // Class USI_Variable_Solutions;
   
new USI_Variable_Solutions();

if (is_admin() && !defined('WP_UNINSTALL_PLUGIN')) {
   require_once('usi-variable-solutions-admin.php');
   require_once('usi-variable-solutions-install.php');
   if (is_dir(plugin_dir_path(__DIR__) . 'usi-settings-solutions')) {
      require_once('usi-variable-solutions-settings.php'); 
   } else {
      add_action('admin_notices', array('USI_Variable_Solutions', 'action_admin_notices'));
   }
   require_once('usi-variable-solutions-table.php');
   require_once('usi-variable-solutions-variable.php');
}

// --------------------------------------------------------------------------------------------------------------------------- // ?>
