<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

final class USI_Variable_Solutions_Variable {

   const VERSION = '2.3.1 (2020-01-01)';

   private $disable_save = false;
   private $error = false;
   private $option_name = 'usi-vs-variable-dummy';
   private $page_active = false;
   private $page_slug = 'usi-vs-variable';
   private $permission = 'Edit-Variables';
   private $readonly = false;
   private $section_id = 'usi-vs-variable';

   function __construct() {

      $action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : null;
      $this->page_active = !empty($_GET['page']) && ($this->page_slug == $_GET['page']) ? true : false;

      if ($action || $this->page_active) {
         add_action('admin_head', array($this, 'action_admin_head'));
         add_action('admin_init', array($this, 'action_admin_init'));
         add_action('admin_menu', array($this, 'action_admin_menu'));
      }

      add_filter('option_page_capability_' . $this->section_id, array($this, 'filter_option_page_capability'), 10, 2);

   } // __construct();

   function action_admin_head() {
      echo 
         '<style>' . PHP_EOL .
         '.form-table td{padding-bottom:12px; padding-top:2px;} /* 25px; */' . PHP_EOL .
         '.form-table th{padding-bottom:7px; padding-top:7px;} /* 20px; */' . PHP_EOL .
         '</style>' . PHP_EOL;
   } // action_admin_head();

