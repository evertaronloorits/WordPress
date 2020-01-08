<?php
//custom post type - weekdays
function omnivo_weekdays_init()
{
    $labels = array(
        'name' => _x('Calendar columns', 'post type general name', 'omnivo_calendar'),
        'singular_name' => _x('Calendar Column', 'post type singular name', 'omnivo_calendar'),
        'add_new' => _x('Add New', 'omnivo_weekdays', 'omnivo_calendar'),
        'add_new_item' => __('Add New Calendar Column', 'omnivo_calendar'),
        'edit_item' => __('Edit Calendar Column', 'omnivo_calendar'),
        'new_item' => __('New Calendar Column', 'omnivo_calendar'),
        'all_items' => __('All Calendar Columns', 'omnivo_calendar'),
        'view_item' => __('View Calendar Column', 'omnivo_calendar'),
        'search_items' => __('Search Calendar Columns', 'omnivo_calendar'),
        'not_found' => __('No calendar columns found', 'omnivo_calendar'),
        'not_found_in_trash' => __('No calendar columns found in Trash', 'omnivo_calendar'),
        'parent_item_colon' => '',
        'menu_name' => __("Calendar columns", 'omnivo_calendar')
    );
    $args = array(
        "labels" => $labels,
        "public" => false,
        "show_ui" => true,
        "capability_type" => "post",
        "menu_position" => 20,
        "hierarchical" => false,
        "rewrite" => true,
        "supports" => array("title", "page-attributes")
    );
    register_post_type("omnivo_weekdays", $args);

}

add_action("init", "omnivo_weekdays_init");

//custom weekdays items list
function omnivo_weekdays_edit_columns($columns)
{
    $columns = array(
        "cb" => "<input type=\"checkbox\" />",
        "title" => __('Day name', 'omnivo_calendar'),
        "date" => __('Date', 'omnivo_calendar')
    );

    return $columns;
}

add_filter("manage_edit-omnivo_weekdays_columns", "omnivo_weekdays_edit_columns");

//autoincrementing order value for new records
function omnivo_weekdays_order_autoincrement_filter($data, $postarr)
{
    if (!function_exists("get_current_screen"))
        return $data;
    $screen = get_current_screen();
    if (!is_null($screen) && $screen->action == "add" && $screen->post_type == "omnivo_weekdays") {
        global $wpdb;
        $menu_order = $wpdb->get_var("SELECT MAX(menu_order)+1 AS menu_order FROM {$wpdb->posts} 
			WHERE 
			post_type='{$screen->post_type}' 
			AND post_status='publish'");
        $menu_order = $menu_order > 0 ? $menu_order : 1;
        $data["menu_order"] = $menu_order;
    }
    return $data;
}

add_filter("wp_insert_post_data", "omnivo_weekdays_order_autoincrement_filter", "99", 2);
?>
