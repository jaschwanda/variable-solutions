<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once('usi-settings.php'); 

if (!class_exists('USI_Settings_Admin')) { class USI_Settings_Admin {

   const VERSION = '1.0.2 (2017-11-01)';

   protected $is_tabbed = false;
   protected $sections = null;

   private $active_tab = null;
   private $name = null;
   private $option_name = null;
   private $page_slug = null;
   private $prefix = null;
   private $section_callback_offset = 0;
   private $section_callbacks = array();
   private $section_ids = array();
   private $text_domain = null;

   function __construct($name, $prefix, $text_domain) {

      $this->name = $name;
      $this->option_name = $prefix . '-options';
      $this->page_slug   = self::page_slug($prefix);
      $this->prefix = $prefix;
      $this->text_domain = $text_domain;

      if ($this->is_tabbed) {
         $prefix_tab = $this->prefix . '-tab';
         $active_tab = !empty($_POST[$prefix_tab]) ? $_POST[$prefix_tab] : (!empty($_GET['tab']) ? $_GET['tab'] : null);
         $default_tab = null;
         foreach ($this->sections as $section_id => $section) {
            if (!$default_tab) $default_tab = $section_id;
            if ($section_id == $active_tab) {
               $this->active_tab = $active_tab;
               break;
            }
         }
         if (!$this->active_tab) $this->active_tab = $default_tab;
      }

      add_action('admin_head', array($this, 'action_admin_head'));
      add_action('admin_init', array($this, 'action_admin_init'));
      add_action('admin_menu', array($this, 'action_admin_menu'));

      add_filter('plugin_action_links', array($this, 'filter_plugin_action_links'), 10, 2);

   } // __construct();

   function action_admin_head() {
      if ($this->page_slug != ((!empty($_GET['page'])) ? esc_attr($_GET['page']) : '')) return;
      echo '<style>' . PHP_EOL .
          '.form-table td{padding-bottom:12px; padding-top:2px;} /* 25px; */' . PHP_EOL .
          '.form-table th{padding-bottom:7px; padding-top:7px;} /* 20px; */' . PHP_EOL .
          '</style>' . PHP_EOL;
   } // action_admin_head();

   function action_admin_init() {  

      $prefix = $this->prefix;

      foreach ($this->sections as $section_id => $section) {

         $this->section_callbacks[] = !empty($section['header_callback']) ? $section['header_callback'] : null;
         $this->section_ids[] = $section_id;

         add_settings_section(
            $section_id, // Section id;
            !$this->is_tabbed && !empty($section['label']) ? $section['label'] : null, // Section title;
            array($this, 'section_render'), // Render section callback;
            $this->page_slug // Settings page menu slug;
         );

         if (!empty($section['after_add_settings_section'])) {
            $object = $section['after_add_settings_section'][0];
            $method = $section['after_add_settings_section'][1];
            if (method_exists($object, $method)) $section['settings'] = $object->$method($section['settings']);
         }

         if (!empty($section['settings'])) {
            foreach ($section['settings'] as $option_id => $attributes) {
               add_settings_field(
                  $option_id, // Option name;
                  !empty($attributes['label']) ? $attributes['label'] : null, // Field title; 
                  array($this, 'fields_render'), // Render field callback;
                  $this->page_slug, // Settings page menu slug;
                  $section_id, // Section id;
                  array_merge($attributes, 
                     array(
                        'name' => $this->option_name . '[' . $section_id . ']['  . $option_id . ']',
                        'value' => !empty(USI_Settings::$options[$prefix][$section_id][$option_id]) 
                           ? USI_Settings::$options[$prefix][$section_id][$option_id] : null,
                     )
                  )
               );
            }
         }

      }

      register_setting(
         $this->page_slug, // Settings group name, must match the group name in settings_fields();
         $this->option_name, // Option name;
         array($this, 'fields_sanitize') // Sanitize field callback;
      );

   } // action_admin_init();

   function action_admin_menu() { 
      add_options_page(
         __($this->name . ' Settings', $this->text_domain), // Page <title/> text;
         __($this->name, $this->text_domain), // Sidebar menu text; 
         'manage_options', // Capability required to enable page;
         $this->page_slug, // Menu page slug name;
         array($this, 'page_render') // Render page callback;
      );
   } // action_admin_menu();

   static function fields_render($args) {

      $notes    = !empty($args['notes']) ? $args['notes'] : null;
      $type     = !empty($args['type'])  ? $args['type']  : 'text';

      $id       = !empty($args['id'])    ? ' id="'    . $args['id']    . '"' : null;
      $class    = !empty($args['class']) ? ' class="' . $args['class'] . '"' : null;
      $name     = !empty($args['name'])  ? ' name="'  . $args['name']  . '"' : null;

      $min      = isset($args['min'])    ? ' min="'   . $args['min']   . '"' : null;
      $max      = isset($args['max'])    ? ' max="'   . $args['max']   . '"' : null;

      $readonly = !empty($args['readonly']) ? ('checkbox' == $type ? ' disabled' : ' readonly') : null;
      $value    = !empty($args['value']) ? esc_attr($args['value']) : ('number' == $type ? 0 : null);

      $maxlen   = !empty($args['maxlength']) ? (is_integer($args['maxlength']) ? ' maxlength="' . $args['maxlength'] . '"' : null) : null;

      $attributes = $id . $class . $name . $min . $max . $maxlen . $readonly;

      switch ($type) {

      case 'radio':
         foreach ($args['choices'] as $choice) {
            $label = !empty($choice['label']);
            echo (!empty($choice['prefix']) ? $choice['prefix'] : '') .
               ($label ? '<label>' : '') . '<input type="radio"' . $attributes . ' value="' . esc_attr($choice['value']) . '"' . 
               checked($choice['value'], $value, false) . ' />' . $choice['notes'] . ($label ? '</label>' : '') .
               (!empty($choice['suffix']) ? $choice['suffix'] : '');
         }
         break;

      case 'checkbox':
         echo '<input type="checkbox"' . $attributes . ' value="true"' . checked($value, true, false) . ' />';
         break;

      case 'hidden':
      case 'number':
      case 'text':
         echo '<input type="' . $type . '"' . $attributes . ' value="' . $value . '" />';
         break;

      case 'textarea':
         echo '<textarea' . $attributes . '>' . $value . '</textarea>';
         break;

      }

      echo $notes . PHP_EOL;

   } // fields_render();

   function fields_sanitize($input) {

      foreach ($this->sections as $section_id => $section) {
         if (!empty($section['fields_sanitize'])) {
            $object = $section['fields_sanitize'][0];
            $method = $section['fields_sanitize'][1];
            if (method_exists($object, $method)) $input = $object->$method($input);
         }
      }

      return($input);

   } // fields_sanitize();

   function filter_plugin_action_links($links, $file) {
      if (false !== strpos($file, $this->text_domain)) {
         $links[] = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=' . 
            $this->page_slug . '">' . __('Settings', $this->text_domain) . '</a>';
      }
      return($links);
   } // filter_plugin_action_links();

   function page_render() {

      $submit_text = null;

      echo PHP_EOL .
         '<div class="wrap">' . PHP_EOL .
         '  <h1>' . __($this->name . ' Settings', $this->text_domain) . '</h1>' . PHP_EOL .
         '  <form method="post" action="options.php">' . PHP_EOL;

      if ($this->is_tabbed) {
         echo 
            '    <h2 class="nav-tab-wrapper">' . PHP_EOL;
            foreach ($this->sections as $section_id => $section) {
               $active_class = null;
               if ($section_id == $this->active_tab) {
                  $active_class = ' nav-tab-active';
                  $submit_text = isset($section['submit']) ? $section['submit'] : 'Save ' . $section['label'];
               }
               echo '      <a href="options-general.php?page=' . $this->page_slug . '&tab=' . $section_id .
                  '" class="nav-tab' . $active_class . '">' .
                  __($section['label'], $this->text_domain) . '</a>' . PHP_EOL;
            }
         echo
            '    </h2>' . PHP_EOL .
            '    <input type="hidden" name="' . $this->prefix . '-tab" value="' . $this->active_tab . '" />' . PHP_EOL;
      }

      settings_fields($this->page_slug);
      do_settings_sections($this->page_slug);

      if ($this->is_tabbed) {

         if ($this->section_callback_offset) echo PHP_EOL . '</div><!--' . $this->page_slug . '-' . 
            $this->section_ids[$this->section_callback_offset - 1] .'-->' . PHP_EOL . PHP_EOL;

         foreach ($this->sections as $section_id => $section) {
            if ($section_id == $this->active_tab) {
               if (!empty($section['footer_callback'])) {
                  $object = $section['footer_callback'][0];
                  $method = $section['footer_callback'][1];
                  if (method_exists($object, $method)) $submit_text = $object->$method();
               }
            }
         }

      } else {

         // Call the first footer callback function found for submit button HTML;
         foreach ($this->sections as $section_id => $section) {
            if (!empty($section['footer_callback'])) {
               $object = $section['footer_callback'][0];
               $method = $section['footer_callback'][1];
               if (method_exists($object, $method)) $submit_text = $object->$method();
               break;
            }
         }

      }

      if ($submit_text) submit_button($submit_text, 'primary', 'submit', true); 

      echo PHP_EOL .
         '  </form>' . PHP_EOL .
         '</div>' . PHP_EOL;

   } // page_render();

   public static function page_slug($prefix) {
      return($prefix . '-settings');
   } // page_slug();

   function section_render() {

      if ($this->is_tabbed) {
         if ($this->section_callback_offset) echo PHP_EOL . '</div><!--' . $this->page_slug . '-' . 
            $this->section_ids[$this->section_callback_offset - 1] .'-->';
         $section_id = $this->section_ids[$this->section_callback_offset];
         echo PHP_EOL . PHP_EOL . '<div id="' . $this->page_slug . '-' . $section_id . '"' .
            ($this->active_tab != $section_id ? ' style="display:none;"' : '') . '>' . PHP_EOL;
      }

      $section_callback = $this->section_callbacks[$this->section_callback_offset];
      $object = $section_callback[0];
      $method = $section_callback[1];
      if (method_exists($object, $method)) $object->$method();

      $this->section_callback_offset++;

   } // section_render();

} /* Class USI_Settings_Admin; */ }

// --------------------------------------------------------------------------------------------------------------------------- // ?>