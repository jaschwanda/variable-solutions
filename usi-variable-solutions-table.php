<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

if (!class_exists('WP_List_Table')) { require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php'); }

require_once(WP_PLUGIN_DIR . '/usi-wordpress-solutions/usi-wordpress-solutions-static.php');

final class USI_Variable_Solutions_Table extends WP_List_Table {

   const VERSION = '2.5.0 (2023-07-04)';

   private $all_categories = null;
   private $category = null;
   private $options_category = null;
   private $page_hook = null;

   function __construct() {

      $this->options_category = USI_Variable_Solutions::PREFIX . '-options-category';

      add_action('admin_menu', array($this, 'action_admin_menu'));

      if (!empty($_GET['page']) && (USI_Variable_Solutions::VARYLIST == $_GET['page'])) {

         add_action('admin_head', array($this, 'action_admin_head'));

         add_filter('set-screen-option', array($this, 'filter_set_screen_options'), 10, 3);

      }

      $this->category = $this->all_categories = __('All Categories', USI_Variable_Solutions::TEXTDOMAIN);

   } // __construct();

   function action_admin_head() {

      if (USI_Variable_Solutions::VARYLIST != ((isset($_GET['page'])) ? $_GET['page'] : '')) return;

      $columns = array(
         'cb'          => 3, 
         'variable_id' => 7, 
         'variable'    => 15, 
         'category'    => 10, 
         'type'        => 10, 
         'value'       => 15, 
         'notes'       => 15, 
         'owner'       => 10, 
         'updated'     => 15,
      );

      echo USI_WordPress_Solutions_Static::column_style($columns, 'overflow:hidden; text-overflow:ellipsis; white-space:nowrap;');

   } // action_admin_head();

   function action_admin_menu() {

      $capability = USI_WordPress_Solutions_Capabilities::capability_slug(USI_Variable_Solutions::PREFIX, 'view-variables');

      $position   = USI_Variable_Solutions::$options['preferences']['menu-position'];

      if ((0 == $position) || ('null' == $position)) $position = null;

      $this->page_hook = add_menu_page(
         __(USI_Variable_Solutions::NAME, USI_Variable_Solutions::TEXTDOMAIN), // Text displayed in <title> when menu is selected;
         __('Variables', USI_Variable_Solutions::TEXTDOMAIN), // Text displayed in menu; 
         $capability, // Capability required to enable page; 
         USI_Variable_Solutions::VARYLIST, // Unique slug to of this menu; 
         array($this, 'render_page'), // Function called to render page content;
         USI_Variable_Solutions::$options['preferences']['menu-icon'], // Menu icon;
         $position // Menu position;
      );

      add_action('load-' . $this->page_hook, array($this, 'action_load_screen_options'));
   
   } // action_admin_menu();

   function action_load_screen_options() {

      $option = 'per_page';

      $args = array(
         'label' => __('Variables per page', USI_Variable_Solutions::TEXTDOMAIN),
         'default' => 20,
         'option' => $option,
      );

      add_screen_option($option, $args);

      parent::__construct( 
         array(
            'singular' => __('variable', USI_Variable_Solutions::TEXTDOMAIN), 
            'plural' => __('variables', USI_Variable_Solutions::TEXTDOMAIN),
            'ajax' => false,
         ) 
      );

   } // action_load_screen_options();

   function column_cb($item) {

      return('<input class="' . USI_Variable_Solutions::VARYLIST . '"' .
         ' data-id="' . esc_attr($item['variable_id']) . '"' .
         ' data-name="' . esc_attr($item['variable']) . '"' .
         ' data-value="' . esc_attr($item['value']) . '"' .
         ' name="variable_id[]" type="checkbox" value="' . $item['variable_id'] .'" />');

    } // column_cb();

   function column_default($item, $column_name) {

      switch($column_name) { 
      case 'variable_id':
      case 'category':
      case 'notes':
      case 'owner':
      case 'type':
      case 'value':
      case 'variable':
         return $item[$column_name];
      case 'updated':
         $updated = strtotime($item['updated']);
         if ((abs(time() - $updated)) < 86400){
            return(sprintf('%s ago', human_time_diff($updated, strtotime(current_time('mysql')))));
         } else {
            return(date_i18n('j F, Y \a\t G:i', $updated));
         }
      default:
         return(print_r($item, true)); //Show the whole array for troubleshooting purposes
      }

   } // column_default();

   function column_variable($item) {

      $actions = array();

      if (USI_Variable_Solutions::$variables_change || USI_Variable_Solutions::$variables_edit) {
         $actions['edit'] = '<a href="options-general.php?page=usi-variable&variable_id=' .
            $item['variable_id'] . '">' . __('Edit', USI_Variable_Solutions::TEXTDOMAIN) . '</a>';
      }

      if (USI_Variable_Solutions::$variables_delete) {
         $actions['delete'] = '<a' .
            ' class="thickbox usi-variable-delete-link"' .
            ' data-id="' . esc_attr($item['variable_id']) . '"' .
            ' data-name="' . esc_attr($item['variable']) . '"' .
            ' data-value="' . esc_attr($item['value']) . '"' .
            ' href=""' .
            '">' . __('Delete', USI_Variable_Solutions::TEXTDOMAIN) . '</a>';
      }

      return($item['variable'] . ' ' . $this->row_actions($actions));

   } // column_variable();

   function extra_tablenav($which) {

      global $wpdb;

      if ('top' == $which) {        
         $SAFE_variables_table = $wpdb->prefix . 'USI_variables';
         $rows = $wpdb->get_results("SELECT DISTINCT `category` FROM `$SAFE_variables_table` WHERE (`category` <> '') ORDER BY `category`", OBJECT_K);
         echo '      <div class="alignleft actions bulkactions"><select id="' . $this->options_category . 
            '" name="' . $this->options_category . '">' .
            '<option ' . (($this->all_categories == $this->category) ? 'selected="selected" ' : '') . 'value="' . 
            $this->all_categories . '">' . $this->all_categories . '</option>';
         foreach ($rows as $row) {
            echo '<option ' . (($row->category == $this->category) ? 'selected="selected" ' : '') . 'value="' . $row->category . '">' . $row->category . '</option>';
         }
         echo '</select><input class="button action" type="submit" name="usi-filter" id="usi-filter" value="Filter" /></div>' . PHP_EOL;
      }

   } // extra_tablenav();

   function filter_set_screen_options($status, $option, $value) {

      if ('per_page' == $option) return($value);

      return($status);

   } // filter_set_screen_options();

   function get_bulk_actions() {

      return(
         array(
            'delete' => __('Delete', USI_Variable_Solutions::TEXTDOMAIN),
         )
      );

   } // get_bulk_actions();

   function get_columns() {

      return(
         array(
            'cb' => '<input type="checkbox" />',
            'variable_id' => __('ID', USI_Variable_Solutions::TEXTDOMAIN),
            'variable' => __('Variable', USI_Variable_Solutions::TEXTDOMAIN),
            'category' => __('Category', USI_Variable_Solutions::TEXTDOMAIN),
            'type' => __('Type', USI_Variable_Solutions::TEXTDOMAIN),
            'value' => __( 'Value', USI_Variable_Solutions::TEXTDOMAIN),
            'notes' => __('Notes', USI_Variable_Solutions::TEXTDOMAIN),
            'owner' => __('Owner', USI_Variable_Solutions::TEXTDOMAIN),
            'updated' => __('Updated', USI_Variable_Solutions::TEXTDOMAIN),
         )
      );

    } // get_columns();

   public function get_hidden_columns() {

      return((array)get_user_option('manage' . $this->page_hook . 'columnshidden'));

   } // get_hidden_columns();

   function get_list() {

      global $wpdb;

      $paged = (int)(isset($_GET['paged']) ? $_GET['paged'] : 1);

      $filter = $this->safe_name(isset($_POST['usi-filter']) ? $_POST['usi-filter'] : '');
      if ('Filter' == $filter) $paged = 1;

      $SAFE_order = (isset($_GET['order'])) ? (('desc' == strtolower($_GET['order'])) ? 'DESC' : '') : '';
      $WILD_orderby = (isset($_GET['orderby']) ? $_GET['orderby'] : '');
      switch ($WILD_orderby) {
      default: $SAFE_orderby = 'category` ' . $SAFE_order . ', `variable'; break;
      case 'notes': 
      case 'owner': 
      case 'type':
      case 'updated':
      case 'variable':
      case 'variable_id': $SAFE_orderby = $WILD_orderby;
      }

      $SAFE_orderby = 'ORDER BY `' . $SAFE_orderby . '` ' . $SAFE_order;
      $SAFE_search = ((isset($_POST['s']) && ('' != $_POST['s'])) ? $wpdb->prepare(' AND (`variable` = %s)', $_POST['s']) : '');
      if ('' == $SAFE_search) {
         if (!empty($_POST[$this->options_category])) {
            $this->category = $_POST[$this->options_category];
         } else if (!empty($_GET['filter'])) {
            $this->category = $_GET['filter'];
         } else {
            $category = get_user_option($this->options_category);
            $this->category = (!empty($category) ? $category : $this->all_categories);
         }
         update_user_option(get_current_user_id(), $this->options_category, $this->category);
         if ($this->all_categories != $this->category) $SAFE_search = $wpdb->prepare(' AND (`category` = %s)', $this->category);
      }

      $current_page = $this->get_pagenum();
      $SAFE_per_page = (int)$this->get_items_per_page('per_page', 20);
      $SAFE_skip = (int)($SAFE_per_page * ($paged - 1));

      $SAFE_variables_table = $wpdb->prefix . 'USI_variables';
      $count_of_records = $wpdb->get_var("SELECT COUNT(*) FROM `$SAFE_variables_table` WHERE (`variable_id` > 1)$SAFE_search");

      $SAFE_users_table = $wpdb->prefix . 'users';
      $this->items = $wpdb->get_results(
         "SELECT `variable_id`, `category`, `variable`, `type`, `value`, `display_name` as `owner`, " .
         "`$SAFE_variables_table`.`updated`, `$SAFE_variables_table`.`notes` FROM `$SAFE_variables_table`" .
         " INNER JOIN `$SAFE_users_table` ON `$SAFE_users_table`.`ID` = `$SAFE_variables_table`.`user_id`" . 
         " WHERE (`variable_id` > 1)$SAFE_search $SAFE_orderby LIMIT $SAFE_skip,$SAFE_per_page", ARRAY_A);

      for ($ith = 0; $ith < count($this->items); $ith++) {
         $this->items[$ith]['type'] = ('V' == $this->items[$ith]['type'] ? 'Variable' : 'Expression');
         $this->items[$ith]['value'] = htmlentities($this->items[$ith]['value']);
      }

      $this->set_pagination_args(
         array(
            'total_items' => $count_of_records,
            'per_page' => $SAFE_per_page,
            'total_pages' => ceil($count_of_records / $SAFE_per_page),
         )
      );

   } // get_list();

   function get_sortable_columns() {

      return(
         array(
            'variable_id' => array('variable_id', true),
            'category' => array('category', true),
            'variable' => array('variable', false),
            'notes' => array('notes', false),
            'owner' => array('owner', false),
            'type' => array('type', false),
            'updated' => array('updated', false),
         )
      );

   } // get_sortable_columns();

   function no_items() {

      _e('No variables have been configured.', USI_Variable_Solutions::TEXTDOMAIN);

    } // no_items();

   function prepare_items() {

      $columns = $this->get_columns();
      $hidden = $this->get_hidden_columns();
      $sortable = $this->get_sortable_columns();
      $this->_column_headers = array($columns, $hidden, $sortable);
      $this->get_list();

   } //prepare_items():

   function render_page() {

      global $wpdb;

      $action = $this->current_action();

      if ('delete' == $action) {
         if (USI_Variable_Solutions::$variables_delete) {
            $SAFE_variable_table = $wpdb->prefix . 'USI_variables';
            $ids = isset($_REQUEST['variable_id']) ? explode(',', $_REQUEST['variable_id']) : array();
            $variables_deleted = count($ids);
            if (is_array($ids)) $ids = implode(',', $ids);
            if (!empty($ids)) {
               $wpdb->query("DELETE FROM `$SAFE_variable_table` WHERE (`variable_id` IN($ids))");
            } else {
               $variables_deleted = 0;
            }
            $delete_text = ((1 == $variables_deleted) ? __('One variable has been deleted', USI_Variable_Solutions::TEXTDOMAIN) : 
               sprintf(__('%d variables have been deleted', USI_Variable_Solutions::TEXTDOMAIN), $variables_deleted));
         } else {
            $delete_text =  __('You do not have permission to delete variables', USI_Variable_Solutions::TEXTDOMAIN);
         }
         $message = '<div class="updated below-h2 notice is-dismissible" id="message"><p>' . $delete_text . '.</p>' .
            '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' .
            __('Dismiss this notice', USI_Variable_Solutions::TEXTDOMAIN) . '.</span></button></div>';
      } else {
         $message = null;
      }
?>

<!-- usi-variable-solutions:render_page:begin ---------------------------------------------------------------------------------- -->
<div class="wrap">
  <h2><?php 
   _e('Variables', USI_Variable_Solutions::TEXTDOMAIN); 
   if (USI_Variable_Solutions::$variables_add) 
      echo ' <a class="add-new-h2" href="options-general.php?page=usi-variable">' . 
         __('Add New', USI_Variable_Solutions::TEXTDOMAIN) . '</a>';
   if (USI_Variable_Solutions::$variables_publish) 
      echo ' <a class="add-new-h2" href="options-general.php?page=usi-variable-settings&tab=publish">' . 
         __('Publish', USI_Variable_Solutions::TEXTDOMAIN) . '</a>';
  ?></h2>
  <?php if ($message) echo $message . PHP_EOL;?>
  <form action="" method="post" name="<?php echo USI_Variable_Solutions::VARYLIST;?>">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'];?>">
<?php
      $this->prepare_items(); 
      $this->search_box('search', 'search_id');
      $this->display(); 
?>
  </form>
</div>
<div id="usi-variable-confirm" style="display:none;"></div>
<script>
jQuery(document).ready(
   function($) {

      var text_confirm_prefix = '<?php _e("Please confirm that you want to delete the following variable(s)", USI_Variable_Solutions::TEXTDOMAIN);?>';
      var text_confirm_suffix = '<?php _e("This deletion is permanent and cannot be reversed", USI_Variable_Solutions::TEXTDOMAIN);?>';
      var text_cancel = '<?php _e("Cancel", USI_Variable_Solutions::TEXTDOMAIN);?>';
      var text_delete = '<?php _e("Delete", USI_Variable_Solutions::TEXTDOMAIN);?>';
      var text_ok     = '<?php _e("Ok", USI_Variable_Solutions::TEXTDOMAIN);?>';
      var text_please_action   = '<?php _e("Please select a bulk action before you click the Apply button.", USI_Variable_Solutions::TEXTDOMAIN);?>';
      var text_please_variable = '<?php _e("Please select one or more variables before you click the Apply button.", USI_Variable_Solutions::TEXTDOMAIN);?>';

      function do_action() {

         var ids = $('.usi-variable');
         var id_list = '';
         var text = '';

         if ('delete' != $('#bulk-action-selector-top').val()) {
            text = text_please_action;
         } else {
            var delete_count = 0;
            for (var i = 0; i < ids.length; i++) {
               if (ids[i].checked) {
                  id_list += (id_list.length ? ',' : '') + ids[i].getAttribute('data-id');
                  text += (delete_count++ ? '<br/>' : '') + ids[i].getAttribute('data-name') + ' = ' + ids[i].getAttribute('data-value');
               }
            }
            if (!delete_count) {
               text = text_please_variable;
            }
         }

         return(show_confirmation(delete_count, id_list, text));

      } // do_action();

      function show_confirmation(count_of_variables, id, text) {

         var html = '<p>';

         if (count_of_variables) {
            html += text_confirm_prefix + ':</p><p>' + text + '</p><p>' + text_confirm_suffix + '.';
         } else {
            html += text;
         }

         html += '</p><hr/><p>';

         if (count_of_variables) html += 
            '<a class="button" href="?page=<?php echo USI_Variable_Solutions::VARYLIST;?>&action=delete&variable_id=' +
            id + '">' + text_delete + '</a> &nbsp; ';

         html += '<a class="button" href="" onclick="tb_remove()">' +
            (count_of_variables ? text_cancel : text_ok) + '</a>';

         $('#usi-variable-confirm').html(html);

         tb_show('Variable-Solutions', '#TB_inline?width=500&height=300&inlineId=usi-variable-confirm', null);

         return(false);

      } // show_confirmation();

      $('#doaction').click(do_action); 

      $('#doaction2').click(do_action); 

      $('.usi-variable-delete-link').click(
         function(event) {
            var obj = event.target;
            var id = obj.getAttribute('data-id');
            var text = obj.getAttribute('data-name') + ' = ' + obj.getAttribute('data-value');
            return(show_confirmation(1, id, text));
         }
      );

   }
);
</script>
<!-- usi-variable-solutions:render_page:end ------------------------------------------------------------------------------------ -->
<?php
   } // render_page();

   static function safe_name($category) {

      return(strtolower(str_replace('-', '_', sanitize_title($category))));

   } // safe_name();

} // Class USI_Variable_Solutions_Table;

new USI_Variable_Solutions_Table();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
