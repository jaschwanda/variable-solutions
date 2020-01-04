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

final class USI_Variable_Solutions_Admin {

   const VERSION = '2.0.0 (2020-01-04)';

   public static $variables_add     = false;
   public static $variables_change  = false;
   public static $variables_delete  = false;
   public static $variables_edit    = false;
   public static $variables_publish = false;

   function __construct() {
      add_action('admin_menu', array($this, 'action_admin_menu'));
   } // __construct();

   function action_admin_menu() {
      self::$variables_add     = current_user_can(USI_Variable_Solutions::NAME . '-Add-Variables');
      self::$variables_change  = current_user_can(USI_Variable_Solutions::NAME . '-Change-Values');
      self::$variables_delete  = current_user_can(USI_Variable_Solutions::NAME . '-Delete-Variables');
      self::$variables_edit    = current_user_can(USI_Variable_Solutions::NAME . '-Edit-Variables');
      self::$variables_publish = current_user_can(USI_Variable_Solutions::NAME . '-Publish-Variables');
   } // action_admin_menu();

} // Class USI_Variable_Solutions_Admin;

new USI_Variable_Solutions_Admin;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