   function action_admin_init() {

      add_settings_section(
         $this->section_id, // Section id;
         null, // Section title;
         null, // Render section callback;
         $this->page_slug // Settings page menu slug;
      );

      $display_fields = true;

      $fields = array();

      if (!empty($this->options['variable_id'])) {

         global $wpdb;
         $SAFE_variable_table = $wpdb->prefix . 'USI_variables';
         $row = $wpdb->get_row(
            $wpdb->prepare(
               "SELECT * FROM `$SAFE_variable_table` WHERE (`variable_id` = %d) LIMIT 0,1", $this->options['variable_id']
            ), OBJECT
         );

         if ($row) {

            $this->options['category'] = $row->category;
            $this->options['notes'] = $row->notes;
            $this->options['order'] = $row->order;
            $this->options['type'] = $row->type;
            $this->options['value'] = $row->value;
            $this->options['variable'] = $row->variable;

         } else {

            $display_fields = false;

            $this->disable_save = true;

            add_settings_error(
               $this->section_id, // Section id slug;
               'error', // Message slug name identifier;
               __('Cannot find variable with given Id', USI_Variable_Solutions::TEXTDOMAIN) . '.' // Message text shown to user;
            );

         }

         $fields[] = array(
            'name' => $this->option_name . '[' . ($id = 'variable_id') . ']',
            'title' => 'Id',
            'class' => 'regular-text', 
            'readonly' => true,
            'type' => 'text', 
            'notes' => 'The variable Id cannot be changed.',
            'value' => $this->options[$id],
         );

         $fields[] = array(
            'name' => $this->option_name . '[' . ($id = 'usage-shortcode') . ']',
            'title' => 'Usage',
            'class' => 'large-text', 
            'readonly' => true,
            'type' => 'text', 
            'notes' => 'Copy the above shortcode and paste into your posts/pages.' .
               ('date' == $this->options['category'] ? 
               ' For date-format-string see <a href="http://php.net/manual/en/function.date.php" target="_blank">php.net/manual/en/function.date.php</a>.' : '') ,
            'value' => '[' . USI_Variable_Solutions::$options['preferences']['shortcode-prefix'] .
               ('general' == $this->options['category'] ? '' : ' category="' . $this->options['category'] . '"') . 
               ' item="' .strtolower( $this->options['variable']) . '"' .
               ('date' == $this->options['category'] ? ' format="date-format-string"' : '') . 
               ']',
         );

      } else if (!$this->error) {

         $this->options['category'] = 
         $this->options['notes'] = 
         $this->options['value'] = 
         $this->options['variable'] = '';
         $this->options['order'] = '9999';
         $this->options['type'] = 'V';
         $this->options['variable_id'] - 0;

      }

      if ($display_fields) {
         
         if ('V' == $this->options['type']) {
            $label = 'Value';
            $notes = 'Enter value to be substituted for variable reference.';
         } else {
            $label = 'Expression';
            $notes = 'Expressions cannot be modified by mortals.';
            $this->disable_save = $this->readonly = true;
         }

         $fields[] = array(
            'name' => $this->option_name . '[' . ($id = 'category') . ']',
            'title' => 'Category',
            'class' => 'regular-text', 
            'maxlength' => 24,
            'type' => 'text', 
            'notes' => 'Enter lower case text, no spaces or punctuation except the underscore, 24 characters maximum.',
            'readonly' => $this->readonly,
            'value' => $this->options[$id],
         );

         $fields[] = array(
            'name' => $this->option_name . '[' . ($id = 'variable') . ']',
            'title' => 'Variable',
            'class' => 'regular-text', 
            'maxlength' => 64,
            'type' => 'text', 
            'notes' => 'Enter lower case text, no spaces or punctuation except the underscore, 64 characters maximum.',
            'readonly' => $this->readonly,
            'value' => $this->options[$id],
         );

         $fields[] = array(
            'name' => $this->option_name . '[' . ($id = 'value') . ']',
            'title' => $label,
            'class' => 'regular-text', 
            'type' => 'textarea', 
            'notes' => $notes ,
            'readonly' => $this->disable_save,
            'value' => $this->options[$id],
         );

         $fields[] = array(
            'name' => $this->option_name . '[' . ($id = 'notes') . ']',
            'title' => 'Description / Notes',
            'class' => 'regular-text', 
            'type' => 'textarea', 
            'notes' => 'Enter a brief description.',
            'readonly' => $this->readonly,
            'value' => $this->options[$id],
         );

         $fields[] = array(
            'name' => $this->option_name . '[' . ($id = 'order') . ']',
            'title' => 'Order',
            'class' => 'small-text', 
            'max' => 9999, 
            'type' => 'number', 
            'notes' => 'Enter integer between 0 and 9999 inclusive.',
            'readonly' => $this->readonly,
            'value' => $this->options[$id],
         );

         $fields[] = array(
            'name' => $this->option_name . '[' . ($id = 'type') . ']',
            'title' => null,
            'type' => 'hidden', 
            'value' => $this->options[$id],
         );

         foreach ($fields as $field) {

            if (!empty($field['notes'])) $field['notes'] = 
               '<p class="description">' . __($field['notes'], USI_Variable_Solutions::TEXTDOMAIN) . '</p>';

            add_settings_field(
               $field['name'], // Option name;
               __($field['title'], USI_Variable_Solutions::TEXTDOMAIN), // Field title;
               array('USI_WordPress_Solutions_Settings', 'fields_render_static'), // Render field callback;
               $this->page_slug, // Settings page menu slug;
               $this->section_id, // Section id;
               $field
            );

         }

      } // ENDIF $display_fields;

      register_setting(
         $this->section_id, // Settings group name, must match the group name in settings_fields();
         $this->option_name, // Option name;
         array($this, 'fields_sanitize') // Sanitize field callback;
      );

   } // action_admin_init();

