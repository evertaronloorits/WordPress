<?php
function omnivo_calendar_admin_notices(){
    if(omnivo_calendar_is_licensed()) return;
    ?>
    <div class="omnivo_calendar_free_notice">
        <div class="omnivo_calendar_free_notice_button_container">
            <div class="omnivo_calendar_free_notice_button_border">
                <a class="omnivo-pro-button" href="https://pluginjungle.com/downloads/booking-calendar/" target="_blank">Purchase Pro Version</a>
            </div>
        </div>
        <div class="omnivo_calendar_free_notice_description">
            <strong>Omnivo Booking Calendar</strong> You are using limited version of this plugin, purchase full version for more features
        </div>
    </div>
    <?php
}
add_action('admin_notices', 'omnivo_calendar_admin_notices');

//translation
function omnivo_calendar_load_textdomain()
{
    load_plugin_textdomain("omnivo_caendar", false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'omnivo_calendar_load_textdomain');
function omnivo_calendar_init_sp_bookings()
{
    if(!class_exists('WP_List_Table'))
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    require_once(__DIR__.DIRECTORY_SEPARATOR.'bookings-list.php');
    require_once(__DIR__.DIRECTORY_SEPARATOR.'bookings.php');
    Omnivo_Calendar_Bookings::get_instance();
}
add_action('plugins_loaded', 'omnivo_calendar_init_sp_bookings');
require_once(__DIR__.DIRECTORY_SEPARATOR.'database.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'class-post.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'class-event.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'class-event-hour.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'class-weekday.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'post-type-weekdays.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'post-type-event.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'widgets.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'shortcodes.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'shortcode-calendar.php');
//Template fallback
add_action('template_redirect', 'omnivo_calendar_redirect', 99);

if(function_exists("register_sidebar"))
{
    register_sidebar(array(
        "id" => "sidebar-event",
        "name" => "Sidebar Event",
        'before_widget' => '<div id="%1$s" class="widget %2$s omnivo_calendar_sidebar_box omnivo_calendar_clearfix">',
        'after_widget' => '</div>',
        'before_title' => '<h5 class="box_header">',
        'after_title' => '</h5>'
    ));
}

function omnivo_calendar_init()
{
    add_theme_support("post-thumbnails");
    add_image_size("event-post-thumb", 630, 300, true);
    add_image_size("event-post-thumb-box", 300, 240, true);

    //phpMailer
    add_action('phpmailer_init', 'omnivo_calendar_phpmailer_init');

    $omnivo_calendar_contact_form_options = get_option("omnivo_calendar_contact_form_options");
    if(!$omnivo_calendar_contact_form_options)
    {
        $omnivo_calendar_contact_form_options = array(
            "admin_name" => get_option("admin_email"),
            "admin_email" => get_option("admin_email"),
            "mail_debug" => "not",
            "smtp_host" => "",
            "smtp_username" => "",
            "smtp_password" => "",
            "smtp_port" => "",
            "smtp_secure" => "",
            "email_subject_client" => __("You have been booked for event {event_title}", 'omnivo_calendar'),
            "template_client" => "<html>
<head>
</head>
<body>
	<div>Thank you for using our services.</div>
	<div>Booking details</div>
	<div><b>User</b>: {user_name}</div>
	<div><b>Mail</b>: {user_email}</div>
	<div><b>Date</b>: {booking_datetime}</div>
	<div>Event details</div>
	<div><b>Event</b>: {event_title}</div>
	<div><b>Day</b>: {column_title}</div>
	<div><b>Time</b>: {event_start} - {event_end}</div>
	<div><b>Description 1</b>: {event_description_1}</div>
	<div><b>Description 2</b>: {event_description_2}</div>
	<div><b>Slots number</b>: {slots_number}</div>
	<div>{cancel_booking}</div>
</body>
</html>",
            "email_subject_admin" => __("New booking for event: {event_title}", 'omnivo_calendar'),
            "template_admin" => "<html>
<head>
</head>
<body>
	<div>New client</div>
	<div>Booking details</div>
	<div><b>User</b>: {user_name}</div>
	<div><b>Mail</b>: {user_email}</div>
	<div><b>Date</b>: {booking_datetime}</div>
	<div>Event details</div>
	<div><b>Event</b>: {event_title}</div>
	<div><b>Day</b>: {column_title}</div>
	<div><b>Time</b>: {event_start} - {event_end}</div>
	<div><b>Description 1</b>: {event_description_1}</div>
	<div><b>Description 2</b>: {event_description_2}</div>
	<div><b>Slots number</b>: {slots_number}</div>
</body>
</html>",
        );
        add_option('omnivo_calendar_contact_form_options', $omnivo_calendar_contact_form_options);
    }

    $omnivo_calendar_google_calendar_options = get_option("omnivo_calendar_google_calendar_options");
    if(!$omnivo_calendar_google_calendar_options)
    {
        $omnivo_calendar_google_calendar_options = array(
            "calendar_id" => "",
            "calendar_settings" => "",
        );
        add_option('omnivo_calendar_google_calendar_options', $omnivo_calendar_google_calendar_options);
    }

    if(!isset($omnivo_calendar_contact_form_options['mail_debug']))
    {
        $omnivo_calendar_contact_form_options['mail_debug'] = 'no';
        update_option('omnivo_calendar_contact_form_options', $omnivo_calendar_contact_form_options);
    }
}
add_action('init', 'omnivo_calendar_init');

function omnivo_calendar_cancel_booking()
{
    if(!(array_key_exists('action', $_GET) && $_GET['action']==='omnivo_calendar_cancel_booking'))
        return;

    $booking_id = array_key_exists('booking_id', $_GET) ? absint($_GET['booking_id']) : 0;
    $validation_code = array_key_exists('validation_code', $_GET) ? sanitize_text_field($_GET['validation_code']) : '';
    $bookings_ids = array_key_exists('bookings_ids', $_GET) ? explode(',', sanitize_text_field($_GET['bookings_ids'])) : array();
    $validation_codes = array_key_exists('validation_codes', $_GET) ? explode(',', sanitize_text_field($_GET['validation_codes'])) : array();

    $bookings_ids[] = $booking_id;
    $validation_codes[] = $validation_code;

    //get all booking details
    $bookings = array();
    if(count($bookings_ids) && count($bookings_ids)==count($validation_codes))
    {
        for($i=0, $max_i = count($bookings_ids); $i<$max_i; $i++)
        {
            if($bookings_ids[$i]>0 && strlen($validation_codes[$i])==32)
            {
                $result = Omnivo_Calendar_DB::getBookings(array(
                    'booking_id' => $bookings_ids[$i],
                    'validation_code' => $validation_codes[$i],
                ));
                if(isset($result[0]['booking_id']) && $result[0]['booking_id']==$bookings_ids[$i])
                    $bookings[] = $result[0];
                else
                    echo '<b>' . sprintf(__('Error: Booking #%d does not exist or validation code is incorrect.', 'omnivo_calendar'), $bookings_ids[$i]) . '</b><br>';
            }
        }
    }
    if(!count($bookings))
        return;

    //delete bookings and display their details
    foreach($bookings as $booking)
    {
        //delete booking
        Omnivo_Calendar_DB::deleteBooking($booking['booking_id']);
        //display booking information
        echo '<b>' . sprintf(__('Booking #%d (%s) deleted', 'omnivo_calendar'), $booking['booking_id'], $booking['booking_datetime']) . '</b><br>';
        echo sprintf(__('Title: %s', 'omnivo_calendar'), $booking['event_title']) . '<br>';
        echo sprintf(__('Time: %s', 'omnivo_calendar'), $booking['start'] . '-' . $booking['end']) . '<br>';
        echo sprintf(__('Column: %s', 'omnivo_calendar'), $booking['weekday']) . '<br>';
        if($booking['event_description_1'])
            echo sprintf(__('Description 1: %s', 'omnivo_calendar'), $booking['event_description_1']) . '<br>';
        if($booking['event_description_2'])
            echo sprintf(__('Description 2: %s', 'omnivo_calendar'), $booking['event_description_2']) . '<br>';
        echo '<br>';
    }

    //send email to administrator
    $omnivo_calendar_contact_form_options = omnivo_calendar_stripslashes_deep(get_option("omnivo_calendar_contact_form_options"));
    $admin_name = $omnivo_calendar_contact_form_options['admin_name'];
    $admin_email = $omnivo_calendar_contact_form_options['admin_email'];
    $client_name = '';
    $client_email = '';
    $client_phone = '';
    if($bookings[0]['user_id']>0)
    {
        $client_name = $bookings[0]['user_name'];
        $client_email = $bookings[0]['user_email'];
    }
    else
    {
        $client_name = $bookings[0]['guest_name'];
        $client_email = $bookings[0]['guest_email'];
        $client_phone = $bookings[0]['guest_phone'];
    }

    $headers = array();
    $headers[] = 'Reply-To: ' . $client_name . ' <' . $client_email . '>' . "\r\n";
    $headers[] = 'From: ' . $admin_name . ' <' . $admin_email . '>' . "\r\n";
    $headers[] = 'Content-type: text/html';
    $subject = __('Bookings canceled', 'omnivo_calendar');
    $body = '';

    $body .= '<h3>' . __('Client details', 'omnivo_calendar') . '</h3>';
    $body .= sprintf(__('Name: %s', 'omnivo_calendar'), $client_name) . '<br>';
    $body .= sprintf(__('Email: %s', 'omnivo_calendar'), $client_email) . '<br>';
    if($client_phone)
        $body .= sprintf(__('Phone: %s', 'omnivo_calendar'), $client_phone) . '<br>';
    $body .= '<br>';
    $body .= '<h3>' . __('Canceled Bookings', 'omnivo_calendar') . '</h3>';
    foreach($bookings as $booking)
    {
        $body .= sprintf(__('Booking: #%d (%s)', 'omnivo_calendar'), $booking['booking_id'], $booking['booking_datetime']) . '<br>';
        $body .= sprintf(__('Title: %s', 'omnivo_calendar'), $booking['event_title']) . '<br>';
        $body .= sprintf(__('Time: %s', 'omnivo_calendar'), $booking['start'] . '-' . $booking['end']) . '<br>';
        $body .= sprintf(__('Column: %s', 'omnivo_calendar'), $booking['weekday']) . '<br>';
        if($booking['event_description_1'])
            $body .= sprintf(__('Description 1: %s', 'omnivo_calendar'), $booking['event_description_1']) . '<br>';
        if($booking['event_description_2'])
            $body .= sprintf(__('Description 2: %s', 'omnivo_calendar'), $booking['event_description_2']) . '<br>';
        $body .= '<br>';
    }

    wp_mail($admin_name . ' <' . $admin_email . '>', $subject, $body, $headers);
    die;
}
add_action('init', 'omnivo_calendar_cancel_booking');

function omnivo_calendar_phpmailer_init(PHPMailer $mail)
{
    $omnivo_calendar_contact_form_options = omnivo_calendar_stripslashes_deep(get_option("omnivo_calendar_contact_form_options"));
    $mail->CharSet='UTF-8';
    $smtp = (isset($omnivo_calendar_contact_form_options["smtp_host"]) ? $omnivo_calendar_contact_form_options["smtp_host"] : null);
    if(!empty($smtp))
    {
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
//		$mail->SMTPDebug = 2;
        $mail->Host = $omnivo_calendar_contact_form_options["smtp_host"];
        $mail->Username = $omnivo_calendar_contact_form_options["smtp_username"];
        $mail->Password = $omnivo_calendar_contact_form_options["smtp_password"];
        if((int)$omnivo_calendar_contact_form_options["smtp_port"]>0)
            $mail->Port = (int)$omnivo_calendar_contact_form_options["smtp_port"];
        $mail->SMTPSecure = $omnivo_calendar_contact_form_options["smtp_secure"];
    }
}

function omnivo_calendar_redirect()
{
    global $wp;
    $omnivo_calendar_events_settings = omnivo_calendar_events_settings();
    $plugindir = dirname( __FILE__ );

    //A Specific Custom Post Type
    if (isset($wp->query_vars["post_type"]) && $wp->query_vars["post_type"] == $omnivo_calendar_events_settings["slug"])
    {
        $templatefilename = 'event-template.php';

        if(file_exists(STYLESHEETPATH . '/' . $templatefilename))
        {
            $return_template = STYLESHEETPATH . '/' . $templatefilename;
        }
        elseif(file_exists(TEMPLATEPATH . '/' . $templatefilename))
        {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        }
        else
        {
            $return_template = $plugindir . '/' . $templatefilename;
        }
        do_omnivo_calendar_redirect($return_template);

        //A Custom Taxonomy Page
    }
}

function do_omnivo_calendar_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}

//register event post thumbnail

function omnivo_calendar_image_sizes($sizes)
{
    $addsizes = array(
        "event-post-thumb" => __("Event post thumbnail", 'omnivo_calendar'),
        "event-post-thumb-box" => __("Event post box thumbnail", 'omnivo_calendar')
    );
    $newsizes = array_merge($sizes, $addsizes);
    return $newsizes;
}
add_filter("image_size_names_choose", "omnivo_calendar_image_sizes");

//documentation link
function omnivo_calendar_documentation_link($links)
{
    $documentation_link = '<a href="' . plugins_url('documentation/index.html', __FILE__) . '" title="Documentation">Documentation</a>';
    array_unshift($links, $documentation_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'omnivo_calendar_documentation_link');

//settings link
function omnivo_calendar_settings_link($links)
{
    $settings_link = '<a href="admin.php?page=omnivo_calendar_admin" title="Settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'omnivo_calendar_settings_link');

function omnivo_calendar_enqueue_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script("jquery-qtip2", plugins_url('../assets/js/jquery.qtip.min.js', __FILE__), array('jquery'), false, true);

    wp_enqueue_script("jquery-ba-bqq", plugins_url('../assets/js/jquery.ba-bbq.min.js', __FILE__), array('jquery'), false, true);
    wp_enqueue_script("jquery-carouFredSel", plugins_url('../assets/js/jquery.carouFredSel-6.2.1.js', __FILE__), array('jquery'), false, true);
    if(function_exists("is_customize_preview") && !is_customize_preview())
        wp_enqueue_script('omnivo_calendar_main', plugins_url('../assets/js/calendar.js', __FILE__), array("jquery"), false, true);
    wp_enqueue_style('omnivo_calendar_sf_style', plugins_url('../assets/style/superfish.css', __FILE__));
    wp_enqueue_style('omnivo_calendar_gtip2_style', plugins_url('../assets/style/jquery.qtip.css', __FILE__));
    wp_enqueue_style('omnivo_calendar_style', plugins_url('../assets/style/style.css', __FILE__));
    wp_enqueue_style('omnivo_calendar_event_template', plugins_url('../assets/style/event_template.css', __FILE__));
    wp_enqueue_style('omnivo_calendar_responsive_style', plugins_url('../assets/style/responsive.css', __FILE__));
    wp_enqueue_style('omnivo_calendar_font_lato', '//fonts.googleapis.com/css?family=Lato:400,700');

    $data = array();
    $data["ajaxurl"] = admin_url("admin-ajax.php");

    //pass data to javascript
    $params = array(
        'l10n_print_after' => 'omnivo_calendar_config = ' . json_encode($data) . ';'
    );
    wp_localize_script("omnivo_calendar_main", "omnivo_calendar_config", $params);
}
add_action('wp_enqueue_scripts', 'omnivo_calendar_enqueue_scripts');

//admin
if(is_admin())
{
    function omnivo_calendar_admin_menu()
    {
        $page = add_menu_page(__('Calendar', 'omnivo_calendar'), __('Calendar', 'omnivo_calendar'), 'manage_options', 'omnivo_calendar_admin', 'omnivo_calendar_admin_page', '', 20);
        $shortcode_generator_page = add_submenu_page('omnivo_calendar_admin', __('Shortcode Generator', 'omnivo_calendar'), __('Shortcode Generator', 'omnivo_calendar'), 'manage_options', 'omnivo_calendar_admin', 'omnivo_calendar_admin_page', '', 20);
        $event_config_page = add_submenu_page('omnivo_calendar_admin', __('Event Post Type', 'omnivo_calendar'), __('Event Post Type', 'omnivo_calendar'), 'manage_options', 'omnivo_calendar_admin_page_event_post_type', 'omnivo_calendar_admin_page_event_post_type');
        $email_config_page = add_submenu_page('omnivo_calendar_admin', __('Email config', 'omnivo_calendar'), __('Email config', 'omnivo_calendar'), 'manage_options', 'omnivo_calendar_admin_page_email_config', 'omnivo_calendar_admin_page_email_config');
        $google_calendar_page = add_submenu_page('omnivo_calendar_admin', __('Google Calendar', 'omnivo_calendar'), __('Google Calendar', 'omnivo_calendar'), 'manage_options', 'omnivo_calendar_admin_page_google_calendar', 'omnivo_calendar_admin_page_google_calendar');

        add_action('admin_enqueue_scripts', 'omnivo_calendar_admin_enqueue_scripts');
    }
    add_action('admin_menu', 'omnivo_calendar_admin_menu');

    function omnivo_calendar_admin_init()
    {
        wp_register_script('omnivo_calendar-colorpicker',OMNIVO_CALENDAR_URL.'assets/admin/js/colorpicker.js');
        wp_register_script('omnivo_calendar-clipboard', OMNIVO_CALENDAR_URL.'assets/admin/js/clipboard.min.js', array("jquery"));
        wp_register_script('omnivo_calendar-admin', OMNIVO_CALENDAR_URL.'assets/admin/js/omnivo_calendar_admin.js', array("jquery", "omnivo_calendar-clipboard"));
        wp_register_style('omnivo_calendar-colorpicker', OMNIVO_CALENDAR_URL.'assets/admin/style/colorpicker.css');
        wp_register_style('omnivo_calendar-admin', OMNIVO_CALENDAR_URL.'assets/admin/style/style.css');
    }
    add_action('admin_init', 'omnivo_calendar_admin_init');

    function omnivo_calendar_admin_enqueue_scripts($hook)
    {
        $admin_pages = array('post.php', 'post-new.php', 'widgets.php');

        if(strpos($hook, 'page_omnivo_calendar_admin')!=FALSE || in_array($hook, $admin_pages))
        {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_script('omnivo_calendar-colorpicker');
            wp_enqueue_script('omnivo_calendar-clipboard');
            wp_enqueue_script('omnivo_calendar-admin');
            wp_enqueue_style('omnivo_calendar-colorpicker');
            $data = array(
                'img_url' => OMNIVO_CALENDAR_URL."assets/admin/images/",
                'js_url' => OMNIVO_CALENDAR_URL."assets/admin/js/",
                'delete_event_booking_confirmation' => __('Please confirm that you want to delete event bookings.', 'omnivo_calendar'),
                'booking_popup_message' => BOOKING_POPUP_MESSAGE,
                'booking_popup_thank_you_message' => BOOKING_POPUP_THANK_YOU_MESSAGE,
            );
            //pass data to javascript
            $params = array(
                'l10n_print_after' => 'config = ' . json_encode($data) . ';'
            );
            wp_localize_script('omnivo_calendar-admin', 'config', $params);
        }

        wp_enqueue_style('omnivo_calendar-admin');
    }

    function omnivo_calendar_admin_print_scripts()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('omnivo_calendar-colorpicker');
        wp_enqueue_script('omnivo_calendar-clipboard');
        wp_enqueue_script('omnivo_calendar-admin');
        wp_enqueue_style('omnivo_calendar-colorpicker');
        $data = array(
            'img_url' => plugins_url("assets/admin/images/", __FILE__),
            'js_url' => plugins_url("assets/admin/js/", __FILE__),
            'booking_popup_message' => BOOKING_POPUP_MESSAGE,
            'booking_popup_thank_you_message' => BOOKING_POPUP_THANK_YOU_MESSAGE,
        );
        //pass data to javascript
        $params = array(
            'l10n_print_after' => 'config = ' . json_encode($data) . ';'
        );
        wp_localize_script("omnivo_calendar-admin", "config", $params);
    }

    function omnivo_calendar_admin_print_scripts_all()
    {
        wp_enqueue_style('omnivo_calendar-admin');
    }

    function omnivo_calendar_ajax_get_font_subsets()
    {
        if($_POST["font"]!="")
        {
            $subsets = '';
            $fontExplode = explode(":", sanitize_text_field($_POST["font"]));
            //get google fonts
            $fontsArray = omnivo_calendar_get_google_fonts();
            $fontsCount = count((array)$fontsArray->items);
            for($i=0; $i<$fontsCount; $i++)
            {
                if($fontsArray->items[$i]->family==$fontExplode[0])
                {
                    for($j=0, $max_j=count((array)$fontsArray->items[$i]->subsets); $j<$max_j; $j++)
                    {
                        $subsets .= '<option value="' . $fontsArray->items[$i]->subsets[$j] . '">' . $fontsArray->items[$i]->subsets[$j] . '</option>';
                    }
                    break;
                }
            }
            echo "omnivo_calendar_start" . $subsets . "omnivo_calendar_end";
        }
        exit();
    }
    add_action('wp_ajax_omnivo_calendar_get_font_subsets', 'omnivo_calendar_ajax_get_font_subsets');

    function omnivo_calendar_ajax_event_hour_details()
    {
        $result = array();
        $result['msg'] = '';
        $result['error'] = 0;

        if(!(isset($_POST['event_hour_id']) && $event_hour_id=$_POST['event_hour_id']))
        {
            $result["msg"] = __("<h2>Invalid event hour</h2>
<p>Selected event hour doesn't exist.<br>Please select different event.</p>", "omnivo_calendar");
            $result["error"] = 1;
            omnivo_calendar_ajax_response($result);
        }

        $redirect_url = (isset($_POST['redirect_url']) ? sanitize_text_field($_POST['redirect_url']) : '');
        $allow_user_booking = (isset($_POST['atts']['allow_user_booking']) ? sanitize_text_field($_POST['atts']['allow_user_booking']) : 'yes');
        $booking_popup_message_template = (isset($_POST['atts']['booking_popup_message']) ? sanitize_text_field($_POST['atts']['booking_popup_message']) : '');
        $booking_popup_message_template = omnivo_calendar_stripslashes_deep($booking_popup_message_template);
        $booking_popup_label = (isset($_POST['atts']['booking_popup_label']) ? sanitize_text_field($_POST['atts']['booking_popup_label']) : '');
        $login_popup_label = (isset($_POST['atts']['login_popup_label']) ? sanitize_text_field($_POST['atts']['login_popup_label']) : '');
        $cancel_popup_label = (isset($_POST['atts']['cancel_popup_label']) ? sanitize_text_field($_POST['atts']['cancel_popup_label']) : '');
        $continue_popup_label = (isset($_POST['atts']['continue_popup_label']) ? sanitize_text_field($_POST['atts']['continue_popup_label']) : '');

        $user_id = ($allow_user_booking=='yes' ? get_current_user_id() : 0);

        $event_hour_details = Omnivo_Calendar_DB::getEventHours(array(
            'event_hours_id' => $event_hour_id,
            'user_id' => $user_id,
        ));

        $event_hour_details = $event_hour_details[0];

        if(!$event_hour_details)
        {
            $result['msg'] = __('<h2>Invalid event hour</h2><p>Selected event hour doesn\'t exist.<br>Please select different event.</p>', 'omnivo_calendar');
            $result['error'] = 1;
            omnivo_calendar_ajax_response($result);
        }


        $time_format = (isset($_POST['atts']['time_format']) ? sanitize_text_field($_POST['atts']['time_format']) : 'H.i');
        $booking_form_config = array(
            'allow_user_booking' => $allow_user_booking,
            'allow_guest_booking' => (isset($_POST['atts']['allow_guest_booking']) ? sanitize_text_field($_POST['atts']['allow_guest_booking']) : 'no'),
            'default_booking_view' => (isset($_POST['atts']['default_booking_view']) ? sanitize_text_field($_POST['atts']['default_booking_view']) : 'user'),
            'show_guest_name_field' => (isset($_POST['atts']['show_guest_name_field']) ? sanitize_text_field($_POST['atts']['show_guest_name_field']) : 'no'),
            'guest_name_field_required' => (isset($_POST['atts']['guest_name_field_required']) ? sanitize_text_field($_POST['atts']['guest_name_field_required']) : 'no'),
            'show_guest_phone_field' => (isset($_POST['atts']['show_guest_phone_field']) ? sanitize_text_field($_POST['atts']['show_guest_phone_field']) : 'no'),
            'guest_phone_field_required' => (isset($_POST['atts']['guest_phone_field_required']) ? sanitize_text_field($_POST['atts']['guest_phone_field_required']) : 'no'),
            'show_guest_message_field' => (isset($_POST['atts']['show_guest_message_field']) ? sanitize_text_field($_POST['atts']['show_guest_message_field']) : 'no'),
            'guest_message_field_required' => (isset($_POST['atts']['guest_message_field_required']) ? sanitize_text_field($_POST['atts']['guest_message_field_required']) : 'no'),
            'terms_checkbox' => (isset($_POST['atts']['terms_checkbox']) ? sanitize_text_field($_POST['atts']['terms_checkbox']) : 'no'),
            'terms_message' => (isset($_POST['atts']['terms_message']) ? stripslashes(sanitize_text_field($_POST['atts']['terms_message'])) : 'Please accept terms and conditions'),
            'current_user_booking_count' => $event_hour_details->current_user_booking_count,
            'slots_per_user' => $event_hour_details->slots_per_user,
            'remaining_places' => $event_hour_details->available_places-$event_hour_details->booking_count,
        );

        $btn_book_config = array(
            'booking_label' => $booking_popup_label,
            'login_label' => $login_popup_label,
            'redirect_url' => $redirect_url,
            'allow_user_booking' => $booking_form_config['allow_user_booking'],
            'allow_guest_booking' => $booking_form_config['allow_guest_booking'],
            'default_booking_view' => $booking_form_config['default_booking_view'],
        );

        //insert values into the template
        $result['msg'] = $booking_popup_message_template;
        $result['msg'] = str_replace('{event_title}', $event_hour_details->event_title, $result['msg']);
        $result['msg'] = str_replace('{column_title}', $event_hour_details->column_title, $result['msg']);
        $result['msg'] = str_replace('{event_start}', date($time_format, strtotime($event_hour_details->start)), $result['msg']);
        $result['msg'] = str_replace('{event_end}', date($time_format, strtotime($event_hour_details->end)), $result['msg']);
        $result['msg'] = str_replace('{event_description_1}', $event_hour_details->description_1, $result['msg']);
        $result['msg'] = str_replace('{event_description_2}', $event_hour_details->description_2, $result['msg']);
        $result['msg'] = str_replace('{booking_form}', omnivo_calendar_booking_form($booking_form_config), $result['msg']);
        $result['msg'] = str_replace('{omnivo_calendar_btn_book}', omnivo_calendar_btn_book($btn_book_config), $result['msg']);
        $result['msg'] = str_replace('{omnivo_calendar_btn_cancel}', omnivo_calendar_btn_cancel($cancel_popup_label), $result['msg']);
        $result['msg'] = str_replace('{omnivo_calendar_btn_continue}', omnivo_calendar_btn_continue($continue_popup_label), $result['msg']);

        if(!$user_id && $booking_form_config['allow_guest_booking']=='yes')
        {
            //show additional label 'Continue as guest.'
            $guestOptionHidden = false;
            if($booking_form_config['default_booking_view']=='guest')
                $guestOptionHidden = true;

            $result['msg'] .= sprintf(__('<p class="omnivo_calendar_guest_option ' . ($guestOptionHidden ? 'omnivo_calendar_hide' : '') . '">Don\'t have an account? <a href="%s">Continue as guest</a></p>', 'omnivo_calendar'), '#');
        }


        if(!$user_id && $booking_form_config['allow_user_booking']=='yes')
        {
            //show additional label 'Got an account? Login'
            $loginOptionHidden = false;
            if($booking_form_config['default_booking_view']=='user')
                $loginOptionHidden = true;

            $result['msg'] .= sprintf(__('<p class="omnivo_calendar_login_option ' . ($loginOptionHidden ? 'omnivo_calendar_hide' : '') . '">Got an account? <a href="%s">Login</a></p>', 'omnivo_calendar'), wp_login_url($redirect_url));

        }

        omnivo_calendar_ajax_response($result);
    }

    add_action('wp_ajax_omnivo_calendar_ajax_event_hour_details', 'omnivo_calendar_ajax_event_hour_details');
    add_action('wp_ajax_nopriv_omnivo_calendar_ajax_event_hour_details', 'omnivo_calendar_ajax_event_hour_details');

    function omnivo_calendar_ajax_event_hour_booking()
    {
        $result = array();
        $result['msg'] = '';
        $result['error'] = 0;
        $result['booking_id'] = 0;
        $result['event_hour_active'] = 1;

        $allow_user_booking = (isset($_POST['atts']['allow_user_booking']) ? sanitize_text_field($_POST['atts']['allow_user_booking']) : 'yes');
        $user_id = ($allow_user_booking=='yes' ? get_current_user_id() : 0);
        $terms_checkbox_required = (isset($_POST['atts']['terms_checkbox']) ? sanitize_text_field($_POST['atts']['terms_checkbox']) : 'no');
        $terms_checkbox = (isset($_POST["terms_checkbox"]) ? filter_var($_POST["terms_checkbox"], FILTER_VALIDATE_BOOLEAN) : false);

        if(!$user_id && $terms_checkbox_required=='yes' && !$terms_checkbox)
        {
            $result['event_hour_active'] = 0;
            $result['msg'] = __('<h2>Booking couldn\'t be made</h2>
<p>Please accept terms and conditions checkbox.</p>', 'omnivo_calendar');
            $result['error'] = 1;
            omnivo_calendar_ajax_response($result);
        }

        $event_hour_id = (int)$_POST['event_hour_id'];
        $guest_id = 0;
        $time_format = (isset($_POST['atts']['time_format']) ? $_POST['atts']['time_format'] : 'H.i');
        $guest_config = array(
            'allow_guest_booking' => (isset($_POST['atts']['allow_guest_booking']) ? sanitize_text_field($_POST['atts']['allow_guest_booking']) : 'no'),
            'show_guest_name_field' => (isset($_POST['atts']['show_guest_name_field']) ? sanitize_text_field($_POST['atts']['show_guest_name_field']) : 'no'),
            'guest_name_field_required' => (isset($_POST['atts']['guest_name_field_required']) ? sanitize_text_field($_POST['atts']['guest_name_field_required']) : 'no'),
            'show_guest_phone_field' => (isset($_POST['atts']['show_guest_phone_field']) ? sanitize_text_field($_POST['atts']['show_guest_phone_field']) : 'no'),
            'guest_phone_field_required' => (isset($_POST['atts']['guest_phone_field_required']) ? sanitize_text_field($_POST['atts']['guest_phone_field_required']) : 'no'),
            'show_guest_message_field' => (isset($_POST['atts']['show_guest_message_field']) ? sanitize_text_field($_POST['atts']['show_guest_message_field']) : 'no'),
            'guest_message_field_required' => (isset($_POST['atts']['guest_message_field_required']) ? sanitize_text_field($_POST['atts']['guest_message_field_required']) : 'no'),
        );

        $slots_number = (isset($_POST["slots_number"]) ? sanitize_text_field($_POST["slots_number"]) : 1);
        $guest_name = (isset($_POST["guest_name"]) ? stripslashes(sanitize_text_field($_POST["guest_name"])) : '');
        $guest_email = (isset($_POST["guest_email"]) ? sanitize_email($_POST["guest_email"]) : '');
        $guest_phone = (isset($_POST["guest_phone"]) ? stripslashes(sanitize_text_field($_POST["guest_phone"])) : '');
        $guest_message = (isset($_POST["guest_message"]) ? stripslashes(sanitize_textarea_field($_POST["guest_message"])) : '');

        $available_slots_singular_label = (isset($_POST["atts"]["available_slots_singular_label"]) ? sanitize_text_field($_POST["atts"]["available_slots_singular_label"]) : "{number_available}/{number_total} slot available");
        $available_slots_plural_label = (isset($_POST["atts"]["available_slots_plural_label"]) ? sanitize_text_field($_POST["atts"]["available_slots_plural_label"]) : "{number_available}/{number_total} slots available");
        $continue_popup_label = (isset($_POST["atts"]["continue_popup_label"]) ? sanitize_text_field($_POST["atts"]["continue_popup_label"]) : "");
        $booking_popup_thank_you_message_template = (isset($_POST["atts"]["booking_popup_thank_you_message"]) ? sanitize_text_field($_POST["atts"]["booking_popup_thank_you_message"]) : "");
        $booking_popup_thank_you_message_template = omnivo_calendar_stripslashes_deep($booking_popup_thank_you_message_template);

        $args = array(
            'event_hours_id' => $event_hour_id,
            'user_id' => $user_id,
            'guest_email' => $guest_email,
        );

        $event_hour_details = Omnivo_Calendar_DB::getEventHours($args);
        $event_hour_details = $event_hour_details[0];

        $result["available_places"] = $event_hour_details->available_places;
        $result["booking_count"] = $event_hour_details->booking_count;
        $result["remaining_places"] = $event_hour_details->available_places-$event_hour_details->booking_count;
        if(!($event_hour_details->available_places>0 && $event_hour_details->booking_count<$event_hour_details->available_places))
        {
            $result['event_hour_active'] = 0;
            $result['msg'] = __('<h2>Booking couldn\'t be made</h2>
<p>No place available for selected event hour.</p>', 'omnivo_calendar');
            $result['error'] = 1;
            omnivo_calendar_ajax_response($result);
        }

        //check if user/guest already booked max slots
        $remaining_slots = $event_hour_details->slots_per_user-$event_hour_details->current_user_booking_count-$event_hour_details->current_guest_booking_count;

        if($remaining_slots<1)
        {
            $result['msg'] = __('<h2>Booking couldn\'t be made</h2>
<p>You\'ve already reached maximum number of slots.</p>', 'omnivo_calendar');
            $result['error'] = 1;
            omnivo_calendar_ajax_response($result);
        }

        if($slots_number>$remaining_slots)
        {
            $result['msg'] = __('<h2>Booking couldn\'t be made</h2>
<p>You have selected too many slots.</p>', 'omnivo_calendar');
            $result['error'] = 1;
            omnivo_calendar_ajax_response($result);
        }

        if(!$user_id && $guest_config['allow_guest_booking']=='yes')
        {
            if($guest_config['guest_name_field_required']=='yes' && $guest_name=='')
            {
                $result['msg'] = __('<h2>Error</h2>
<p>You must fill name field.</p>', 'omnivo_calendar');
                $result['error'] = 1;
                omnivo_calendar_ajax_response($result);
            }
            if($guest_email=='')
            {
                $result['msg'] = __('<h2>Error</h2>
<p>You must fill email field.</p>', 'omnivo_calendar');
                $result['error'] = 1;
                omnivo_calendar_ajax_response($result);
            }
            if(!filter_var($guest_email, FILTER_VALIDATE_EMAIL))
            {
                $result['msg'] = __('<h2>Error</h2>
<p>Please provide valid email.</p>', 'omnivo_calendar');
                $result['error'] = 1;
                omnivo_calendar_ajax_response($result);
            }
            if($guest_config['guest_phone_field_required']=='yes' && $guest_phone=='')
            {
                $result['msg'] = __('<h2>Error</h2>
<p>You must fill phone field.</p>', 'omnivo_calendar');
                $result['error'] = 1;
                omnivo_calendar_ajax_response($result);
            }
            if($guest_config['guest_message_field_required']=='yes' && $guest_message=='')
            {
                $result['msg'] = __('<h2>Error</h2>
<p>You must fill message field.</p>', 'omnivo_calendar');
                $result['error'] = 1;
                omnivo_calendar_ajax_response($result);
            }

            $guest_id = Omnivo_Calendar_DB::createGuest(array(
                'guest_name' => $guest_name,
                'guest_email' => $guest_email,
                'guest_phone' => $guest_phone,
                'guest_message' => $guest_message,
            ));
        }

        //create bookings
        $bookings_ids = array();
        $booking_date = date_i18n('Y-m-d H:i:s');
        for($i=0; $i<$slots_number; $i++)
        {
            $validation_code = md5(time()+$event_hour_id+$user_id+mt_rand()*$i+omnivo_calendar_random_string());
            $bookings_ids[] = Omnivo_Calendar_DB::createBooking(array(
                'event_hour_id' => $event_hour_id,
                'user_id' => $user_id,
                'booking_date' => $booking_date,
                'guest_id' => $guest_id,
                'validation_code' => $validation_code,
            ));
        }

        if(!$bookings_ids)
        {
            $result['msg'] = __('<h2>Booking couldn\'t be made</h2>
<p>You can\'t register for this event hour.</p>', 'omnivo_calendar');
            $result['error'] = 1;
            omnivo_calendar_ajax_response($result);
        }

        $result['msg'] = $booking_popup_thank_you_message_template;
        $result['msg'] = str_replace('{event_title}', $event_hour_details->event_title, $result['msg']);
        $result['msg'] = str_replace('{column_title}', $event_hour_details->column_title, $result['msg']);
        $result['msg'] = str_replace('{event_start}', date($time_format, strtotime($event_hour_details->start)), $result['msg']);
        $result['msg'] = str_replace('{event_end}', date($time_format, strtotime($event_hour_details->end)), $result['msg']);
        $result['msg'] = str_replace('{event_description_1}', $event_hour_details->description_1, $result['msg']);
        $result['msg'] = str_replace('{event_description_2}', $event_hour_details->description_2, $result['msg']);
        $result['msg'] = str_replace('{omnivo_calendar_btn_continue}', omnivo_calendar_btn_continue($continue_popup_label), $result['msg']);

        $result['booking_count'] += $slots_number;
        $result['remaining_places'] -= $slots_number;

        $current_user_booking_count = ($user_id ? $event_hour_details->current_user_booking_count+$slots_number : 0);

        $result['booking_button'] = prepare_booking_button(array(
            'current_user_booking_count' => $current_user_booking_count,
            'slots_per_user' => $event_hour_details->slots_per_user,
            'event_hours_id' => $event_hour_id,
            'booked_text_color' => sanitize_text_field($_POST['atts']['booked_text_color']),
            'booked_bg_color' => sanitize_text_field($_POST['atts']['booked_bg_color']),
            'booked_label' => sanitize_text_field($_POST['atts']['booked_label']),
            'available_slots' => sanitize_text_field($result['remaining_places']),
            'unavailable_text_color' => sanitize_text_field($_POST['atts']['unavailable_text_color']),
            'unavailable_bg_color' => sanitize_text_field($_POST['atts']['unavailable_bg_color']),
            'unavailable_label' => sanitize_text_field($_POST['atts']['unavailable_label']),
            'booking_text_color' => sanitize_text_field($_POST['atts']['booking_text_color']),
            'booking_bg_color' => sanitize_text_field($_POST['atts']['booking_bg_color']),
            'booking_hover_text_color' => sanitize_text_field($_POST['atts']['booking_hover_text_color']),
            'booking_hover_bg_color' => sanitize_text_field($_POST['atts']['booking_hover_bg_color']),
            'booking_label' => sanitize_text_field($_POST['atts']['booking_label']),
            'show_booking_button' => sanitize_text_field($_POST['atts']['show_booking_button']),
        ));

        $result['available_slots_label'] = prepare_booking_slots_label(array(
            'available_slots' => $result['remaining_places'],
            'taken_slots' => $result['booking_count'],
            'total_slots' => $result['available_places'],
            'available_slots_singular_label' => $available_slots_singular_label,
            'available_slots_plural_label' => $available_slots_plural_label,
        ));

        $result['event_hour_active'] = (int)($event_hour_details->booking_count+1<$event_hour_details->available_places);

        if($user_id || $guest_email)
        {
            $debug_info = omnivo_calendar_booking_notification($bookings_ids, array('time_format' => $time_format));
            if($debug_info['msg'])
            {
                $result['msg'] .= '<p class="debug-info">' . $debug_info['msg'] . '</p>';
            }
        }

        omnivo_calendar_ajax_response($result);
    }
    add_action('wp_ajax_omnivo_calendar_ajax_event_hour_booking', 'omnivo_calendar_ajax_event_hour_booking');
    add_action('wp_ajax_nopriv_omnivo_calendar_ajax_event_hour_booking', 'omnivo_calendar_ajax_event_hour_booking');

    function omnivo_calendar_ajax_response($result)
    {
        echo "omnivo_calendar_start" . json_encode($result) . "omnivo_calendar_end";
        exit();
    }

    function omnivo_calendar_booking_notification($bookings_ids, $options = array())
    {
        $result = array(
            'error' => 0,
            'msg' => '',
        );
        $omnivo_calendar_contact_form_options = omnivo_calendar_stripslashes_deep(get_option("omnivo_calendar_contact_form_options"));
        $time_format = isset($options['time_format']) ? $options['time_format'] : 'H.i';

        $bookings = Omnivo_Calendar_DB::getBookings(array(
            'bookings_ids' => $bookings_ids,
        ));
        $booking_details = $bookings[0];
        $slots_number = count($bookings);

        $values = array(
            'booking_id' => $booking_details['booking_id'],
            'validation_code' => $booking_details['validation_code'],
            'event_title' => $booking_details['event_title'],
            'column_title' => $booking_details['weekday'],
            'event_start' => $booking_details['start'],
            'event_end' => $booking_details['end'],
            'event_description_1' => $booking_details['event_description_1'],
            'event_description_2' => $booking_details['event_description_2'],
            'booking_datetime' => $booking_details['booking_datetime'],
            'user_id' => $booking_details['user_id'],
            'user_name' => $booking_details['user_name'],
            'user_email' => $booking_details['user_email'],
            'guest_id' => $booking_details['guest_id'],
            'guest_name' => $booking_details['guest_name'],
            'guest_email' => $booking_details['guest_email'],
            'guest_phone' => $booking_details['guest_phone'],
            'guest_message' => $booking_details['guest_message'],
        );

        if(get_magic_quotes_gpc())
            $values = array_map('stripslashes', $values);
        $values = array_map('htmlspecialchars', $values);

        $user_name = '';
        $user_email = '';
        $user_phone = '';
        $user_message = '';
        if($values['user_id'])
        {
            $user_name = $values['user_name'];
            $user_email = $values['user_email'];
        }
        elseif($values['guest_id'])
        {
            $user_name = $values['guest_name'];
            $user_email = $values['guest_email'];
            $user_phone = $values['guest_phone'];
            $user_message = $values['guest_message'];
        }

        $cancel_booking = '';
        $booking_ids = array();
        $validation_codes = array();
        foreach($bookings as $index=>$booking)
        {
            $booking_ids[] = (int)$booking['booking_id'];
            $validation_codes[] = $booking['validation_code'];
            $cancel_booking .= '<a href="' . get_site_url() . '?action=omnivo_calendar_cancel_booking&booking_id=' . $booking['booking_id'] . '&validation_code=' . $booking['validation_code'] . '">' . sprintf(__('Cancel booking #%d', 'omnivo_calendar'), $booking['booking_id']) . '</a><br>';
        }
        if(count($bookings)>1)
            $cancel_booking .= '<a href="' . get_site_url() . '?action=omnivo_calendar_cancel_booking&bookings_ids=' . implode(',', $booking_ids) . '&validation_codes=' . implode(',', $validation_codes) . '">' . __('Cancel all bookings', 'omnivo_calendar') . '</a><br>';

        //SEND EMAIL TO CLIENT
        $headers = array();
        $headers[] = 'Reply-To: ' . $omnivo_calendar_contact_form_options['admin_name'] . ' <' . $omnivo_calendar_contact_form_options['admin_email'] . '>' . "\r\n";
        $headers[] = 'From: ' . $omnivo_calendar_contact_form_options['admin_name'] . ' <' . $omnivo_calendar_contact_form_options['admin_email'] . '>' . "\r\n";
        $headers[] = 'Content-type: text/html';
        $subject = $omnivo_calendar_contact_form_options['email_subject_client'];
        $subject = str_replace('{booking_id}', $values['booking_id'], $subject);
        $subject = str_replace('{event_title}', $values['event_title'], $subject);
        $subject = str_replace('{column_title}', $values['column_title'], $subject);
        $subject = str_replace('{event_start}', date($time_format, strtotime($values['event_start'])), $subject);
        $subject = str_replace('{event_end}', date($time_format, strtotime($values['event_end'])), $subject);
        $subject = str_replace('{event_description_1}', $values['event_description_1'], $subject);
        $subject = str_replace('{event_description_2}', $values['event_description_2'], $subject);
        $subject = str_replace('{slots_number}', $slots_number, $subject);
        $subject = str_replace('{booking_datetime}', $values['booking_datetime'], $subject);
        $subject = str_replace('{user_name}', $user_name, $subject);
        $subject = str_replace('{user_email}', $user_email, $subject);
        $subject = str_replace('{user_phone}', $user_phone, $subject);
        $subject = str_replace('{user_message}', nl2br($user_message), $subject);
        $body = $omnivo_calendar_contact_form_options['template_client'];
        $body = str_replace('{booking_id}', $values['booking_id'], $body);
        $body = str_replace('{event_title}', $values['event_title'], $body);
        $body = str_replace('{column_title}', $values['column_title'], $body);
        $body = str_replace('{event_start}', date($time_format, strtotime($values['event_start'])), $body);
        $body = str_replace('{event_end}', date($time_format, strtotime($values['event_end'])), $body);
        $body = str_replace('{event_description_1}', $values['event_description_1'], $body);
        $body = str_replace('{event_description_2}', $values['event_description_2'], $body);
        $body = str_replace('{slots_number}', $slots_number, $body);
        $body = str_replace('{booking_datetime}', $values['booking_datetime'], $body);
        $body = str_replace('{user_name}', $user_name, $body);
        $body = str_replace('{user_email}', $user_email, $body);
        $body = str_replace('{user_phone}', $user_phone, $body);
        $body = str_replace('{user_message}', nl2br($user_message), $body);
        $body = str_replace('{cancel_booking}', $cancel_booking, $body);

        $result['error'] = !(int)wp_mail($user_name . ' <' . $user_email . '>', $subject, $body, $headers);

        //SEND EMAIL TO ADMIN
        $headers = array();
        $headers[] = 'Reply-To: ' . $values["user_name"] . ' <' . $values["user_email"] . '>' . "\r\n";
        $headers[] = 'From: ' . $omnivo_calendar_contact_form_options["admin_name"] . ' <' . $omnivo_calendar_contact_form_options["admin_email"] . '>' . "\r\n";
        $headers[] = 'Content-type: text/html';
        $subject = $omnivo_calendar_contact_form_options["email_subject_admin"];
        $subject = str_replace("{booking_id}", $values["booking_id"], $subject);
        $subject = str_replace("{event_title}", $values["event_title"], $subject);
        $subject = str_replace("{column_title}", $values["column_title"], $subject);
        $subject = str_replace("{event_start}", date($time_format, strtotime($values["event_start"])), $subject);
        $subject = str_replace("{event_end}", date($time_format, strtotime($values["event_end"])), $subject);
        $subject = str_replace("{event_description_1}", $values["event_description_1"], $subject);
        $subject = str_replace("{event_description_2}", $values["event_description_2"], $subject);
        $subject = str_replace("{slots_number}", $slots_number, $subject);
        $subject = str_replace("{booking_datetime]", $values["booking_datetime"], $subject);
        $subject = str_replace("{user_name}", $user_name, $subject);
        $subject = str_replace("{user_email}", $user_email, $subject);
        $subject = str_replace("{user_phone}", $user_phone, $subject);
        $subject = str_replace("{user_message}", nl2br($user_message), $subject);
        $body = $omnivo_calendar_contact_form_options["template_admin"];
        $body = str_replace("{booking_id}", $values["booking_id"], $body);
        $body = str_replace("{event_title}", $values["event_title"], $body);
        $body = str_replace("{column_title}", $values["column_title"], $body);
        $body = str_replace("{event_start}", date($time_format, strtotime($values["event_start"])), $body);
        $body = str_replace("{event_end}", date($time_format, strtotime($values["event_end"])), $body);
        $body = str_replace("{event_description_1}", $values["event_description_1"], $body);
        $body = str_replace("{event_description_2}", $values["event_description_2"], $body);
        $body = str_replace("{slots_number}", $slots_number, $body);
        $body = str_replace("{booking_datetime}", $values["booking_datetime"], $body);
        $body = str_replace("{user_name}", $user_name, $body);
        $body = str_replace("{user_email}", $user_email, $body);
        $body = str_replace("{user_phone}", $user_phone, $body);
        $body = str_replace("{user_message}", nl2br($user_message), $body);
        $body = str_replace("{cancel_booking}", $cancel_booking, $body);

        $result['error'] = !(int)wp_mail($omnivo_calendar_contact_form_options['admin_name'] . ' <' . $omnivo_calendar_contact_form_options['admin_email'] . '>', $subject, $body, $headers);

        if($omnivo_calendar_contact_form_options['mail_debug']=='yes')
        {
            if($result['error']==0)
            {
                $result['msg'] .= __('Email message sent.', 'omnivo_calendar');
            }
            else
            {
                $result['msg'] .= sprintf(__('Email message not sent.<br>%s', 'omnivo_calendar'), $GLOBALS['phpmailer']->ErrorInfo);
            }
        }
        return $result;
    }

    function omnivo_calendar_get_new_widget_name( $widget_name, $widget_index )
    {
        $current_sidebars = get_option( 'sidebars_widgets' );
        $all_widget_array = array( );
        foreach ( $current_sidebars as $sidebar => $widgets ) {
            if ( !empty( $widgets ) && is_array( $widgets ) && $sidebar != 'wp_inactive_widgets' ) {
                foreach ( $widgets as $widget ) {
                    $all_widget_array[] = $widget;
                }
            }
        }
        while ( in_array( $widget_name . '-' . $widget_index, $all_widget_array ) ) {
            $widget_index++;
        }
        $new_widget_name = $widget_name . '-' . $widget_index;
        return $new_widget_name;
    }


    function omnivo_calendar_ajax_events_settings_save()
    {
        $omnivo_calendar_events_settings = get_option("omnivo_calendar_events_settings");
        $slug_old = $omnivo_calendar_events_settings["slug"];
        $omnivo_calendar_slug_old = $omnivo_calendar_events_settings["slug"];
        $omnivo_calendar_events_settings["slug"] = (!empty($_POST["events_slug"]) ? sanitize_title($_POST["events_slug"]) : __("events", "omnivo_calendar"));
        $omnivo_calendar_events_settings["label_singular"] = (!empty($_POST["events_label_singular"]) ? sanitize_text_field($_POST["events_label_singular"]) : __("Event", "omnivo_calendar"));
        $omnivo_calendar_events_settings["label_plural"] = (!empty($_POST["events_label_plural"]) ? sanitize_text_field($_POST["events_label_plural"]) : __("Events", "omnivo_calendar"));
        if(update_option("omnivo_calendar_events_settings", $omnivo_calendar_events_settings) && $omnivo_calendar_slug_old!=$_POST["events_slug"])
        {
            require_once(__DIR__.DIRECTORY_SEPARATOR.'events.php');
            $events = get_posts(array(
                'post_type' => $slug_old,
                'posts_per_page' => -1
            ));
            foreach($events as $event)
                set_post_type($event->ID, $omnivo_calendar_events_settings["slug"]);
            //delete rewrite rules, they will be regenerated automatically by WP on next request
            delete_option('rewrite_rules');
        }
        exit();
    }
    add_action('wp_ajax_omnivo_calendar_ajax_events_settings_save', 'omnivo_calendar_ajax_events_settings_save');

    function omnivo_calendar_admin_page()
    {
        $omnivo_calendar_events_settings = omnivo_calendar_events_settings();

        //get events list
        $events_list = get_posts(array(
            'posts_per_page' => -1,
            'nopaging' => true,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_type' => $omnivo_calendar_events_settings['slug']
        ));

        //get weekdays list
        $weekdays_list = get_posts(array(
            'posts_per_page' => -1,
            'nopaging' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_type' => 'omnivo_weekdays'
        ));

        //get all hour categories
        global $wpdb;
        $query = "SELECT distinct(category) AS category FROM " . $wpdb->prefix . "omnivo_calendar_event_hours AS t1
				LEFT JOIN {$wpdb->posts} AS t2 ON t1.event_id=t2.ID 
				WHERE 
				t2.post_type='" . $omnivo_calendar_events_settings['slug'] . "'
				AND t2.post_status='publish'
				AND category<>''
				ORDER BY category ASC";
        $hour_categories = $wpdb->get_results($query);
        //events string
        $events_string = "";
        $events_select_list = "";
        foreach($events_list as $event)
        {
            $events_select_list .= '<option value="' . urldecode($event->post_name) . '">' . $event->post_title . ' (id: ' . $event->ID . ')' . '</option>';
            $events_string .= $event->post_name . (end($events_list)!=$event ? "," : "");
        }
        //events categories string
        $events_categories_list = "";
        $events_categories = get_terms(array(
            "taxonomy" => "events_category",
            "orderby" => "name",
            "order" => "ASC",
        ));
        foreach($events_categories as $events_category)
            $events_categories_list .= '<option value="' . urldecode(esc_attr($events_category->slug)) . '">' . $events_category->name . '</option>';
        //weekdays string
        $weekdays_string = "";
        $weekdays_select_list = "";
        foreach($weekdays_list as $weekday)
        {
            $weekdays_select_list .= '<option value="' . urldecode($weekday->post_name) . '">' . $weekday->post_title . ' (id: ' . $weekday->ID . ')' . '</option>';
            $weekdays_string .= $weekday->post_name . (end($weekdays_list)!=$weekday ? "," : "");
        }
        //get google fonts
        $fontsArray = omnivo_calendar_get_google_fonts();
        $fontsHtml = "";
        if(isset($fontsArray))
        {
            $fontsCount = count((array)$fontsArray->items);
            for($i=0; $i<$fontsCount; $i++)
            {
                $variantsCount = count((array)$fontsArray->items[$i]->variants);
                if($variantsCount>1)
                {
                    for($j=0; $j<$variantsCount; $j++)
                    {
                        $fontsHtml .= '<option value="' . $fontsArray->items[$i]->family . ":" . $fontsArray->items[$i]->variants[$j] . '">' . $fontsArray->items[$i]->family . ":" . $fontsArray->items[$i]->variants[$j] . '</option>';
                    }
                }
                else
                {
                    $fontsHtml .= '<option value="' . $fontsArray->items[$i]->family . '">' . $fontsArray->items[$i]->family . '</option>';
                }
            }
        }
        require(__DIR__ . "/admin-page.php");
    }

    function omnivo_calendar_admin_page_email_config()
    {
        if(isset($_POST["action"]) && $_POST["action"]=="save")
        {
            $omnivo_calendar_contact_form_options = array(
                "email_subject_client" => sanitize_text_field($_POST["email_subject_client"]),
                "email_subject_admin" => sanitize_text_field($_POST["email_subject_admin"]),
                "admin_name" => sanitize_text_field($_POST["admin_name"]),
                "admin_email" => sanitize_text_field($_POST["admin_email"]),
                "mail_debug" => sanitize_text_field($_POST["mail_debug"]),
                "template_client" => sanitize_text_field($_POST["template_client"]),
                "template_admin" => sanitize_text_field($_POST["template_admin"]),
                "smtp_host" => sanitize_text_field($_POST["smtp_host"]),
                "smtp_username" => sanitize_text_field($_POST["smtp_username"]),
                "smtp_password" => sanitize_text_field($_POST["smtp_password"]),
                "smtp_port" => sanitize_text_field($_POST["smtp_port"]),
                "smtp_secure" => sanitize_text_field($_POST["smtp_secure"])
            );
            update_option("omnivo_calendar_contact_form_options", $omnivo_calendar_contact_form_options);
        }
        $omnivo_calendar_contact_form_options = omnivo_calendar_stripslashes_deep(get_option("omnivo_calendar_contact_form_options"));
        require(__DIR__ . "/admin-page-email-config.php");
    }

    function omnivo_calendar_admin_page_event_post_type()
    {
        $omnivo_calendar_events_settings = omnivo_calendar_events_settings();
        require(__DIR__ . "/admin-page-event-post-type.php");
    }

    function omnivo_calendar_admin_page_google_calendar()
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'class-google-calendar.php');
        $googleCalendar = new Omnivo_Calendar_Google_Calendar();

        if(isset($_POST["action"]) && $_POST["action"]=="save")
        {
            $googleCalendar->id = filter_input(INPUT_POST, 'calendar_id');
            $googleCalendar->service_account_encoded = filter_input(INPUT_POST, 'service_account_encoded');
            $googleCalendar->SaveOptions();
        }

        if(isset($_POST["action"]) && $_POST["action"]=="export")
        {
            $event = (isset($_POST['event']) ? array_map('sanitize_text_field', $_POST['event']) : array());
            $weekday = (isset($_POST['weekday']) ? array_map('sanitize_text_field', $_POST['weekday']) : array());
            $eventsHours = Omnivo_Calendar_Event_Hour::Fetch(array(
                'event' => $event,
                'weekday' => $weekday,
            ));
            $exportResult = $googleCalendar->ExportEvents($eventsHours, $weekday);
        }

        if(isset($_POST["action"]) && $_POST["action"]=="get_calendar_data")
        {
            $importResult = $googleCalendar->LoadEventsData();
        }

        if(isset($_POST["action"]) && $_POST["action"]=="import")
        {
            $weekday = (isset($_POST['weekday']) ? array_map('sanitize_text_field', $_POST['weekday']) : array());
            $calendar_event = (isset($_POST['calendar_event']) ? array_map('sanitize_text_field', $_POST['calendar_event']) : array());
            $importResult = $googleCalendar->ImportEvents($calendar_event, $weekday);
        }

        $Events = Omnivo_Calendar_Event::Fetch();
        $Weekdays = Omnivo_Calendar_Weekday::Fetch();
        $UniqueCalendarEvents = $googleCalendar->UniqueCalendarEvents();

        require(__DIR__ . "/admin-page-google-calendar.php");
    }
}

function omnivo_calendar_ajax_omnivo_calendar_save_shortcode()
{
    $shortcode = (!empty($_POST["omnivo_calendar_shortcode"]) ? stripslashes(sanitize_text_field($_POST["omnivo_calendar_shortcode"])) : "");
    $shortcode_id = (!empty($_POST["omnivo_calendar_shortcode_id"]) ? sanitize_text_field($_POST["omnivo_calendar_shortcode_id"]) : "");

    if($shortcode_id!=="" && $shortcode!=="")
    {
        $omnivo_calendar_shortcodes_list = get_option("omnivo_calendar_shortcodes_list");
        if($omnivo_calendar_shortcodes_list===false)
            $omnivo_calendar_shortcodes_list = array();
        $omnivo_calendar_shortcodes_list[$shortcode_id] = $shortcode;
        ksort($omnivo_calendar_shortcodes_list);
        if(update_option("omnivo_calendar_shortcodes_list", $omnivo_calendar_shortcodes_list))
            echo "omnivo_calendar_start" . $shortcode_id . "omnivo_calendar_end";
        else
            echo 0;
    }
    exit();
}
add_action('wp_ajax_omnivo_calendar_save_shortcode', 'omnivo_calendar_ajax_omnivo_calendar_save_shortcode');

function omnivo_calendar_ajax_omnivo_calendar_delete_shortcode()
{
    if(!empty($_POST["omnivo_calendar_shortcode_id"]))
    {
        $shortcode_id = sanitize_text_field($_POST["omnivo_calendar_shortcode_id"]);
        $omnivo_calendar_shortcodes_list = get_option("omnivo_calendar_shortcodes_list");
        if($omnivo_calendar_shortcodes_list!==false && !empty($omnivo_calendar_shortcodes_list[$shortcode_id]))
        {
            unset($omnivo_calendar_shortcodes_list[$shortcode_id]);
            if(update_option("omnivo_calendar_shortcodes_list", $omnivo_calendar_shortcodes_list))
            {
                echo 1;
                exit();
            }
        }
    }
    echo 0;
    exit();
}
add_action('wp_ajax_omnivo_calendar_delete_shortcode', 'omnivo_calendar_ajax_omnivo_calendar_delete_shortcode');

function omnivo_calendar_is_licensed(){
    return OMNIVO_CALENDAR_VERSION === '9.0.0';
}

function omnivo_calendar_ajax_omnivo_calendar_get_shortcode()
{
    if(!empty($_POST["omnivo_calendar_shortcode_id"]))
    {
        $shortcode_id = sanitize_text_field($_POST["omnivo_calendar_shortcode_id"]);
        $omnivo_calendar_shortcodes_list = get_option("omnivo_calendar_shortcodes_list");
        if($omnivo_calendar_shortcodes_list!==false && !empty($omnivo_calendar_shortcodes_list[$shortcode_id]))
        {
            echo "omnivo_calendar_start" . html_entity_decode($omnivo_calendar_shortcodes_list[$shortcode_id]) . "omnivo_calendar_end";
            exit();
        }
    }
    echo 0;
    exit();
}
add_action('wp_ajax_omnivo_calendar_get_shortcode', 'omnivo_calendar_ajax_omnivo_calendar_get_shortcode');

function omnivo_calendar_slots_number($slots_number_config)
{
    $output = '';
    if($slots_number_config['slots_per_user']>1 && $slots_number_config['remaining_places']>1)
    {
        $max_slosts = ($slots_number_config['remaining_places']<=$slots_number_config['slots_per_user'] ? $slots_number_config['remaining_places'] : $slots_number_config['slots_per_user']);
        $output .=
            '<p>
			<label>' . __('Slots number', 'omnivo_calendar') . '</label>
			<select class="omnivo_calendar_slots_number" name="slots_number">';
        for($i=1; $i<=$max_slosts; $i++)
        {
            $output .=
                '<option value="' . $i . '">' . $i . '</option>';
        }
        $output .=
            '</select>
		</p>';
    }
    return $output;
}

function omnivo_calendar_booking_form($args)
{
    $output = '';
    $max_slots = 0;
    $remaining_slots = $args['slots_per_user']-$args['current_user_booking_count'];
    if($remaining_slots>1 && $args['remaining_places']>1)
    {
        $max_slots = ($args['remaining_places']<=$remaining_slots ? $args['remaining_places'] : $remaining_slots);
    }

    if($args['allow_user_booking']=='yes')
    {
        //CREATE USER FORM

        $formHidden = false;
        if($max_slots==0)
            $formHidden = true;
        if($args['default_booking_view']!='user' && !is_user_logged_in())
            $formHidden = true;

        $output .=
            '<form class="omnivo_calendar_booking_form user ' . ($formHidden ? 'omnivo_calendar_hide' : '') . '">';

        //slots field
        if($max_slots>1)
        {
            $output .=
                '<div class="omnivo_calendar_field_wrapper " data-max-slots="' . $max_slots . '">
    			<label for="omnivo_calendar_slots_number">' . __('Slots number', 'omnivo_calendar') . '</label>
    			<div class="omnivo_calendar_slots_number_wrapper">
    				<input id="omnivo_calendar_slots_number" class="omnivo_calendar_field omnivo_calendar_slots_number" name="slots_number" type="number" min="1" max="' . $max_slots . '" step="1" value="1" autocomplete="off"/>
    				<input type="button" class="omnivo_calendar_slots_number_plus" value="+">
    				<input type="button" class="omnivo_calendar_slots_number_minus" value="-">
    			</div>
    		</div>';
        }

        $output .=
            '</form>';
    }

    if($args['allow_guest_booking']=='yes')
    {
        //CREATE GUEST FORM

        $formHidden = false;
        if($args['default_booking_view']!='guest')
            $formHidden = true;
        elseif($args['allow_user_booking']=='yes' && is_user_logged_in())
            $formHidden = true;

        $output .=
            '<form class="omnivo_calendar_booking_form guest ' . ($formHidden ? 'omnivo_calendar_hide' : '') . '">';

        //name field
        if($args['show_guest_name_field']=='yes')
        {
            $placeholder = ($args['guest_name_field_required']=='yes' ? __('Name *', 'omnivo_calendar') : __('Name', 'omnivo_calendar'));
            $output .=
                '<div class="omnivo_calendar_field_wrapper ">
    			<label for="omnivo_calendar_guest_name">' . $placeholder . '</label>
    			<input id="omnivo_calendar_guest_name" class="omnivo_calendar_field omnivo_calendar_guest_name" name="name" type="text"  value="" autocomplete="off"/>
    		</div>';
        }

        //email field(required)
        $placeholder = __('Email *', 'omnivo_calendar');
        $output .=
            '<div class="omnivo_calendar_field_wrapper ">
    		<label for="omnivo_calendar_guest_email">' . $placeholder . '</label>
    		<input id="omnivo_calendar_guest_email" class="omnivo_calendar_field omnivo_calendar_guest_email" name="email" type="email"  value="" autocomplete="off"/>
    	</div>';

        //phone field
        if($args['show_guest_phone_field']=='yes')
        {
            $placeholder = ($args['guest_phone_field_required']=='yes' ? __('Phone *', 'omnivo_calendar') : __('Phone', 'omnivo_calendar'));
            $output .=
                '<div class="omnivo_calendar_field_wrapper ">
    			<label for="omnivo_calendar_guest_phone">' . $placeholder . '</label>
    			<input id="omnivo_calendar_guest_phone" class="omnivo_calendar_field omnivo_calendar_guest_phone" name="phone" type="text"  value="" autocomplete="off"/>
    		</div>';
        }

        //slots field
        if($max_slots>1)
        {
            $output .=
                '<div class="omnivo_calendar_field_wrapper" data-max-slots="'.$max_slots.'">
    			<label for="omnivo_calendar_slots_number">' . __('Slots number', 'omnivo_calendar') . '</label>
    			<div class="omnivo_calendar_slots_number_wrapper">
    				<input id="omnivo_calendar_slots_number" class="omnivo_calendar_field omnivo_calendar_slots_number" name="slots_number" type="number" min="1" max="' . $max_slots . '" step="1" value="1" autocomplete="off"/>
    				<input type="button" class="omnivo_calendar_slots_number_plus" value="+">
    				<input type="button" class="omnivo_calendar_slots_number_minus" value="-">
    			</div>
    		</div>';
        }

        //message field
        if($args['show_guest_message_field']=='yes')
        {
            $placeholder = ($args['guest_message_field_required']=='yes' ? __('Message *', 'omnivo_calendar') : __('Message', 'omnivo_calendar'));
            $output .=
                '<div class="omnivo_calendar_field_wrapper wide ">
    			<label for="omnivo_calendar_guest_message">' . $placeholder . '</label>
    			<textarea id="omnivo_calendar_guest_message" class="omnivo_calendar_field omnivo_calendar_guest_message" name="message" autocomplete="off"></textarea>
    		</div>';
        }

        //terms checkbox field
        if($args['terms_checkbox']=='yes')
        {
            $output .=
                '<div class="omnivo_calendar_field_wrapper wide terms_checkbox_wrapper ">
    			<input id="tt_terms_checkbox" class="omnivo_calendar_field tt_terms_checkbox" name="terms_checkbox" type="checkbox" value="1" autocomplete="off"/>
    			<label for="tt_terms_checkbox">' . $args['terms_message'] . '</label>
    		</div>';
        }

        $output .=
            '</form>';
    }

    return $output;
}

function omnivo_calendar_btn_book($args)
{
    $args = shortcode_atts(array(
        'booking_label' => '',
        'login_label' => '',
        'redirect_url' => '',
        'allow_user_booking' => '',
        'allow_guest_booking' => '',
        'default_booking_view' => '',
    ), $args);

    $bookingHidden = false;
    if(!is_user_logged_in() &&
        $args['default_booking_view']=='user' &&
        $args['allow_user_booking']=='yes')
    {
        $bookingHidden = true;
    }

    $loginHidden = false;
    if(is_user_logged_in())
        $loginHidden = true;
    if($args['allow_user_booking']=='no')
        $loginHidden = true;
    if($args['default_booking_view']=='guest')
        $loginHidden = true;

    $output = '';
    $output .= '<a href="#" class="omnivo_calendar_btn book ' . ($bookingHidden ? 'omnivo_calendar_hide' : '') . '">' . $args['booking_label'] . '</a>';
    $output .= '<a href="' . wp_login_url($args['redirect_url']) . '" class="omnivo_calendar_btn login ' . ($loginHidden ? 'omnivo_calendar_hide' : '') . '">' . $args['login_label'] . '</a>';

    return $output;
}

function omnivo_calendar_btn_continue($continue_label)
{
    $output = '';
    $output .= "<a href='#' class='omnivo_calendar_btn continue'>" . $continue_label . "</a>";
    return $output;
}

function omnivo_calendar_btn_cancel($cancel_label)
{
    $output = '';
    $output .= "<a href='#' class='omnivo_calendar_btn cancel'>" . $cancel_label . "</a>";
    return $output;
}

/**
 * Returns array of Google Fonts
 * @return array of Google Fonts
 */
function omnivo_calendar_get_google_fonts()
{
    //get google fonts
    $fontsArray = get_option("omnivo_calendar_google_fonts");
    //update if option doesn't exist or it was modified more than 2 weeks ago
    return $fontsArray;
}

function omnivo_calendar_random_string($length=12)
{
    $code = '';
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chars_length = strlen($chars);
    for($i=0; $i<$length; $i++)
        $code .= $chars[mt_rand(0,$chars_length-1)];
    return $code;
}

function omnivo_calendar_stripslashes_deep($value)
{
    $value = is_array($value) ?
        array_map('stripslashes_deep', $value) :
        stripslashes($value);

    return $value;
}

function tiny_mce_on_change($settings)
{
    if(array_key_exists('selector', $settings) && in_array($settings['selector'], array('#booking_popup_message', '#booking_popup_thank_you_message')))
    {
        $settings['setup'] = 'function(ed){
			ed.on("keyup change", function(){
				generateShortcode();				
			});
		}';
    }
    return $settings;
}
add_filter('tiny_mce_before_init', 'tiny_mce_on_change');

function omnivo_calendar_generate_pdf()
{
    if(!($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['omnivo_calendar_action']) && $_POST['omnivo_calendar_action']=='omnivo_calendar_generate_pdf'))
        return;

    require_once(__DIR__ . '/libraries/dompdf/autoload.inc.php');
    $pdf_font = filter_input(INPUT_POST, 'omnivo_calendar_pdf_font');
    $omnivo_calendar_pdf_html_content=(isset($_POST['omnivo_calendar_pdf_html_content']) ? stripslashes($_POST['omnivo_calendar_pdf_html_content']) : '');
    $omnivo_calendar_html = require(__DIR__ . '/pdf-template.php');

    $options = new Dompdf\Options();
    $options->set('defaultFont', 'Lato');
    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->loadHtml($omnivo_calendar_html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("calendar.pdf");
}
add_action('wp_loaded','omnivo_calendar_generate_pdf');
