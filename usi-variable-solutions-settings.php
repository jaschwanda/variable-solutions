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

require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-capabilities.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-settings.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-updates.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-versions.php');

class USI_Variable_Solutions_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '2.4.6 (2022-07-12)';

   protected $is_tabbed = true;

   function __construct() {

      parent::__construct(
         array(
            'name' => USI_Variable_Solutions::NAME, 
            'prefix' => USI_Variable_Solutions::PREFIX, 
            'text_domain' => USI_Variable_Solutions::TEXTDOMAIN,
            'options' => USI_Variable_Solutions::$options,
            'capabilities' => USI_Variable_Solutions::$capabilities,
         )
      );

   } // __construct();

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
      if ('publish' == (!empty($_REQUEST['usi-variable-tab']) ? $_REQUEST['usi-variable-tab'] : null)) {
         USI_WordPress_Solutions_History::history(get_current_user_id(), 'vary', 
            'Published variables', 0, $input['publish']['explaination']);
         $input['publish']['explaination'] = '';
         $prefix = USI_Variable_Solutions::$options['preferences']['variable-prefix'];
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
         fwrite($fh, ''
            . '<?php // ' . $dashes . $dashes . ' //' . PHP_EOL 
            . "defined('ABSPATH') or die('Accesss not allowed.');" . PHP_EOL 
            . "define('USI_VARIABLE_SOLUTIONS', '" . date('Y-m-d H:i:s') . "'); // Location:$location;" . PHP_EOL
         ); 
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

         $shortcode_function = USI_Variable_Solutions::$options['preferences']['shortcode-function'];

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
         $links[0] = USI_WordPress_Solutions_Versions::link(
            $links[0], 
            USI_Variable_Solutions::NAME, 
            USI_Variable_Solutions::VERSION, 
            USI_Variable_Solutions::TEXTDOMAIN, 
            __DIR__ // Folder containing plugin or theme;
         );
         $links[] = '<a href="https://www.usi2solve.com/donate/variable-solutions" target="_blank">' . 
            __('Donate', USI_Variable_Solutions::TEXTDOMAIN) . '</a>';
      }
      return($links);
   } // filter_plugin_row_meta();

   function sections() {

      $sections = array(
         'preferences' => array(
            'header_callback' => array($this, 'config_section_header_preferences'),
            'label' => 'Preferences',
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
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
                  'f-class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Shortcode function name',
                  'notes' => 'Enter lower case text, no spaces or punctuation except the underscore. This is the name of the PHP function that executes the variable shortcodes. Defaults to <b>usi_variable_shortcode</b>.',
               ),
               'menu-icon' => array(
                  'f-class' => 'regular-text', 
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

         'capabilities' => new USI_WordPress_Solutions_Capabilities($this),

         'publish' => array(
            'header_callback' => array($this, 'config_section_header_publish'),
            'label' => 'Publish',
            'settings' => array(
               'explaination' => array(
                  'f-class' => 'regular-text', 
                  'type' => 'textarea', 
                  'label' => 'Explaination',
                  'notes' => 'Enter up to 255 printable characters.', 
               ),
            ),
            'submit' => __('Publish Variables', USI_Variable_Solutions::TEXTDOMAIN),
         ), // publish;

         'updates' => new USI_WordPress_Solutions_Updates($this),

      );

      return($sections);

   } // sections();

} // Class USI_Variable_Solutions_Settings;

new USI_Variable_Solutions_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>