<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

if (!class_exists('USI_Settings_Versions')) { class USI_Settings_Versions {

   const VERSION = '1.0.0 (2017-12-14)';

   private function __construct() {
   } // __construct();

   public static function action() {
      add_action('init', 'add_thickbox');
   } // action();

   public static function link($link_text, $title, $version, $text_domain, $file) {

      $id = 'usi-settings-versions-' . $title;

      $ajax = plugin_dir_url($file) . 'usi-settings-versions-scan.php';

      return(
         '<a id="' . $id . '-link" class="thickbox" href="">' . $link_text . '</a>' . 
         '<div id="' . $id . '-popup" style="display:none;">' .  
           '<p id="' . $id . '-list"></p>' . 
           '<hr>' . 
           '<p>' . 
             '<a class="button" href="" onclick="tb_remove()">' . __('Close', $text_domain) . '</a>' . 
           '</p>' . 
         '</div>' .  
         '<script>' . 
         'jQuery(document).ready(' . 
            'function($) {' . 
               'function resize() {' . 
                  'var padding_left = $("#TB_ajaxContent").css("padding-left");' .
                  'var padding = padding_left.substring(0, padding_left.length - 2);' .
                  'var width = $("#TB_window").width() - 2 * padding;' . 
                  'var height = $("#TB_window").height() - 75;' . 
                  "$('#TB_ajaxContent').css({'width' : width, 'height' : height});" .
               '}' .
               '$("#' . $id . '-link").click(' . 
                  'function(event) {' . 
                     'tb_show("' . $title . ' &nbsp; &nbsp; Version ' . $version . ' ", "#TB_inline?inlineId=' . $id . '-popup", null);' . 
                     '$("#' . $id . '-list").load("' . $ajax . '", "title=' . $title . '", resize);' . 
                     'return(false);' . 
                  '}' . 
               ');' . 
               '$(window).resize(resize);' .
            '}' . 
         ');' . 
         '</script>');
   } //link();

} /* Class USI_Settings_Versions; */ }

// --------------------------------------------------------------------------------------------------------------------------- // ?>