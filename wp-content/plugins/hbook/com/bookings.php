<?php
class Omnivo_Calendar_Bookings
{
    static $instance;
    public $BookingsList;

    public static function get_instance()
    {
        if(!isset(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_filter('set-screen-option', array(__CLASS__, 'set_screen'), 10, 3);
        add_action('admin_menu', array($this, 'plugin_menu'));
        add_action('wp_loaded', array($this, 'handle_booking_export'));
    }

    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    public function plugin_menu()
    {
        $admin_booking_hook = add_menu_page(__('Calendar Bookings', 'omnivo_calendar'), __('Calendar Bookings', 'omnivo_calendar'), 'manage_options', 'omnivo_calendar_admin_bookings', array($this, 'bookings_page'), '', 20);
        add_action('load-' . $admin_booking_hook, array($this, 'screen_option'));
        add_submenu_page('omnivo_calendar_admin_bookings', __('Export Bookings', 'omnivo_calendar'), __('Export Bookings', 'omnivo_calendar'), 'manage_options', 'omnivo_calendar_admin_bookings_export', array($this, 'bookings_export_page'), '');
    }

    public function screen_option()
    {
        $option = 'per_page';
        $args   = array(
            'label' => 'Bookings',
            'default' => 20,
            'option' => 'bookings_per_page',
        );

        add_screen_option($option, $args);

        $this->BookingsList = new Omnivo_Calendar_Bookings_List();
        $this->BookingsList->process_bulk_action();
    }

    public function bookings_page()
    {
        ?>
        <div class="wrap">
            <h2><?php _e('Bookings list', 'omnivo_calendar'); ?></h2>
            <form method="post">
                <?php
                $this->BookingsList->prepare_items();
                $this->BookingsList->display();
                ?>
            </form>
        </div>
        <?php
    }

    public function bookings_export_page()
    {
        //fetch a list of events
        $omnivo_calendar_events_settings = omnivo_calendar_events_settings();
        $events_list = get_posts(array(
            'posts_per_page' => -1,
            'nopaging' => true,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_type' => $omnivo_calendar_events_settings['slug']
        ));

        //fetch a list of weekdays
        $weekdays_list = get_posts(array(
            'posts_per_page' => -1,
            'nopaging' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_type' => 'omnivo_weekdays'
        ));

        ?>
        <div class="wrap omnivo_calendar_settings_section first">
            <h2><?php _e('Bookings export', 'omnivo_calendar'); ?></h2>
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="omnivo_calendar_bookings_export">
                <div>
                    <table class="omnivo_calendar_table form-table">
                        <tr valign="top">
                            <th>
                                <label for="booking_export_events"><?php _e("Events: ", "omnivo_calendar"); ?></label>
                            </th>
                            <td>
                                <select id="booking_export_events" name="booking_export_events[]" multiple>
                                    <?php
                                    for ($i=0, $max_i=count($events_list); $i < $max_i ; $i++)
                                    {
                                        ?>
                                        <option value="<?php echo esc_attr($events_list[$i]->ID); ?>"><?php echo esc_html($events_list[$i]->post_title); ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th>
                                <label for="booking_export_weekdays"><?php _e("Columns: ", "omnivo_calendar"); ?></label>
                            </th>
                            <td>
                                <select id="booking_export_weekdays" name="booking_export_weekdays[]" multiple>
                                    <?php
                                    for ($i=0, $max_i=count($weekdays_list); $i < $max_i ; $i++)
                                    {
                                        ?>
                                        <option value="<?php echo esc_attr($weekdays_list[$i]->ID); ?>"><?php echo esc_html($weekdays_list[$i]->post_title); ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top" class="no-border">
                            <td colspan="3">
                                <input type="hidden" name="action" value="export-bookings"/>
                                <input type="submit" class="button button-primary" name="bookings_export" id="bookings_export" value="<?php _e('Export', 'omnivo_calendar'); ?>" />

                            </td>
                        </tr>
                        <tr valign="top" class="omnivo_calendar_hide no-border">
                            <td colspan="3">
                                <div id="event_slug_info"></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
        <?php
    }

    public function handle_booking_export()
    {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

        if($action!='export-bookings')
            return;

        $events = filter_input(INPUT_POST, 'booking_export_events', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
        $weekdays = filter_input(INPUT_POST, 'booking_export_weekdays', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);

        $bookings = Omnivo_Calendar_DB::getBookings(array(
            'events_ids' => $events,
            'weekdays_ids' => $weekdays,
        ));

        $exportFileName = 'bookings.csv';

        $data = '';
        $dataArray = array();
        $dataArray[] = __('ID','omnivo_calendar');
        $dataArray[] = __('Event','omnivo_calendar');
        $dataArray[] = __('Weekday','omnivo_calendar');
        $dataArray[] = __('Start','omnivo_calendar');
        $dataArray[] = __('End','omnivo_calendar');
        $dataArray[] = __('Type','omnivo_calendar');
        $dataArray[] = __('Name','omnivo_calendar');
        $dataArray[] = __('E-mail','omnivo_calendar');
        $dataArray[] = __('Phone','omnivo_calendar');
        $dataArray[] = __('Message','omnivo_calendar');

        $data .= implode(chr(9),$dataArray) . "\r\n";

        if($bookings)
        {
            foreach($bookings as $booking)
            {
                $dataArray = array();
                $dataArray[] = $booking['booking_id'];
                $dataArray[] = $booking['event_title'];
                $dataArray[] = $booking['weekday'];
                $dataArray[] = $booking['start'];
                $dataArray[] = $booking['end'];
                if($booking['user_id'])
                {
                    $dataArray[] = sprintf(__('Logged in (%s)', 'omnivo_calendar'), $booking['user_login']);
                    $dataArray[] = $booking['user_name'];
                    $dataArray[] = $booking['user_email'];
                    $dataArray[] = '';	//empty for phone column
                    $dataArray[] = '';	//empty for message column
                }
                else
                {
                    $dataArray[] = __('Guest', 'omnivo_calendar');
                    $dataArray[] = $booking['guest_name'];
                    $dataArray[] = $booking['guest_email'];
                    $dataArray[] = $booking['guest_phone'];
                    $dataArray[] = $booking['guest_message'];
                }

                for($i=0, $max_i=count($dataArray); $i<$max_i; $i++)
                    $dataArray[$i] = preg_replace('/\s+/', ' ', $dataArray[$i]);

                $data .= implode(chr(9),$dataArray)."\r\n";
            }
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        header('Cache-Control: public');
        header('Content-Type: text/csv');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length:' . strlen($data));
        header('Content-Disposition: attachment;filename=' . $exportFileName);
        echo $data;
        die();
    }

}
