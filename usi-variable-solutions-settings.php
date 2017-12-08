<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once('usi-settings-admin.php');
require_once('usi-settings-capabilities.php');

class USI_Variable_Solutions_Settings extends USI_Settings_Admin {

   const VERSION = '1.0.4 (2017-12-07)';

   protected $is_tabbed = true;

   function __construct() {

      $this->sections = array(
         'preferences' => array(
            'header_callback' => array($this, 'config_section_header_preferences'),
            'label' => 'Preferences',
            'settings' => array(
               'variable-prefix' => array(
                  'type' => 'text', 
                  'label' => 'Variable prefix',
                  'notes' => 'Enter lower case text, no spaces or punctuation except the underscore. This is the string that prefixes <b>variable</b> in the <b>define(variable, "value")</b> statements in the variables.php file and is used to ensure that your variable names are unique. Defaults to the WordPress database prefix.',
               ),
               'shortcode-prefix' => array(
                  'type' => 'text', 
                  'label' => 'Shortcode identifier',
                  'notes' => 'Enter lower case text, no spaces or punctuation. This is the <b>ID</b> in [<b>ID</b> attribute="value"] used to access the variable shortcodes in you content. Defaults to <b>variable</b>.',
               ),
               'shortcode-function' => array(
                  'class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Shortcode function name',
                  'notes' => 'Enter lower case text, no spaces or punctuation except the underscore. This is the name of the PHP function that executes the variable shortcodes. Defaults to <b>usi_variable_shortcode</b>.',
               ),
               'menu-icon' => array(
                  'class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Variable list page menu icon',
                  'notes' => 'Enter the dashicons text string, see <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">developer.wordpress.org/resource/dashicons</a> for choices. Defaults to <b>dashicons-controls-repeat</b>.',
               ),
               'menu-position' => array(
                  'type' => 'text', 
                  'label' => 'Variable list page menu position',
                  'notes' => 'Enter a numeric value, blank or null appends the menu item to the bottom of the menu. Defaults to <b>null</b>.',
               ),
               'file-location' => array(
                  'type' => 'radio', 
                  'label' => 'Location of variables.php file',
                  'choices' => array(
                     array(
                        'value' => 'plugin', 
                        'label' => true, 
                        'notes' => __('Plugin folder', USI_Variable_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ),
                     array(
                        'value' => 'theme', 
                        'label' => true, 
                        'notes' => __('Theme folder', USI_Variable_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ),
                     array(
                        'value' => 'root', 
                        'label' => true, 
                        'notes' => __('WordPress wp-config.php folder', USI_Variable_Solutions::TEXTDOMAIN), 
                     ),
                  ),
                  'notes' => 'Defaults to <b>Plugin folder</b>.',
               ), // file-location;
            ),
         ), // preferences;

         'capabilities' => USI_Settings_Capabilities::section(
            USI_Variable_Solutions::NAME, 
            USI_Variable_Solutions::PREFIX, 
            USI_Variable_Solutions::TEXTDOMAIN,
            USI_Variable_Solutions::$capabilities
         ), // capabilities;

         'publish' => array(
         // 'footer_callback' => array($this, 'config_section_footer'), // Only to test no tabbing;
            'header_callback' => array($this, 'config_section_header_publish'),
            'label' => 'Publish',
            'settings' => array(
               'explaination' => array(
                  'class' => 'regular-text', 
                  'type' => 'textarea', 
                  'label' => 'Explaination',
                  'notes' => __('Enter up to 255 printable characters.', USI_Variable_Solutions::TEXTDOMAIN), 
               ),
            ),
            'submit' => __('Publish Variables', USI_Variable_Solutions::TEXTDOMAIN),
         ), // publish;
      );

      foreach ($this->sections as $name => & $section) {
         foreach ($section['settings'] as $name => & $setting) {
            if (!empty($setting['notes']))
               $setting['notes'] = '<p class="description">' . __($setting['notes'], USI_Variable_Solutions::TEXTDOMAIN) . '</p>';
         }
      }
      unset($setting);

      parent::__construct(
         USI_Variable_Solutions::NAME, 
         USI_Variable_Solutions::PREFIX, 
         USI_Variable_Solutions::TEXTDOMAIN
      );

      add_filter('plugin_row_meta', array($this, 'filter_plugin_row_meta'), 10, 2);

   } // __construct();

   /* This function is here only to test the no tabbing settings option;
   function config_section_footer() {
      submit_button(__('Single Save', USI_Variable_Solutions::TEXTDOMAIN), 'primary', 'submit', true); 
      return(null);
   } // config_section_footer();
   */

   function config_section_header_preferences() {
      echo '<p>' . __('Changing these settings after the system is in use may cause referencing errors. Make sure that you also change the <b>[ID attribute="value"]</b> shortcodes in your content and the <b>defined(variable, "value")</b> statments in your PHP files to match the settings you enter here.', USI_Variable_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL;
   } // config_section_header_preferences();

   function config_section_header_publish() {
      if ('root' == ($folder = USI_Variable_Solutions::get_variables_folder())) $folder = 'WordPress wp-config.php';
      echo '<p>' . sprintf(__('Enter an explaination for publishing the variables and click on the <b>Publish Variables</b> button. The variables.php file will be published in the %s folder.', USI_Variable_Solutions::TEXTDOMAIN), $folder) . '</p>' . PHP_EOL;
   } // config_section_header_publish();

   function fields_sanitize($input) {
      if (!empty($input['preferences']['menu-icon'])) {
         $input['preferences']['menu-icon'] = sanitize_title(strtolower($input['preferences']['menu-icon']));
      } else {
         $input['preferences']['menu-icon'] = 'dashicons-controls-repeat';
      }
      if (!empty($input['preferences']['menu-position'])) {
         $input['preferences']['menu-position'] = (int)$input['preferences']['menu-position'];
         if (0 == $input['preferences']['menu-position']) $input['preferences']['menu-position'] = 'null';
      } else {
         $input['preferences']['menu-position'] = 'null';
      }
      if (!empty($input['preferences']['shortcode-function'])) {
         $input['preferences']['shortcode-function'] = sanitize_title(strtolower($input['preferences']['shortcode-function']));
      }
      if (!empty($input['preferences']['shortcode-prefix'])) {
         $input['preferences']['shortcode-prefix'] = sanitize_title(strtolower($input['preferences']['shortcode-prefix']));
      }
      if (!empty($input['preferences']['variable-prefix'])) {
         $input['preferences']['variable-prefix'] = sanitize_title(strtolower($input['preferences']['variable-prefix']));
      }
      $input = parent::fields_sanitize($input);
      if ('publish' == $_REQUEST['usi-variable-tab']) {
         usi_history('usi-variable-solutions:publish:explaination=' . $input['publish']['explaination']);
         $input['publish']['explaination'] = '';
         $prefix = USI_Settings::$options[USI_Variable_Solutions::PREFIX]['preferences']['variable-prefix'];
         global $wpdb;
         switch ($location = USI_Variable_Solutions::get_variables_folder()) {
         default: case 'plugin': 
            $fh = fopen(plugin_dir_path( __FILE__ ) . '/variables.php', 'w');
            break;
         case 'root': 
            $fh = fopen(ABSPATH . '/variables.php', 'w');
            break;
         case 'theme': 
            $fh = fopen(get_theme_root() . '/variables.php', 'w');
            break;
         }
         $dashes = '------------------------------------------------------------';
         fwrite($fh, '<?php // ' . $dashes . $dashes . ' //' . PHP_EOL . "define('USI_VARIABLE_SOLUTIONS', '" . 
            date('Y-m-d H:i:s') . "'); // Location:$location;" . PHP_EOL); 
         $SAFE_variable_table = $wpdb->prefix . 'USI_variables';
         $rows = $wpdb->get_results(
            "SELECT * FROM `$SAFE_variable_table` WHERE (`variable_id` > 1)" .
            " ORDER BY `category` = 'general' DESC, `category`, `order`, `variable`", OBJECT_K);
         foreach ($rows as $row) {
            $variable = $prefix . (('general' == $row->category) ? '' : $row->category . '_') . strtoupper($row->variable);
            if ('E' == $row->type) {
               $value = $row->value;
            } else {
               $value = "'" . str_replace("'", "\'", $row->value) . "'";
            }
            fwrite($fh, "define('$variable', $value);" . PHP_EOL);
         }

         $shortcode_function = USI_Settings::$options[USI_Variable_Solutions::PREFIX]['preferences']['shortcode-function'];

         fwrite($fh, 'function ' . $shortcode_function . '($attributes, $content = null) {' . PHP_EOL);
         fwrite($fh, '   $category = !empty($attributes[\'category\']) ? $attributes[\'category\'] : null;' . PHP_EOL);
         fwrite($fh, '   $item = !empty($attributes[\'item\']) ? $attributes[\'item\'] : null;' . PHP_EOL);
         fwrite($fh, '   switch ($category) {' . PHP_EOL);
         
         $old_category = null;
         foreach ($rows as $row) {
            if ($old_category != $row->category) {
               $this->fields_sanitize_publish($fh, $old_category);
               if ('general' == $row->category) fwrite($fh, "   default:" . PHP_EOL);
               fwrite($fh, "   case '{$row->category}':" . PHP_EOL);
               fwrite($fh, '      switch ($item) {' . PHP_EOL);
               $old_category = $row->category;
            }
            $variable = $prefix . (('general' == $row->category) ? '' : $row->category . '_') . strtoupper($row->variable);
            if ('date' == $row->category) {
               fwrite($fh, "      case '" . $row->variable . '\': $time = ' . $variable . '; break;' . PHP_EOL);
            } else {
               fwrite($fh, "      case '{$row->variable}': return($variable);" . PHP_EOL);
            }
         }
         $this->fields_sanitize_publish($fh, $old_category);
         fwrite($fh, '   }' . PHP_EOL);
         fwrite($fh, '   return(\'bad request:item=\' . $item);' . PHP_EOL);
         fwrite($fh, '} // ' . $shortcode_function . '();' . PHP_EOL);
         
         fwrite($fh, '// ---' . $dashes .$dashes . ' // ?>' . PHP_EOL);
         fclose($fh);
      }
      return($input);
   } // fields_sanitize();

   function fields_sanitize_publish($fh, $old_category) {
      if ($old_category) {
         fwrite($fh, '      }' . PHP_EOL);
         if ('date' == $old_category) {
            fwrite($fh, '      if (!empty($attributes[\'format\'])) $time = date($attributes[\'format\'], ' .
               'strtotime($time));' . PHP_EOL . '      return($time);' . PHP_EOL);
         } else {
            fwrite($fh, '      break;' . PHP_EOL);
         }
      }
   } // fields_sanitize_publish();

   function filter_plugin_row_meta($links, $file) {
      if (false !== strpos($file, USI_Variable_Solutions::TEXTDOMAIN)) {
         $links[] = '<a href="https://www.usi2solve.com/donate/variable-solutions" target="_blank">' . 
            __('Donate', USI_Variable_Solutions::TEXTDOMAIN) . '</a>';
      }
      return($links);
   } // filter_plugin_row_meta();

} // Class USI_Variable_Solutions_Settings;

new USI_Variable_Solutions_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>