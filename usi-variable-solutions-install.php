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

final class USI_Variable_Solutions_Install {

   const VERSION = '2.1.0 (2020-02-21)';

   const VERSION_DATA = '1.0';

   private function __construct() {
   } // __construct();

   static function init() {
      $file = str_replace('-install', '', __FILE__);
      register_activation_hook($file, array(__CLASS__, 'hook_activation'));
      register_deactivation_hook($file, array(__CLASS__, 'hook_deactivation'));
   } // init();

   static function hook_activation() {

      global $wpdb;

      if (!current_user_can('activate_plugins')) return;

      check_admin_referer('activate-plugin_' . (isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : ''));

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      $updated = current_time('mysql');
      $user_id = get_current_user_id();

      $SAFE_history_table = $wpdb->prefix . 'USI_history';

      // The new-lines and double space after PRIMARY KEY are required;
      $sql = "CREATE TABLE `$SAFE_history_table` " .
         '(`history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,' . PHP_EOL .
         "`time_stamp` timestamp," . PHP_EOL .
         "`user_id` bigint(20) unsigned DEFAULT '0'," . PHP_EOL .
         '`action` text DEFAULT NULL,' . PHP_EOL .
         'PRIMARY KEY  (`history_id`))';

      $result = dbDelta($sql);

      $SAFE_variable_table = $wpdb->prefix . 'USI_variables';

      // The new-lines and double space after PRIMARY KEY are required;
      $sql = "CREATE TABLE `$SAFE_variable_table` " .
         '(`variable_id` int(10) unsigned NOT NULL AUTO_INCREMENT,' . PHP_EOL .
         "`updated` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL," . PHP_EOL .
         "`user_id` bigint(20) unsigned DEFAULT '0'," . PHP_EOL .
         "`type` char(1) DEFAULT 'V'," . PHP_EOL .
         "`order` smallint(4) unsigned DEFAULT '9999'," . PHP_EOL .
         '`category` varchar(24) DEFAULT NULL,' . PHP_EOL .
         '`variable` varchar(64) DEFAULT NULL,' . PHP_EOL .
         '`value` text DEFAULT NULL,' . PHP_EOL .
         '`notes` text DEFAULT NULL,' . PHP_EOL .
         'PRIMARY KEY  (`variable_id`),' . PHP_EOL .
         'UNIQUE KEY `VARIABLE` (`category`,`variable`))';

      $result = dbDelta($sql);

      $count_of_records = $wpdb->get_var("SELECT COUNT(*) FROM $SAFE_variable_table WHERE (`variable_id` = 1)");

      if (1 != $count_of_records) {
         $wpdb->insert($SAFE_variable_table, 
            array(
               'updated' => $updated, 
               'user_id' => $user_id, 
               'type' => '-', 
               'notes' => serialize(array('version_data' => self::VERSION_DATA)),
            )
         );

         $wpdb->insert($SAFE_variable_table, 
            array(
               'updated' => $updated, 
               'user_id' => $user_id, 
               'type' => 'E', 
               'category' => 'general', 
               'variable' => 'current_year', 
               'value' => "date('Y')", 
               'notes' => 'Current year', 
            )
         );

         $wpdb->insert($SAFE_variable_table, 
            array(
               'updated' => $updated, 
               'user_id' => $user_id, 
               'type' => 'E', 
               'category' => 'general', 
               'variable' => 'tld',
               'value' => "(((PHP_OS == 'WINNT') || (PHP_OS == 'Darwin')) ? 'local' : 'org')", 
               'notes' => 'Top level domain', 
            )
         );
      }

      $role = get_role('administrator');
      foreach (USI_Variable_Solutions::$capabilities as $capability => $description) {
         $role->add_cap(USI_Variable_Solutions::NAME . '-' . $capability);
      }

   } // hook_activation();

   static function hook_deactivation() {

      if (!current_user_can('activate_plugins')) return;

      check_admin_referer('deactivate-plugin_' . (isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : ''));

   } // hook_deactivation();

} // Class USI_Variable_Solutions_Install;

USI_Variable_Solutions_Install::init();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
