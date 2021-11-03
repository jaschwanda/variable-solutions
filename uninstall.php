<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/*
Variable-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
Variable-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with Variable-Solutions. If not, see 
https://github.com/jaschwanda/variable-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-capabilities.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-uninstall.php');

require_once('usi-variable-solutions.php');

final class USI_Variable_Solutions_Uninstall {

   const VERSION = '2.4.2 (2021-11-03)';

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
