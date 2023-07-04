<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once(WP_PLUGIN_DIR . '/usi-wordpress-solutions/usi-wordpress-solutions-capabilities.php');
require_once(WP_PLUGIN_DIR . '/usi-wordpress-solutions/usi-wordpress-solutions-settings.php');
require_once(WP_PLUGIN_DIR . '/usi-wordpress-solutions/usi-wordpress-solutions-versions.php');

class USI_Variable_Solutions_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '2.5.0 (2023-07-04)';

   protected $is_tabbed = true;

   function __construct() {

      parent::__construct(
         [
            'capabilities' => USI_Variable_Solutions::$capabilities,
            'name' => USI_Variable_Solutions::NAME, 
            'options' => USI_Variable_Solutions::$options,
            'prefix' => USI_Variable_Solutions::PREFIX, 
            'text_domain' => USI_Variable_Solutions::TEXTDOMAIN,
         ]
      );

   } // __construct();

   function fields_sanitize($input) {

      if (empty($input['preferences']['menu-icon'])) {
         $input['preferences']['menu-icon'] = 'dashicons-controls-repeat';
      } else {
         $input['preferences']['menu-icon'] = sanitize_title(strtolower($input['preferences']['menu-icon']));
      }

      if (empty($input['preferences']['menu-position'])) {
         $input['preferences']['menu-position'] = 'null';
      } else {
         $input['preferences']['menu-position'] = (int)$input['preferences']['menu-position'];
         if (0 == $input['preferences']['menu-position']) $input['preferences']['menu-position'] = 'null';
      }

      if (empty($input['preferences']['shortcode-function'])) {
         $input['preferences']['shortcode-function'] = 'usi_variable_shortcode';
      } else {
         $input['preferences']['shortcode-function'] = sanitize_title(strtolower($input['preferences']['shortcode-function']));
      }

      if (empty($input['preferences']['shortcode-prefix'])) {
         $input['preferences']['shortcode-prefix'] = 'variable';
      } else {
         $input['preferences']['shortcode-prefix'] = sanitize_title(strtolower($input['preferences']['shortcode-prefix']));
      }

      if (empty($input['preferences']['variable-prefix'])) {
         global $wpdb;
         $input['preferences']['variable-prefix'] = $wpdb->prefix;
      } else {
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

         fwrite($fh, 'function ' . $shortcode_function . '($attr, $content = null) {' . PHP_EOL);
         fwrite($fh, '   $category = $attr[\'category\'] ?? null;' . PHP_EOL);
         fwrite($fh, '   $item     = $attr[\'item\']     ?? null;' . PHP_EOL);
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
            switch($row->category) {
            case 'date':
               fwrite($fh, "      case '{$row->variable}': \$time = $variable; break;" . PHP_EOL);
               break;
            case 'email':
               fwrite($fh, "      case '{$row->variable}': \$email = $variable; break;" . PHP_EOL);
               break;
            default:
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
         if ('email' == $old_category) {
            fwrite($fh, '      default: break 2;' . PHP_EOL);
         }
         fwrite($fh, '      }' . PHP_EOL);
         if ('date' == $old_category) {
            fwrite($fh, '      if (!empty($attr[\'format\'])) $time = date($attr[\'format\'], ' .
               'strtotime($time));' . PHP_EOL . '      return($time);' . PHP_EOL);
         } else if ('email' == $old_category) {
            fwrite($fh, '      list($email, $content, $prefix, $suffix) = explode(\'|\', $email);' . PHP_EOL);
            fwrite($fh, '      $args = [\'email\' => $email];' . PHP_EOL);
            fwrite($fh, '      if (!empty($attr[\'class\']))   $args[\'class\']   = $attr[\'class\'];'   . PHP_EOL);
            fwrite($fh, '      if (!empty($attr[\'id\']))      $args[\'id\']      = $attr[\'id\'];'      . PHP_EOL);
            fwrite($fh, '      if (!empty($attr[\'style\']))   $args[\'style\']   = $attr[\'style\'];'   . PHP_EOL);
            fwrite($fh, '      if (!empty($attr[\'subject\'])) $args[\'subject\'] = $attr[\'subject\'];' . PHP_EOL);
            fwrite($fh, '      return($prefix . USI_WordPress_Solutions::shortcode_email($args, $content) . $suffix);' . PHP_EOL);
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

      if ('root' == ($folder = USI_Variable_Solutions::get_variables_folder())) $folder = 'WordPress wp-config.php';
      $publish  = '<p style="text-align:justify;">' . sprintf(__('Enter an explaination for publishing the variables and click on the <b>Publish Variables</b> button. The variables.php file will be published in the %s folder.', USI_Variable_Solutions::TEXTDOMAIN), $folder) . '</p>' . PHP_EOL;

      $sections = [
         'preferences' => [
            'header_callback' => [$this, 'sections_header', '    <p style="text-align:justify;">' . __('Changing these settings after the system is in use may cause referencing errors. Make sure that you also change the <b>[ID attribute="value"]</b> shortcodes in your content and the <b>defined(variable, "value")</b> statments in your PHP files to match the settings you enter here.', USI_Variable_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL],
            'label' => 'Preferences',
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
            'settings' => [
               'variable-prefix' => [
                  'type' => 'text', 
                  'label' => 'Variable Prefix',
                  'notes' => 'Enter lower case text, no spaces or punctuation except the underscore. This is the string that prefixes <b>variable</b> in the <b>define(variable, "value")</b> statements in the variables.php file and is used to ensure that your variable names are unique. Defaults to the WordPress database prefix.',
               ],
               'shortcode-prefix' => [
                  'type' => 'text', 
                  'label' => 'Shortcode Identifier',
                  'notes' => 'Enter lower case text, no spaces or punctuation. This is the <b>ID</b> in [<b>ID</b> attribute="value"] used to access the variable shortcodes in you content. Defaults to <b>variable</b>.',
               ],
               'shortcode-function' => [
                  'f-class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Shortcode Function Name',
                  'notes' => 'Enter lower case text, no spaces or punctuation except the underscore. This is the name of the PHP function that executes the variable shortcodes. Defaults to <b>usi_variable_shortcode</b>.',
               ],
               'menu-icon' => [
                  'f-class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Variable List Page Menu Icon',
                  'notes' => 'Enter the dashicons text string, see <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">developer.wordpress.org/resource/dashicons</a> for choices. Defaults to <b>dashicons-controls-repeat</b>.',
               ],
               'menu-position' => [
                  'type' => 'text', 
                  'label' => 'Variable List Menu Position',
                  'notes' => 'Enter a numeric value, blank or null appends the menu item to the bottom of the menu. Defaults to <b>null</b>.',
               ],
               'file-location' => [
                  'type' => 'radio', 
                  'label' => 'Location of variables.php File',
                  'choices' => [
                     [
                        'value' => 'plugin', 
                        'label' => true, 
                        'notes' => __('Plugin folder', USI_Variable_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ],
                     [
                        'value' => 'theme', 
                        'label' => true, 
                        'notes' => __('Theme folder', USI_Variable_Solutions::TEXTDOMAIN), 
                        'suffix' => ' &nbsp; &nbsp; &nbsp; ',
                     ],
                     [
                        'value' => 'root', 
                        'label' => true, 
                        'notes' => __('WordPress wp-config.php folder', USI_Variable_Solutions::TEXTDOMAIN), 
                     ],
                  ],
                  'notes' => 'Defaults to WordPress <b>wp-config.php</b> folder.',
               ], // file-location;
            ], // settings;
         ], // preferences;

         'capabilities' => new USI_WordPress_Solutions_Capabilities($this),

         'publish' => [
            'header_callback' => [$this, 'sections_header', $publish],
            'label' => 'Publish',
            'settings' => [
               'explaination' => [
                  'f-class' => 'regular-text', 
                  'type' => 'textarea', 
                  'label' => 'Explaination',
                  'notes' => 'Enter up to 255 printable characters.', 
               ],
            ],
            'submit' => __('Publish Variables', USI_Variable_Solutions::TEXTDOMAIN),
         ], // publish;

      ]; // sections;

      return($sections);

   } // sections();

} // Class USI_Variable_Solutions_Settings;

new USI_Variable_Solutions_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>