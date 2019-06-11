<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

final class USI_Variable_Solutions_Admin {

   const VERSION = '1.1.1 (2019-06-12)';

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
