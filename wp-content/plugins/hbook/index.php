<?php

/*
 * Plugin Name: Booking Calendar
 * Plugin URI:  https://pluginjungle.com/downloads/booking-calendar/
 * Description: Create or Import events to demonstrate on your WordPress website. Booking Calendar has all required options and it's extremely easy in use.
 * Version:     1.0.2
 * Author:      Omnivo
 * Author URI:  https://pluginjungle.com/author/omnivo/?author_downloads=true
 * License:     GPL-2.0+
 */

define("OMNIVO_CALENDAR_URL", plugin_dir_url(__FILE__));
define("OMNIVO_CALENDAR_PATH", plugin_dir_path(__FILE__));
define("OMNIVO_CALENDAR_VERSION", '1.0.2');


require_once __DIR__ . DIRECTORY_SEPARATOR . 'com' . DIRECTORY_SEPARATOR . 'main.php';

register_activation_hook(__FILE__, 'omnivo_calendar_activate');

function omnivo_calendar_activate()
{
    $omnivo_calendar_events_settings = omnivo_calendar_events_settings();

    $weekdaysQuery = new WP_Query(array(
        'post_type' => 'omnivo_weekdays',
    ));
    if (!$weekdaysQuery->have_posts()) {

        $weekdays = array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        );

        $weekdayIds = array();

        foreach ($weekdays as $i => $weekday) {
            $weekdayIds[] = wp_insert_post(array(
                'post_title' => $weekday,
                'post_type' => 'omnivo_weekdays',
                'post_status' => 'publish',
                'menu_order' => $i
            ));
        }

        $eventsQuery = new WP_Query(array(
            'post_type' => $omnivo_calendar_events_settings["slug"],
        ));
        if (!$eventsQuery->have_posts()) {
            $posts = include OMNIVO_CALENDAR_PATH . DIRECTORY_SEPARATOR . 'default-content.php';

            $eventIds = array();

            foreach ($posts as $post) {
                $post['post_type'] = $omnivo_calendar_events_settings["slug"];
                $post['post_status'] = 'publish';
                $eventIds[] = wp_insert_post($post);
            }

            global $wpdb;

            $sql = "INSERT INTO `" . $wpdb->prefix . "omnivo_calendar_event_hours` (`event_id`, `weekday_id`, `start`, `end`, `tooltip`, `before_hour_text`, `after_hour_text`, `category`, `available_places`) VALUES
({{eventid}}, {{weekdayid}}, '10:00', '13:00', '', 'Beginners', 'Robert Bandana', '', 20),
({{eventid}}, {{weekdayid}}, '14:00', '15:45', '', 'Intermediate', 'Mark Moreau', '', 15),
({{eventid}}, {{weekdayid}}, '17:00', '18:30', '', 'Advanced', 'Kevin Nomak', '', 7),
({{eventid}}, {{weekdayid}}, '20:00', '23:00', '', 'Pros', 'Kevin Nomak', '', 3);";

            $i = 0;

            foreach($eventIds as $eventId) {
                $eventSql = str_replace('{{eventid}}', $eventId, $sql);

                $weekdaySql = str_replace('{{weekdayid}}', $weekdayIds[$i], $eventSql);
                $wpdb->query($weekdaySql);
                $i++;
            }
        }
    }
}
