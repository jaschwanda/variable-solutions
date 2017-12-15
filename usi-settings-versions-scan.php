<?php // ------------------------------------------------------------------------------------------------------------------------ //

class USI_Settings_Versions_Scan {

   const VERSION = '1.0.0 (2017-12-14)';

   private function __construct() {
   } // __construct();

   public static function versions() {
      $files  = scandir(dirname(__FILE__));
      $title  = !empty($_GET['title']) ? $_GET['title'] : null;
      
      $html = '<table>';

      foreach ($files as $file) {
         if ('.php' == substr($file, -4)) {
            $contents = file_get_contents($file);
            $status = preg_match('/VERSION\s*=\s*\'([(0-9\.\s\-\)]*)/', $contents, $matches);
            if (!empty($matches[1])) $html .= '<tr><td>' . $file . ' &nbsp; &nbsp; </td><td>' . $matches[1] . '</td></tr>';
         }
      }

      $html .= '</table>';

      echo($html);
   } // versions();

} // Class USI_Settings_Versions_Scan;

USI_Settings_Versions_Scan::versions();

// --------------------------------------------------------------------------------------------------------------------------- // ?>