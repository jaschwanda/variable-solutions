<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

if (!class_exists('USI_Settings_Uninstall')) { final class USI_Settings_Uninstall {

   const VERSION = '1.0.0 (2017-10-29)';

   private function __construct() {
   } // __construct();

   static function uninstall($name, $prefix, $capabilities) {

      global $wp_roles;
      global $wpdb;

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

      $users = get_users(array('exclude' => 0, 'fields' => array('ID')));

      foreach ($capabilities as $capability) {

         $capability_name = $name . '-' . $capability['name'];

         foreach (array_keys($wp_roles->roles) as $role_name) {
            $role = get_role($role_name);
            if ($role) $role->remove_cap($capability_name);
         }

         foreach ($users as $user) {
            $user_object = new WP_User($user->ID);
            if ($user_object) $user_object->remove_cap($capability_name);
         }

      }

      delete_metadata('user', null, $wpdb->prefix . $prefix . '-options-role-id', null, true);
      delete_metadata('user', null, $wpdb->prefix . $prefix . '-options-user-id', null, true);

      delete_option($prefix . '-options');

   } // uninstall();

} } // Class USI_Settings_Uninstall;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