   function action_admin_menu() {

      $updated = !empty($_REQUEST['settings-updated']);

      $variable_id = !empty($_GET['variable_id']) ? (int)$_GET['variable_id'] : 0;

      if ($updated) {

         if ($results = get_settings_errors()) {
            foreach ($results as $result) {
               if ('error' == $result['type']) {
                  $this->error = true;
                  break;
               }
            }
         }

         $this->options = get_option($this->option_name);

      } else {

         $this->options['variable_id'] = $variable_id;

      }

      if ($variable_id || (!$this->error && $updated)) {
         if (USI_Variable_Solutions_Admin::$variables_edit) {
            $this->button = __('Save Variable', USI_Variable_Solutions::TEXTDOMAIN);
            $this->header = __('Edit Variable', USI_Variable_Solutions::TEXTDOMAIN);
            $this->permission = 'Edit-Variables';
         } else if (USI_Variable_Solutions_Admin::$variables_change) {
            $this->button = __('Save Value', USI_Variable_Solutions::TEXTDOMAIN);
            $this->header = __('Change Value', USI_Variable_Solutions::TEXTDOMAIN);
            $this->permission = 'Change-Values';
            $this->readonly = true;
         }
      } else if (USI_Variable_Solutions_Admin::$variables_add) {
         $this->button =
         $this->header = __('Add Variable', USI_Variable_Solutions::TEXTDOMAIN);
         $this->permission = 'Add-Variables';
      } else {
         if (!empty($_POST['usi-vs-variable-permission'])) $this->permission = $_POST['usi-vs-variable-permission'];
         $this->button =
         $this->header = null;
      }

      // We don't want the page to show up in the sidebar menu, just be accessable from the list;
      // We need it in the menu or the settings API won't allow option changes, so we remove it from the menu
      // if this page isn't active, and if it is active we remove the menu item with jQuery down below;

      add_options_page(
         __('Variable-Solutions', USI_Variable_Solutions::TEXTDOMAIN) . ' | ' . $this->header, // Page <title/> text;
         '<span id="usi-vs-variable-remove"></span>', // Sidebar menu text; 
         USI_Variable_Solutions::NAME . '-' . $this->permission, // Capability required to enable page;
         $this->page_slug, // Settings page menu slug;
         array($this, 'render_page') // Render page callback;
      );

      if (!$this->page_active) {
         remove_submenu_page('options-general.php', $this->page_slug);
      }

   } // action_admin_menu();

   function fields_sanitize($input) {

      global $wpdb;

      $SAFE_variable_table = $wpdb->prefix . 'USI_variables';

      $category = !empty($input['category']) ? $this->safe_name($input['category']) : null; 
      $variable = !empty($input['variable']) ? $this->safe_name($input['variable']) : null; 

      $order = !empty($input['order']) ? (int)$input['order'] : 0; 

      $warning = false;

      if (!$category && !$variable) {

         $text = 'Category and variable are missing';
         $type = 'error';

      } else if (!$category) {

         $text = 'Category is missing';
         $type = 'error';

      } else if (!$variable) {

         $text = 'Variable is missing';
         $type = 'error';

      } else if ((0 > $order) || (9999 < $order)) {

         $text = 'Order must be between 0 and 9999 inclusive';
         $type = 'error';

      } else if (!empty($input['variable_id']) && (null != $wpdb->get_row($wpdb->prepare(
            "SELECT `variable_id` FROM `$SAFE_variable_table`" .
            ' WHERE (`category` = %s) AND (`variable` = %s) AND (`variable_id` <> %d)', 
            $category, $variable, $input['variable_id']), OBJECT))) {

         $text = 'Category and variable pair already in use';
         $type = 'error';

      } else if (!empty($input['variable_id'])) {

         $permission = !empty($_POST['usi-vs-variable-permission']) ? $_POST['usi-vs-variable-permission'] : null;

         if ('Edit-Variables' == $permission) {

            $wpdb->query(
               $wpdb->prepare(
                  "UPDATE `$SAFE_variable_table` SET" .
                  ' `category` = %s' .
                  ', `notes` = %s' .
                  ', `order` = %d' .
                  ', `type` = %s' .
                  ', `updated` = %s' .
                  ', `value` = %s' .
                  ', `variable` = %s' .
                  ' WHERE (`variable_id` = %d)',

                  $category,
                  $input['notes'],
                  $order,
                  $input['type'],
                  current_time('mysql'), 
                  $input['value'],
                  $variable,
                  $input['variable_id']
               )
            );

            $text = 'Variable saved';
            $warning = true;

         } else if ('Change-Values' == $permission) {

            $wpdb->query(
               $wpdb->prepare(
                  "UPDATE `$SAFE_variable_table` SET" .
                  ' `updated` = %s' .
                  ', `value` = %s' .
                  ' WHERE (`variable_id` = %d)',

                  current_time('mysql'), 
                  $input['value'],
                  $input['variable_id']
               )
            );

            $text = 'Value saved';

         }

         $type = 'updated';

      } else {

         $wpdb->insert($SAFE_variable_table, 
            array(
               'category' => $category, 
               'notes' => $input['notes'], 
               'order' => $order, 
               'type' => $input['type'], 
               'updated' => current_time('mysql'), 
               'user_id' => get_current_user_id(), 
               'value' => $input['value'], 
               'variable' => $variable, 
            )
         );

         $text = 'Variable added';
         $type = 'updated';
         $warning = true;

         $input['variable_id'] = $wpdb->insert_id;

      }

      add_settings_error(
         $this->section_id, // Section id slug;
         $type, // Message slug name identifier;
         __($text, USI_Variable_Solutions::TEXTDOMAIN) . '.', // Message text shown to user;
         $type // Message type, [updated|error];
      );

      if ($warning) {
         ob_start();
         submit_button(__('Publish', USI_Variable_Solutions::TEXTDOMAIN), 'secondary', 'usi-vs-publish', false);
         add_settings_error(
            $this->section_id, // Section id slug;
            'warning', // Message slug name identifier;
            sprintf(__('Remember to %s your changes', USI_Variable_Solutions::TEXTDOMAIN), ob_get_clean()) . '.', // Message text shown to user;
            'notice-warning' // Message type;
         );
      }

      $input['category'] = $category;
      $input['order']    = $order;
      $input['variable'] = $variable;

      return($input);

   } // fields_sanitize();

