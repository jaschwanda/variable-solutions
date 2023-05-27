<?php // ------------------------------------------------------------------------------------------------------------------------ //
defined('ABSPATH') or die('Accesss not allowed.');
define('USI_VARIABLE_SOLUTIONS', '2023-05-23 03:45:39'); // Location:plugin;
define('nbrc_CURRENT_YEAR', date('Y'));
define('nbrc_TLD', (((PHP_OS == 'WINNT') || (PHP_OS == 'Darwin')) ? 'local' : 'org'));
function usi_variable_shortcode($attributes, $content = null) {
   $category = !empty($attributes['category']) ? $attributes['category'] : null;
   $item = !empty($attributes['item']) ? $attributes['item'] : null;
   switch ($category) {
   default:
   case 'general':
      switch ($item) {
      case 'current_year': return(nbrc_CURRENT_YEAR);
      case 'tld': return(nbrc_TLD);
      }
      break;
   }
   return('bad request:item=' . $item);
} // usi_variable_shortcode();
// --------------------------------------------------------------------------------------------------------------------------- // ?>
