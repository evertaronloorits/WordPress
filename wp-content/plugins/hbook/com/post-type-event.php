<?php
function omnivo_calendar_events_settings()
{
    $omnivo_calendar_events_settings = get_option("omnivo_calendar_events_settings");
    if (!$omnivo_calendar_events_settings) {
        $omnivo_calendar_events_settings = array(
            "slug" => "omnivo_event",
            "label_singular" => "Event",
            "label_plural" => "Events",
        );
        add_option("omnivo_calendar_events_settings", $omnivo_calendar_events_settings);
    }
    return $omnivo_calendar_events_settings;
}

//custom post type - events
function omnivo_calendar_events_init()
{
    global $wpdb;
    $omnivo_calendar_events_settings = omnivo_calendar_events_settings();
    $labels = array(
        'name' => $omnivo_calendar_events_settings['label_plural'],
        'singular_name' => $omnivo_calendar_events_settings['label_singular'],
        'add_new' => _x('Add New', $omnivo_calendar_events_settings["slug"], 'omnivo_calendar'),
        'add_new_item' => sprintf(__('Add New %s', 'omnivo_calendar'), $omnivo_calendar_events_settings['label_singular']),
        'edit_item' => sprintf(__('Edit %s', 'omnivo_calendar'), $omnivo_calendar_events_settings['label_singular']),
        'new_item' => sprintf(__('New %s', 'omnivo_calendar'), $omnivo_calendar_events_settings['label_singular']),
        'all_items' => sprintf(__('All %s', 'omnivo_calendar'), $omnivo_calendar_events_settings['label_plural']),
        'view_item' => sprintf(__('View %s', 'omnivo_calendar'), $omnivo_calendar_events_settings['label_singular']),
        'search_items' => sprintf(__('Search %s', 'omnivo_calendar'), $omnivo_calendar_events_settings['label_singular']),
        'not_found' => sprintf(__('No %s found', 'omnivo_calendar'), strtolower($omnivo_calendar_events_settings['label_plural'])),
        'not_found_in_trash' => sprintf(__('No %s found in Trash', 'omnivo_calendar'), strtolower($omnivo_calendar_events_settings['label_plural'])),
        'parent_item_colon' => '',
        'menu_name' => $omnivo_calendar_events_settings['label_plural']
    );
    $args = array(
        "labels" => $labels,
        "public" => true,
        "show_ui" => true,
        "capability_type" => "post",
        "menu_position" => 20,
        "hierarchical" => false,
        "rewrite" => true,
        "supports" => array("title", "editor", "excerpt", "thumbnail", "page-attributes")
    );
    register_post_type($omnivo_calendar_events_settings["slug"], $args);


    register_taxonomy("events_category", array($omnivo_calendar_events_settings["slug"]), array("label" => "Categories", "singular_label" => "Category", "rewrite" => true, "hierarchical" => true));

    if (array_key_exists("omnivo_calendar_warning", $_GET) && $_GET["omnivo_calendar_warning"] == "available_places") {
        add_action("admin_notices", "omnivo_calendar_warning_available_places");
    }


    if (!get_option("omnivo_calendar_event_hours_table_installed")) {
        //create custom db table
        $query = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "omnivo_calendar_event_hours` (
			`event_hours_id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`event_id` BIGINT( 20 ) NOT NULL ,
			`weekday_id` BIGINT( 20 ) NOT NULL ,
			`start` TIME NOT NULL ,
			`end` TIME NOT NULL,
			`tooltip` text NOT NULL,
			`before_hour_text` text NOT NULL,
			`after_hour_text` text NOT NULL,
			`category` varchar(255) NOT NULL,
			`available_places` int(11) NOT NULL DEFAULT 0,
			KEY `event_id` (`event_id`),
			KEY `weekday_id` (`weekday_id`)
		) ENGINE = MYISAM DEFAULT CHARSET=utf8;";
        $wpdb->query($query);
        update_option("omnivo_calendar_event_hours_table_installed", 1);
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    if (!get_option("omnivo_calendar_event_hours_table_column_available_places")) {
        $query = "SHOW COLUMNS FROM " . $wpdb->prefix . "omnivo_calendar_event_hours LIKE 'available_places'";
        $result = $wpdb->get_results($query);
        if (!$result) {
            $query = "ALTER TABLE " . $wpdb->prefix . "omnivo_calendar_event_hours ADD available_places int(11) NOT NULL DEFAULT 0";
            $wpdb->query($query);
        }
        update_option("omnivo_calendar_event_hours_table_column_available_places", 1);
    }

    if (!get_option("omnivo_calendar_event_hours_table_column_slots_per_user")) {
        $query = "SHOW COLUMNS FROM " . $wpdb->prefix . "omnivo_calendar_event_hours LIKE 'slots_per_user'";
        $result = $wpdb->get_results($query);
        if (!$result) {
            $query = "ALTER TABLE " . $wpdb->prefix . "omnivo_calendar_event_hours ADD slots_per_user int(11) NOT NULL DEFAULT 1";
            $wpdb->query($query);
        }
        update_option("omnivo_calendar_event_hours_table_column_slots_per_user", 1);
    }

    if (!get_option("omnivo_calendar_event_hours_booking_table_installed")) {
        //create custom db table
        $query = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "omnivo_calendar_event_hours_booking` (
			`booking_id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`event_hours_id` BIGINT( 20 ) UNSIGNED NOT NULL,
			`user_id` BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT 0,
			`booking_datetime` DATETIME NOT NULL,
			`validation_code` VARCHAR(32) NOT NULL,
			`guest_id` BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT 0
		) ENGINE = MYISAM DEFAULT CHARSET=utf8;";

        $wpdb->query($query);
        update_option("omnivo_calendar_event_hours_booking_table_installed", 1);
    }

    if (!get_option('omnivo_calendar_event_hours_booking_table_modify_1')) {
        $query = 'SHOW COLUMNS FROM ' . $wpdb->prefix . 'omnivo_calendar_event_hours_booking LIKE "guest_id"';
        $result = $wpdb->get_results($query);
        if (!$result) {
            $query = 'ALTER TABLE ' . $wpdb->prefix . 'omnivo_calendar_event_hours_booking ADD guest_id BIGINT(20) UNSIGNED';
            $wpdb->query($query);
        }

        $query = 'SHOW INDEX FROM ' . $wpdb->prefix . 'omnivo_calendar_event_hours_booking WHERE Key_name="unique_index"';
        $result = $wpdb->get_results($query);
        if ($result) {
            $query = 'ALTER TABLE ' . $wpdb->prefix . 'omnivo_calendar_event_hours_booking DROP INDEX unique_index';
            $wpdb->query($query);
        }
        update_option('omnivo_calendar_event_hours_booking_table_modify_1', 1);
    }

    if (!get_option("omnivo_calendar_guests_table_installed")) {
        //create custom db table
        $query = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "omnivo_calendar_guests` (
			`guest_id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`name` VARCHAR(250),
			`email` VARCHAR(100),
			`phone` VARCHAR(50),
			`message` TEXT
		) ENGINE = MYISAM DEFAULT CHARSET=utf8;";
        $wpdb->query($query);
        update_option("omnivo_calendar_guests_table_installed", 1);
    }

}

add_action("init", "omnivo_calendar_events_init");

//Adds a box to the right column and to the main column on the Events edit screens
function omnivo_calendar_add_events_custom_box()
{
    $omnivo_calendar_events_settings = omnivo_calendar_events_settings();
    add_meta_box(
        "event_hours",
        __("Event hours", 'omnivo_calendar'),
        "omnivo_calendar_inner_events_custom_box_side",
        $omnivo_calendar_events_settings["slug"],
        "normal"
    );
    add_meta_box(
        "event_config",
        __("Options", 'omnivo_calendar'),
        "omnivo_calendar_inner_events_custom_box_main",
        $omnivo_calendar_events_settings["slug"],
        "normal",
        "high"
    );
}

add_action("add_meta_boxes", "omnivo_calendar_add_events_custom_box");
//backwards compatible (before WP 3.0)
//add_action("admin_init", "omnivo_calendar_add_custom_box", 1);

//get event hour details
function omnivo_calendar_get_event_hour_details()
{
    global $wpdb;
    $query = $wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours` AS t1 LEFT JOIN {$wpdb->posts} AS t2 ON t1.weekday_id=t2.ID WHERE t1.event_id='%d' AND t1.event_hours_id='%d'", absint($_POST["post_id"]), absint($_POST["id"]));
    $event_hour = $wpdb->get_row($query);
    $event_hour->start = date("H:i", strtotime($event_hour->start));
    $event_hour->end = date("H:i", strtotime($event_hour->end));
    echo "event_hour_start" . json_encode($event_hour) . "event_hour_end";
    exit();
}

add_action('wp_ajax_get_event_hour_details', 'omnivo_calendar_get_event_hour_details');

function omnivo_calendar_delete_event_bookings()
{
    $result = array(
        'error' => 0,
        'msg' => '',
    );

    global $wpdb;
    $event_id = (isset($_POST["event_id"]) ? sanitize_text_field($_POST["event_id"]) : '');
    $booking_weekday_id = (isset($_POST["booking_weekday_id"]) ? sanitize_text_field($_POST["booking_weekday_id"]) : '');

    $query = "";
    $query_args = array();

    $query .=
        "SELECT booking_id FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours_booking` 
	WHERE event_hours_id IN (
		SELECT event_hours_id 
		FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours`
		WHERE event_id=%d";
    $query_args[] = $event_id;
    if ((int)$booking_weekday_id) {
        $query .= " AND weekday_id=%d";
        $query_args[] = $booking_weekday_id;
    }
    $query .= ")";

    $query = $wpdb->prepare($query, $query_args);
    $bookings_ids = $wpdb->get_col($query);

    for ($i = 0, $max_i = count($bookings_ids); $i < $max_i; $i++) {
        Omnivo_Calendar_DB::deleteBooking($bookings_ids[$i]);
    }

    omnivo_calendar_ajax_response($result);
}

add_action('wp_ajax_delete_event_bookings', 'omnivo_calendar_delete_event_bookings');

// Prints the box content
function omnivo_calendar_inner_events_custom_box_side($post)
{
    global $wpdb;
    //Use nonce for verification
    wp_nonce_field(plugin_basename(__FILE__), "omnivo_calendar_events_noncename");

    //The actual fields for data entry
    $query = "SELECT * FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours` AS t1 LEFT JOIN {$wpdb->posts} AS t2 ON t1.weekday_id=t2.ID WHERE t1.event_id='" . $post->ID . "' ORDER BY t2.menu_order, t1.start, t1.end";
    $event_hours = $wpdb->get_results($query);
    $event_hours_count = count($event_hours);

    //get weekdays
    $query = "SELECT ID, post_title FROM {$wpdb->posts}
			WHERE 
			post_type='omnivo_weekdays'
			AND post_status='publish'
			ORDER BY menu_order";
    $weekdays = $wpdb->get_results($query);

    //get booking details
    $args = array(
        'event_id' => $post->ID,
    );
    $bookings = Omnivo_Calendar_DB::getBookings($args);
    $bookings_array = array();
    for ($i = 0, $max_i = count($bookings); $i < $max_i; $i++) {
        $bookings_array[$bookings[$i]['event_hours_id']][] = $bookings[$i];
    }

    echo '
	<ul id="event_hours_list"' . (!$event_hours_count ? ' style="display: none;"' : '') . '>';
    for ($i = 0; $i < $event_hours_count; $i++) {
        $booking_count = (isset($bookings_array[$event_hours[$i]->event_hours_id]) ? count($bookings_array[$event_hours[$i]->event_hours_id]) : 0);
        //get day by id
        $current_day = get_post($event_hours[$i]->weekday_id);
        echo '<li id="event_hours_' . $event_hours[$i]->event_hours_id . '">' . $current_day->post_title . ' ' . date("H:i", strtotime($event_hours[$i]->start)) . '-' . date("H:i", strtotime($event_hours[$i]->end)) . '<img class="operation_button delete_button delete_event_hour" src="' . plugins_url('../assets/admin/images/delete.png', __FILE__) . '" alt="del" /><img class="operation_button edit_button" src="' . plugins_url('../assets/admin/images/edit.png', __FILE__) . '" alt="edit" /><img class="operation_button edit_hour_event_loader" src="' . plugins_url('../assets/admin/images/ajax-loader.gif', __FILE__) . '" alt="loader" />';
        if ($event_hours[$i]->tooltip != "" || $event_hours[$i]->before_hour_text != "" || $event_hours[$i]->after_hour_text != "" || $event_hours[$i]->category != "" || $event_hours[$i]->available_places != "") {
            echo '<div>';
            if ($event_hours[$i]->tooltip != "")
                echo '<br /><strong>' . __('Tooltip', 'omnivo_calendar') . ':</strong> ' . $event_hours[$i]->tooltip;
            if ($event_hours[$i]->before_hour_text != "")
                echo '<br /><strong>' . __('Description 1', 'omnivo_calendar') . ':</strong> ' . $event_hours[$i]->before_hour_text;
            if ($event_hours[$i]->after_hour_text != "")
                echo '<br /><strong>' . __('Description 2', 'omnivo_calendar') . ':</strong> ' . $event_hours[$i]->after_hour_text;
            if ($event_hours[$i]->available_places != 0)
                echo '<br /><strong>' . __('Available slots', 'omnivo_calendar') . ':</strong> ' . ($booking_count > 0 ? $event_hours[$i]->available_places - $booking_count . '/' : '') . $event_hours[$i]->available_places;
            if ($event_hours[$i]->available_places != 0 && $event_hours[$i]->slots_per_user != 0)
                echo '<br /><strong>' . __('Slots per user', 'omnivo_calendar') . ':</strong> ' . $event_hours[$i]->slots_per_user;
            if ($booking_count) {
                echo '<br><a href="#" class="show_hide_bookings">' . __('Show/Hide booked users', 'omnivo_calendar') . '</a>
						<ul class="booking_list">';
                foreach ($bookings_array[$event_hours[$i]->event_hours_id] as $booking) {
                    echo '<li id="booking_id_' . $booking['booking_id'] . '">';
                    if ($booking['user_id']) {
                        echo sprintf(__('<a href="%s">%s</a> on %s', 'omnivo_calendar'), get_edit_user_link($booking['user_id']), $booking['user_name'], $booking['booking_datetime']);
                    } elseif ($booking['guest_id']) {
                        echo sprintf(__('Guest: %s on %s', 'omnivo_calendar'), $booking['guest_name'], $booking['booking_datetime']);
                    }
                    echo '<img class="operation_button delete_button delete_booking" src="' . plugins_url('../assets/admin/images/delete.png', __FILE__) . '" alt="del" />
							</li>';
                }
                echo '</ul>';
            }
            if ($event_hours[$i]->category != "")
                echo '<br /><strong>' . __('Category', 'omnivo_calendar') . ':</strong> ' . $event_hours[$i]->category;
            echo '</div>';
        }
        echo '</li>';
    }
    echo '
	</ul>
	<table id="event_hours_table">
		<tr>
			<td>
				<label for="weekday_id">' . __('Calendar column', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<select name="weekday_id" id="weekday_id">';
    foreach ($weekdays as $weekday)
        echo '<option value="' . $weekday->ID . '">' . $weekday->post_title . '</option>';
    echo '		</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="start_hour">' . __('Start hour', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<input size="5" maxlength="5" type="text" id="start_hour" name="start_hour" value="" />
				<span class="description">hh:mm</span>
			</td>
		</tr>
		<tr>
			<td>
				<label for="end_hour">' . __('End hour', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<input size="5" maxlength="5" type="text" id="end_hour" name="end_hour" value="" />
				<span class="description">hh:mm</span>
			</td>
		</tr>
		<tr>
			<td>
				<label for="before_hour_text">' . __('Description 1', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<textarea id="before_hour_text" name="before_hour_text"></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<label for="after_hour_text">' . __('Description 2', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<textarea id="after_hour_text" name="after_hour_text"></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<label for="tooltip">' . __('Tooltip', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<textarea id="tooltip" name="tooltip"></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<label for="event_hour_category">' . __('Category', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<input type="text" id="event_hour_category" name="event_hour_category" value="" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="available_places">' . __('Available slots', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<input type="text" id="available_places" name="available_places" value="" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="slots_per_user">' . __('Slots per user', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<input type="text" id="slots_per_user" name="slots_per_user" value="" />
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align: right;">
				<input id="add_event_hours" type="button" class="button button-primary" value="' . __("Add", 'omnivo_calendar') . '" />
				<p><strong style="color:red">*Click "Add" button after inserting the data</strong></p>
				<input type="hidden" id="event_hours_id" name="event_hours_id" value="0"/>
			</td>
		</tr>
	</table>
	<table id="event_bookings_table">
		<tr>
			<td colspan="2">
				<h3>' . __('Delete event bookings', 'omnivo_calendar') . '</h3>
			</td>
		</tr>
		<tr>
			<td>
				<label for="booking_weekday_id">' . __('Calendar column', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<select name="booking_weekday_id" id="booking_weekday_id">
					<option value="all">' . __('All', 'omnivo_calendar') . '</option>';

    foreach ($weekdays as $weekday)
        echo '<option value="' . $weekday->ID . '">' . $weekday->post_title . '</option>';
    echo '		</select>
			</td>
		</tr>
		<tr>	
			<td colspan="2" style="text-align: right;">
				<input type="hidden" id="event_id" name="event_id" value="' . $post->ID . '"/>
				<input id="delete_event_bookings" type="button" class="button" value="' . __("Delete", 'omnivo_calendar') . '" />
			</td>
		</tr>
	</table>
	';
    //Reset Query
    wp_reset_query();
}

function omnivo_calendar_inner_events_custom_box_main($post)
{
    //Use nonce for verification
    wp_nonce_field(plugin_basename(__FILE__), "omnivo_calendar_events_noncename");

    //The actual fields for data entry
    $omnivo_calendar_disable_url = get_post_meta($post->ID, "omnivo_calendar_disable_url", true);
    echo '
	<table>
		<tr>
			<td>
				<label for="color">' . __('Subtitle', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<input class="regular-text" type="text" id="subtitle" name="subtitle" value="' . esc_attr(get_post_meta($post->ID, "omnivo_calendar_subtitle", true)) . '" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="color">' . __('Calendar box background color', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<span class="color_preview" style="background-color: #' . (get_post_meta($post->ID, "omnivo_calendar_color", true) != "" ? esc_attr(get_post_meta($post->ID, "omnivo_calendar_color", true)) : 'transparent') . '"></span>
				<input class="regular-text color" type="text" id="color" name="color" value="' . esc_attr(get_post_meta($post->ID, "omnivo_calendar_color", true)) . '" data-default-color="transparent" />
				<span class="description">' . __('Required when \'Calendar box hover color\' isn\'t empty', 'omnivo_calendar') . '</span>
			</td>
		</tr>
		<tr>
			<td>
				<label for="color">' . __('Calendar box hover background color', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<span class="color_preview" style="background-color: #' . (get_post_meta($post->ID, "omnivo_calendar_hover_color", true) != "" ? esc_attr(get_post_meta($post->ID, "omnivo_calendar_hover_color", true)) : 'transparent') . '"></span>
				<input class="regular-text color" type="text" id="hover_color" name="hover_color" value="' . esc_attr(get_post_meta($post->ID, "omnivo_calendar_hover_color", true)) . '" data-default-color="transparent" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="text_color">' . __('Calendar box text color', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<span class="color_preview" style="background-color: #' . (get_post_meta($post->ID, "omnivo_calendar_text_color", true) != "" ? esc_attr(get_post_meta($post->ID, "omnivo_calendar_text_color", true)) : 'transparent') . '"></span>
				<input class="regular-text color" type="text" id="text_color" name="text_color" value="' . esc_attr(get_post_meta($post->ID, "omnivo_calendar_text_color", true)) . '" data-default-color="transparent" />
				<span class="description">' . __('Required when \'Calendar box hover text color\' isn\'t empty', 'omnivo_calendar') . '</span>
			</td>
		</tr>
		<tr>
			<td>
				<label for="text_color">' . __('Calendar box hover text color', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<span class="color_preview" style="background-color: #' . (get_post_meta($post->ID, "omnivo_calendar_hover_text_color", true) != "" ? esc_attr(get_post_meta($post->ID, "omnivo_calendar_hover_text_color", true)) : 'transparent') . '"></span>
				<input class="regular-text color" type="text" id="hover_text_color" name="hover_text_color" value="' . esc_attr(get_post_meta($post->ID, "omnivo_calendar_hover_text_color", true)) . '" data-default-color="transparent" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="text_color">' . __('Calendar box hours text color', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<span class="color_preview" style="background-color: #' . (get_post_meta($post->ID, "omnivo_calendar_hours_text_color", true) != "" ? esc_attr(get_post_meta($post->ID, "omnivo_calendar_hours_text_color", true)) : 'transparent') . '"></span>
				<input class="regular-text color" type="text" id="hours_text_color" name="hours_text_color" value="' . esc_attr(get_post_meta($post->ID, "omnivo_calendar_hours_text_color", true)) . '" data-default-color="transparent" />
				<span class="description">' . __('Required when \'Calendar box hover hours text color\' isn\'t empty', 'omnivo_calendar') . '</span>
			</td>
		</tr>
		<tr>
			<td>
				<label for="text_color">' . __('Calendar box hover hours text color', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<span class="color_preview" style="background-color: #' . (get_post_meta($post->ID, "omnivo_calendar_hours_hover_text_color", true) != "" ? esc_attr(get_post_meta($post->ID, "omnivo_calendar_hours_hover_text_color", true)) : 'transparent') . '"></span>
				<input class="regular-text color" type="text" id="hours_hover_text_color" name="hours_hover_text_color" value="' . esc_attr(get_post_meta($post->ID, "omnivo_calendar_hours_hover_text_color", true)) . '" data-default-color="transparent" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="color">' . __('Calendar custom URL', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<input class="regular-text" type="text" id="omnivo_calendar_custom_url" name="omnivo_calendar_custom_url" value="' . esc_attr(get_post_meta($post->ID, "omnivo_calendar_custom_url", true)) . '" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="color">' . __('Disable calendar event URL', 'omnivo_calendar') . ':</label>
			</td>
			<td>
				<select name="omnivo_calendar_disable_url">
					<option value="0"' . (!(int)$omnivo_calendar_disable_url ? ' selected="selected"' : '') . '>' . __("No", 'omnivo_calendar') . '</option>
					<option value="1"' . ((int)$omnivo_calendar_disable_url ? ' selected="selected"' : '') . '>' . __("Yes", 'omnivo_calendar') . '</option>
				</select>
			</td>
		</tr>
	</table>';
}

//When the post is saved, saves our custom data
function omnivo_calendar_save_events_postdata($post_id)
{
    global $wpdb;
    $query = "";
    //verify if this is an auto save routine.
    //if it is our form has not been submitted, so we dont want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    //verify this came from the our screen and with proper authorization,
    //because save_post can be triggered at other times
    if (isset($_POST['omnivo_calendar_events_noncename']) && !wp_verify_nonce($_POST['omnivo_calendar_events_noncename'], plugin_basename(__FILE__)) || !isset($_POST['omnivo_calendar_events_noncename']))
        return;

    //Check permissions
    if (!current_user_can('edit_post', $post_id))
        return;

    //OK, we're authenticated: we need to find and save the data

    if (isset($_POST["weekday_ids"])) {
        $hours_count = count($_POST["weekday_ids"]);
        for ($i = 0; $i < $hours_count; $i++) {
            $slots_per_user = absint($_POST["slots_per_user_array"][$i]);
            if ($slots_per_user < 1)
                $slots_per_user = 1;
            if ($slots_per_user > absint($_POST["available_places_array"][$i]))
                $slots_per_user = absint($_POST["available_places_array"][$i]);

            $event_hour_id = (isset($_POST["event_hours_ids"][$i]) ? sanitize_text_field($_POST["event_hours_ids"][$i]) : 0);
            $weekday_id = sanitize_text_field($_POST["weekday_ids"][$i]);
            $start_hour = sanitize_text_field($_POST["start_hours"][$i]);
            $end_hour = sanitize_text_field($_POST["end_hours"][$i]);
            $tooltip = stripslashes(sanitize_text_field($_POST["tooltips"][$i]));
            $before_hour_text = stripslashes(sanitize_text_field($_POST["before_hour_texts"][$i]));
            $after_hour_text = stripslashes(sanitize_text_field($_POST["after_hour_texts"][$i]));
            $event_hours_category = sanitize_text_field($_POST["event_hours_category"][$i]);
            $available_places = sanitize_text_field($_POST["available_places_array"][$i]);

            if (!($event_hour_id > 0)) {
                $query = $wpdb->prepare(
                    "INSERT INTO `" . $wpdb->prefix . "omnivo_calendar_event_hours`(event_id, weekday_id, start, end, tooltip, before_hour_text, after_hour_text, category, available_places, slots_per_user) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                    $post_id, $weekday_id, $start_hour, $end_hour, $tooltip, $before_hour_text, $after_hour_text, $event_hours_category, $available_places, $slots_per_user);
            } else {
                //update only if the available_places is equal or grater than the count of existing bookings
                $booking_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(booking_id) FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours_booking` WHERE event_hours_id=%d", $event_hour_id));
                if ($available_places >= $booking_count) {
                    $query = $wpdb->prepare(
                        "UPDATE `" . $wpdb->prefix . "omnivo_calendar_event_hours` SET weekday_id=%s, start=%s, end=%s, tooltip=%s, before_hour_text=%s, after_hour_text=%s, category=%s, available_places=%s, slots_per_user=%s WHERE event_hours_id=%s",
                        $weekday_id, $start_hour, $end_hour, $tooltip, $before_hour_text, $after_hour_text, $event_hours_category, $available_places, $slots_per_user, $event_hour_id);
                } else {
                    add_filter("redirect_post_location", "omnivo_calendar_set_warning_available_places");
                }
            }

            if (strlen($query))
                $wpdb->query($query);
        }
    }
    //removing data if needed
    if (isset($_POST["delete_event_hours_ids"])) {
        $delete_event_hours_ids_count = count($_POST["delete_event_hours_ids"]);
        if ($delete_event_hours_ids_count) {
            $wpdb->query("DELETE FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours` WHERE event_hours_id IN(" . implode(",", array_map('sanitize_text_field', $_POST["delete_event_hours_ids"])) . ")");
            $wpdb->query("DELETE FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours_booking` WHERE event_hours_id IN(" . implode(",", array_map('sanitize_text_field', $_POST["delete_event_hours_ids"])) . ")");
        }
    }
    if (isset($_POST["delete_booking_ids"])) {
        for ($i = 0, $max_i = count($_POST["delete_booking_ids"]); $i < $max_i; $i++)
            Omnivo_Calendar_DB::deleteBooking(absint($_POST["delete_booking_ids"][$i]));
    }

    //post meta
    update_post_meta($post_id, "omnivo_calendar_subtitle", sanitize_text_field($_POST["subtitle"]));
    update_post_meta($post_id, "omnivo_calendar_color", sanitize_text_field($_POST["color"]));
    update_post_meta($post_id, "omnivo_calendar_hover_color", sanitize_text_field($_POST["hover_color"]));
    update_post_meta($post_id, "omnivo_calendar_text_color", sanitize_text_field($_POST["text_color"]));
    update_post_meta($post_id, "omnivo_calendar_hover_text_color", sanitize_text_field($_POST["hover_text_color"]));
    update_post_meta($post_id, "omnivo_calendar_hours_text_color", sanitize_text_field($_POST["hours_text_color"]));
    update_post_meta($post_id, "omnivo_calendar_hours_hover_text_color", sanitize_text_field($_POST["hours_hover_text_color"]));
    update_post_meta($post_id, "omnivo_calendar_custom_url", sanitize_text_field($_POST["omnivo_calendar_custom_url"]));
    update_post_meta($post_id, "omnivo_calendar_disable_url", sanitize_text_field($_POST["omnivo_calendar_disable_url"]));
}

add_action("save_post", "omnivo_calendar_save_events_postdata");

function omnivo_calendar_delete_events($post_id)
{
    global $wpdb;
    //delete event hour bookings associated with the event
    $query = $wpdb->prepare("DELETE FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours_booking` WHERE event_hours_id IN (
		SELECT event_hours_id FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours` WHERE event_id=%d
	)", $post_id);
    $wpdb->query($query);

    //delete event hours associated with the event
    $query = $wpdb->prepare("DELETE FROM `" . $wpdb->prefix . "omnivo_calendar_event_hours` WHERE event_id=%d", $post_id);
    $wpdb->query($query);
}

add_action("delete_post", "omnivo_calendar_delete_events");

//custom events items list
function events_edit_columns($columns)
{
    $columns = array(
        "cb" => "<input type=\"checkbox\" />",
        "title" => _x('Title', 'post type singular name', 'omnivo_calendar'),
        "events_category" => __('Categories', 'omnivo_calendar'),
        "date" => __('Date', 'omnivo_calendar')
    );

    return $columns;
}

$omnivo_calendar_events_settings = omnivo_calendar_events_settings();
add_filter("manage_edit-" . $omnivo_calendar_events_settings["slug"] . "_columns", "events_edit_columns");
function manage_events_posts_custom_column($column)
{
    global $post;
    switch ($column) {
        case "events_category":
            echo get_the_term_list($post->ID, "events_category", '', ', ', '');
            break;
    }
}

add_action("manage_" . $omnivo_calendar_events_settings["slug"] . "_posts_custom_column", "manage_events_posts_custom_column");

function omnivo_calendar_set_warning_available_places($location)
{
    return add_query_arg("omnivo_calendar_warning", "available_places", $location);
}

function omnivo_calendar_warning_available_places()
{
    echo "
	<div class='notice notice-error'>
		<p>" . __("Error: Available slots value must be equal or greater than the number of existing bookings.", "omnivo_calendar") . "</p>
	</div>";
}

?>