   function filter_option_page_capability() {
      return(USI_Variable_Solutions::NAME . '-' . $this->permission);
   } // filter_option_page_capability();

   function render_page() {
?>
<!-- usi-variable-solutions-variable:render_page:begin ------------------------------------------------------------------------- -->
<div class="wrap">
  <h1><?php _e($this->header, USI_Variable_Solutions::TEXTDOMAIN); ?></h1>
  <form method="post" action="options.php">
    <input name="usi-vs-variable-permission" type="hidden" value="<?php echo $this->permission; ?>" />
    <?php settings_fields($this->section_id); do_settings_sections($this->page_slug); ?>
    <div class="submit">
      <?php 
         submit_button(__($this->button, USI_Variable_Solutions::TEXTDOMAIN), 'primary', 'submit', false, $this->disable_save ? 'disabled' : null); 
         echo ' &nbsp; ';
         submit_button(__('Back To List', USI_Variable_Solutions::TEXTDOMAIN), 'secondary', 'usi-vs-variables', false); 
         echo ' &nbsp; '; 
         if (USI_Variable_Solutions_Admin::$variables_add && ('Add-Variables' != $this->permission)) 
            submit_button(__('Add Variable', USI_Variable_Solutions::TEXTDOMAIN), 'secondary', $this->page_slug . '-add', false); 
         echo ' &nbsp; '; 
         if (USI_Variable_Solutions_Admin::$variables_publish && ('Add-Variables' != $this->permission)) 
            submit_button(__('Publish', USI_Variable_Solutions::TEXTDOMAIN), 'secondary', 'usi-vs-publish', false); 
      ?>
    </div>
  </form>
</div>
<script>
jQuery(document).ready(function($) {
   $('#usi-vs-publish').click(function() {
      window.location.href = 'options-general.php?page=usi-variable-settings&tab=publish';
      return(false);
   });

   $('#usi-vs-variable-remove').parent().remove();

   $('#usi-vs-variables').click(function() {
      window.location.href = 'admin.php?page=usi-vs-variables';
      return(false);
   });

   $('#usi-vs-variable-add').click(function() {
      window.location.href = 'options-general.php?page=usi-vs-variable';
      return(false);
   });

});
</script>
<!-- usi-variable-solutions-variable:render_page:end --------------------------------------------------------------------------- -->
<?php
   } // render_page();

   function safe_name($name) {
      return(str_replace('-', '_', sanitize_title(trim($name))));
   } // safe_name();

} // Class USI_Variable_Solutions_Variable;

new USI_Variable_Solutions_Variable();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
