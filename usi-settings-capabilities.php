<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

if (!class_exists('USI_Settings_Capabilities')) { class USI_Settings_Capabilities {

   const VERSION = '1.0.0 (2017-10-29)';

   private $capabilities = null;
   private $disable_save = true;
   private $name = null;
   private $prefix = null;
   private $role = null;
   private $role_id = null;
   private $prefix_select_user = null;
   private $text_domain = null;
   private $user = null;
   private $user_id = null;

   private function __construct($name, $prefix, $text_domain, $capabilities) {
      $this->capabilities = $capabilities;
      $this->name = $name;
      $this->prefix = $prefix;
      $this->text_domain = $text_domain;
      $this->prefix_select_user = $this->prefix . '-select-user';
   } // __construct();

   function after_add_settings_section($settings) {

      $prefix = $this->prefix;

      $current_user_id = get_current_user_id();

      // Get current role, default to administrator if none given;
      $role_id_option_name = $prefix . '-options-role-id';
      $option_role_id = get_user_option($role_id_option_name, $current_user_id);
      if (empty($option_role_id)) $option_role_id = 'administrator';
      $this->role_id = (!empty($_GET['role_id']) ? $_GET['role_id'] : $option_role_id);
      $this->role = get_role($this->role_id);

      // Get selected user, default to current user if none selected;
      $option_user_id = (int)get_user_option($prefix . '-options-user-id', $current_user_id);
      if (0 == $option_user_id) $option_user_id = $current_user_id;
      $this->user_id = (int)(!empty($_GET['user_id']) && (0 < $_GET['user_id']) ? $_GET['user_id'] : $option_user_id);

      if ($this->role_id != $option_role_id) update_user_option($current_user_id, $role_id_option_name, $this->role_id);
      if ($this->user_id != $option_user_id) update_user_option($current_user_id, $prefix . '-options-user-id', $this->user_id);

      if ($this->prefix_select_user == $this->role_id) {

         if ($this->user = new WP_User($this->user_id)) {
            $user = $this->user;
            if (!empty($user->roles) && is_array($user->roles)) {
               foreach ($user->roles as $role_id) {
                  $role = get_role($role_id);
                  foreach ($settings as $field_id => & $attributes) {
                     $capability_name = $this->name . '-' . $field_id;
                     if (USI_Settings::$options[$prefix]['capabilities'][$field_id] = $role->has_cap($capability_name)) {
                        $attributes['readonly'] = true;
                        $attributes['notes'] = ' <i>(' . sprintf(__("Set by user's %s role settings", 
                           $this->text_domain), ucfirst($role_id)) . ')</i>';
                     } else {
                        $this->disable_save = false;
                     }
                     if ($user->has_cap($capability_name)) {
                        USI_Settings::$options[$prefix]['capabilities'][$field_id] = true;
                     }
                  }
                  unset($attributes);
               }
            }
         }

      } else if ('administrator' == $this->role_id) {

         foreach ($settings as $field_id => & $attributes) {
            USI_Settings::$options[$prefix]['capabilities'][$field_id] = true;
            $attributes['readonly'] = true;
            $attributes['notes'] = ' <i>(Default setting for Administrator)</i>';
         }
         unset($attributes);

      } else if ($role = $this->role) {

         $this->disable_save = false;

         foreach ($settings as $field_id => $attributes) {
            $capability_name = $this->name . '-' . $field_id;
            USI_Settings::$options[$prefix]['capabilities'][$field_id] = $role->has_cap($capability_name);
         }

      }

      return($settings);

   } // after_add_settings_section();

   function fields_sanitize($input) {

      $prefix_role_id = $this->prefix . '-role_id';

      if (!empty($_POST[$prefix_role_id])) {

         if ($this->prefix_select_user != $_POST[$prefix_role_id]) {

            $role = $this->role;

            if ('administrator' == $_POST[$prefix_role_id]) {
               foreach ($this->capabilities as $name => $capability) {
                  $capability_name = $this->name . '-' . $name;
                  $role->add_cap($capability_name);
               }
            } else {
               foreach ($this->capabilities as $name => $capability) {
                  $capability_name = $this->name . '-' . $name;
                  !empty($input['capabilities'][$name]) ? $role->add_cap($capability_name) : $role->remove_cap($capability_name);
               }
            }

         } else if (0 < $_POST[$this->prefix . '-user_id']) {

            $user = $this->user;
            foreach ($this->capabilities as $name => $capability) {
               $capability_name = $this->name . '-' . $name;
               !empty($input['capabilities'][$name]) ? $user->add_cap($capability_name) : $user->remove_cap($capability_name);
            }

         }

      }

      return($input);
   } // fields_sanitize();

   function render_section() {
      echo 
         '    <p>' . sprintf(__('The %s plugin enables you to set the role capabilites system wide or for a specific user on a user-by-user basis. Select the role or specific user you would like to edit and then check or uncheck the desired capabilites for that role or user.', $this->text_domain), $this->name) . '</p>' . PHP_EOL .
         '    <label>' . __('Capabilities for', $this->text_domain) . ' : </label>' . PHP_EOL .
         '    <input type="hidden" name="' . $this->prefix . '-role_id" value="' . $this->role_id . '" />' . PHP_EOL .
         '    <input type="hidden" name="' . $this->prefix . '-user_id" value="' . $this->user_id . '" />' . PHP_EOL .
         '    <select id="' . $this->prefix . '-role-select">' . PHP_EOL . 
         '      <option value="' . $this->prefix . '-select-user">' . __('Select User', $this->text_domain) . '</option>';
      wp_dropdown_roles($this->role_id);
      echo PHP_EOL . 
         '    </select>' . PHP_EOL;
         if ($this->prefix_select_user == $this->role_id) {
            wp_dropdown_users(array('id' => $this->prefix . '-user-select', 'selected' => $this->user_id));
            if (!empty($this->user->roles) && is_array($this->user->roles)) {
               $comma = ' (';
               foreach ($this->user->roles as $role) {
                  echo $comma . ucfirst($role);
                  $comma = ', ';
               }
               echo ')';
            }
         }
      echo PHP_EOL . 
         '<script>' . PHP_EOL .
         'jQuery(document).ready(function($) {' . PHP_EOL .
         "   var url = 'options-general.php?page=" . USI_Settings_Admin::page_slug($this->prefix) . "&tab=capabilities&role_id='" . PHP_EOL .
         "   $('#{$this->prefix}-role-select').change(function(){window.location.href = url + $(this).val() + '&user_id={$this->user_id}';});" . PHP_EOL .
         "   $('#{$this->prefix}-user-select').change(function(){window.location.href = url + '{$this->role_id}' + '&user_id=' + $(this).val();});" . PHP_EOL .
         '});' . PHP_EOL .
         '</script>' . PHP_EOL;
   } // render_section();

   function section_footer() {
      submit_button(
         __('Save Capabilities', $this->text_domain),
        'primary', 
        'submit', 
        true, 
        $this->disable_save ? 'disabled' : null
      ); 
      return(null);
   } // section_footer();

   public static function section($name, $prefix, $text_domain, $capabilities) {

      $that = new USI_Settings_Capabilities($name, $prefix, $text_domain, $capabilities);

      $section = array(
         'after_add_settings_section' => array($that, 'after_add_settings_section'),
         'fields_sanitize' => array($that, 'fields_sanitize'),
         'footer_callback' => array($that, 'section_footer'),
         'header_callback' => array($that, 'render_section'),
         'label' => __('Capabilities', $that->text_domain),
         'settings' => array(),
      );

      foreach ($that->capabilities as $name => $label) {
         $section['settings'][$name] = array(
            'readonly' => false, 
            'label' => $label, 
            'notes' => null, 
            'type' => 'checkbox'
         );
      }

      return($section);

   } // section();

} /* Class USI_Settings_Capabilities; */ }

// --------------------------------------------------------------------------------------------------------------------------- // ?>